<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        // Handle Update
        $customer_id = $_POST['id'];
        $customer_name = trim($_POST['customer_name']);
        $customer_email = trim($_POST['customer_email']);
        $customer_phone = trim($_POST['customer_phone']);

        if (!preg_match('/^09\d{9}$/', $customer_phone)) {
            $_SESSION['error'] = "Invalid phone number. It must start with 09 and be 11 digits.";
            header("Location: accounts.php");
            exit();
        }

            // Debugging: Print the values
        echo "ID: $customer_id<br>";
        echo "Name: $customer_name<br>";
        echo "Email: $customer_email<br>";
        echo "Phone: $customer_phone<br>";

        $stmt = $pdo->prepare("UPDATE customer SET customer_name = ?, customer_email = ?, customer_phone = ? WHERE id = ?");
        $stmt->execute([$customer_name, $customer_email, $customer_phone, $customer_id]);
        header("Location: accounts.php");

          // Debugging: Check if the query was successful
    if ($stmt->rowCount() > 0) {
        echo "Update successful!";
    } else {
        echo "No rows updated.";
    }

        exit();
    }

    if (isset($_POST['approve'])) {
        // Handle Approve
        $customer_id = $_POST['id'];
        $stmt = $pdo->prepare("UPDATE customer SET status = 'Approved' WHERE id = ?");
        $stmt->execute([$customer_id]);
        header("Location: accounts.php");
        exit();
    }

    if (isset($_POST['reject'])) {
        // Handle Reject
        $customer_id = $_POST['id'];
        $stmt = $pdo->prepare("UPDATE customer SET status = 'Rejected' WHERE id = ?");
        $stmt->execute([$customer_id]);
        header("Location: accounts.php");
        exit();
    }

    // Check if the delete button was clicked
    if (isset($_POST['delete']) && isset($_POST['id'])) {
        $customer_id = $_POST['id'];

        try {
            // Begin transaction
            $pdo->beginTransaction();

            // Retrieve the user_id associated with the customer
            $stmt = $pdo->prepare("SELECT id FROM customer WHERE id = ?");
            $stmt->execute([$customer_id]);
            $user_id = $stmt->fetchColumn();

            // Delete the customer record
            $stmt = $pdo->prepare("DELETE FROM customer WHERE id = ?");
            $stmt->execute([$customer_id]);

            // If a user_id exists, delete the corresponding user record
            if ($user_id) {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
            }

            // Commit the transaction
            $pdo->commit();

            $_SESSION['success'] = "Account deleted successfully.";
        } catch (Exception $e) {
            // Roll back the transaction on error
            $pdo->rollBack();
            $_SESSION['error'] = "Error deleting account: " . $e->getMessage();
        }

        // Redirect back to the accounts page
        header("Location: accounts.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin | Accounts</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/accounts.css">
    
</head>
<body>  
    <div class="account-content">
        <?php include 'includes/sidebar.php'; ?>
        <?php include 'includes/topbar.php'; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                try {
                    // Fetch all customers using PDO
                    $stmt = $pdo->query("SELECT * FROM customer ORDER BY id ASC");
                    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (count($customers) > 0):
                        foreach ($customers as $row):
                ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($row['customer_name'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($row['customer_email'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($row['customer_phone'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($row['status'] ?? 'Pending Approval') ?></td>
                        <td>
                            <button class="btn edit" onclick="openEditModal(<?= htmlspecialchars(json_encode($row)) ?>)">Edit</button>
                            <button class="btn view" onclick="openViewModal(<?= htmlspecialchars(json_encode($row)) ?>)">View Verification</button>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this account?');">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($row['id']) ?>">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <button type="submit" name="delete" class="btn delete">Delete</button>
                        </form>
                        </td>
                    </tr>
                <?php
                        endforeach;
                    else:
                ?>
                    <tr>
                        <td colspan="6">No customer accounts found.</td>
                    </tr>
                <?php
                    endif;
                } catch (PDOException $e) {
                    echo "<tr><td colspan='6'>Error fetching customer accounts: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>

    </div>

    <!-- Modal for Edit -->
    <div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeEditModal">&times;</span>
        <h2>Edit Customer</h2>
        <form method="POST">
            <input type="hidden" name="id" id="editCustomerId">
            <label for="editCustomerName">Name:</label>
            <input type="text" id="editCustomerName" name="customer_name" required>
            <label for="editCustomerEmail">Email:</label>
            <input type="email" id="editCustomerEmail" name="customer_email" required>
            <label for="editCustomerPhone">Phone:</label>
            <input 
                type="tel" 
                id="editCustomerPhone" 
                name="customer_phone" 
                required 
                pattern="^\+?[0-9]{10,15}$" 
                title="Please enter a valid phone number (e.g., +639756864187 or 09756864187)">
            <button type="submit" name="update" class="btn edit">Update</button>
        </form>
    </div>
</div>

    <!-- Modal for View Verification -->
<div id="viewModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeViewModal">&times;</span>
        <h2>Customer Verification</h2>
        <p id="verificationDetails"></p>
        <!-- Clickable Submitted ID -->
        <img id="verificationImage" src="" alt="Submitted ID" style="display: none; margin-top: 10px; cursor: pointer;" onclick="openImageModal()">
        <form method="POST">
            <input type="hidden" name="id" id="verificationCustomerId">
            <button type="submit" name="approve" class="btn view">Approve</button>
            <button type="submit" name="reject" class="btn delete">Reject</button>
        </form>
    </div>
</div>

<!-- Modal for Viewing Full-Size Submitted ID -->
<div id="imageModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeImageModal">&times;</span>
        <img id="fullImage" src="" alt="Submitted ID" style="width: 100%; height: auto;">
    </div>
</div>

<script>
    // Get modal elements
    const editModal = document.getElementById("editModal");
    const viewModal = document.getElementById("viewModal");
    const imageModal = document.getElementById("imageModal");
    const closeEditModal = document.getElementById("closeEditModal");
    const closeViewModal = document.getElementById("closeViewModal");
    const closeImageModal = document.getElementById("closeImageModal");

    // Open Edit Modal
    function openEditModal(customer) {
        document.getElementById("editCustomerId").value = customer.id;
        document.getElementById("editCustomerName").value = customer.customer_name;
        document.getElementById("editCustomerEmail").value = customer.customer_email;
        document.getElementById("editCustomerPhone").value = customer.customer_phone;
        editModal.style.display = "block";
    }

    // Open View Modal
    function openViewModal(customer) {
        console.log(customer); // Debugging

    document.getElementById("verificationDetails").innerText = `
        Name: ${customer.customer_name}
        Email: ${customer.customer_email}
        Phone: ${customer.customer_phone}
    `;

    const verificationImage = document.getElementById("verificationImage");

    if (customer.submitted_id) {
        const imagePath = `../uploads/${customer.submitted_id}`;
        verificationImage.src = imagePath;
        verificationImage.style.display = "block";
        verificationImage.setAttribute("data-full-image", imagePath); // Store the full image path
    } else {
        verificationImage.style.display = "none";
    }

    document.getElementById("verificationCustomerId").value = customer.id; // Use 'id' instead of 'customer_id'
    viewModal.style.display = "block";
}

    // Open Image Modal
    function openImageModal() {
        const verificationImage = document.getElementById("verificationImage");
        const fullImage = document.getElementById("fullImage");
        const imagePath = verificationImage.getAttribute("data-full-image");

        if (imagePath) {
            fullImage.src = imagePath;
            imageModal.style.display = "block";
        }
    }

    // Close Modals
    closeEditModal.onclick = () => editModal.style.display = "none";
    closeViewModal.onclick = () => viewModal.style.display = "none";
    closeImageModal.onclick = () => imageModal.style.display = "none";
    window.onclick = (event) => {
        if (event.target === editModal) editModal.style.display = "none";
        if (event.target === viewModal) viewModal.style.display = "none";
        if (event.target === imageModal) imageModal.style.display = "none";
    };
</script>

    <script src="https://kit.fontawesome.com/b7bdbf86fb.js" crossorigin="anonymous"></script>


</body>
</html>
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../db.php';
require_once('../includes/session_handler.php');

// Start admin session and check access
startAdminSession();
requireAdmin();

// Get the current user's information
$user_id = $_SESSION['user_id'] ?? null;
$user = null;

if ($user_id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND user_type = 'admin'");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        // Handle Update
        $users_id = $_POST['id'];
        $firstname = trim($_POST['firstname']);
        $email = trim($_POST['email']);
        $customer_phone = trim($_POST['customer_phone']);

        if (!preg_match('/^09\d{9}$/', $customer_phone)) {
            $_SESSION['error'] = "Invalid phone number. It must start with 09 and be 11 digits.";
            header("Location: accounts.php");
            exit();
        }

        try {
            $stmt = $pdo->prepare("UPDATE users SET firstname = ?, email = ?, customer_phone = ? WHERE id = ?");
            $stmt->execute([$firstname, $email, $customer_phone, $users_id]);
            $_SESSION['success'] = "Account updated successfully.";
        } catch (Exception $e) {
            $_SESSION['error'] = "Error updating account: " . $e->getMessage();
        }
        header("Location: accounts.php");
        exit();
    }

    if (isset($_POST['approve'])) {
        // Handle Approve
        $users_id = $_POST['id'];
        try {
            // Debug: Log the current status before update
            error_log("Attempting to approve user ID: " . $users_id);
            
            // First check current status
            $check_before = $pdo->prepare("SELECT status FROM users WHERE id = ?");
            $check_before->execute([$users_id]);
            $current_status = $check_before->fetchColumn();
            error_log("Current status before update: " . $current_status);
            
            // Update status
            $stmt = $pdo->prepare("UPDATE users SET status = 'Approved' WHERE id = ?");
            $result = $stmt->execute([$users_id]);
            error_log("Update query result: " . ($result ? "success" : "failed"));
            
            // Verify the update
            $check_after = $pdo->prepare("SELECT status FROM users WHERE id = ?");
            $check_after->execute([$users_id]);
            $updated_status = $check_after->fetchColumn();
            error_log("Status after update: " . $updated_status);
            
            if ($updated_status === 'Approved') {
                $_SESSION['success'] = "Account verified successfully.";
            } else {
                throw new Exception("Status update failed. Current status: " . $updated_status);
            }

            error_log('Approve POST: ' . print_r($_POST, true));
            error_log('Approve SQL result: ' . ($result ? 'success' : 'fail'));
        } catch (Exception $e) {
            error_log("Error in approve process: " . $e->getMessage());
            $_SESSION['error'] = "Error verifying account: " . $e->getMessage();
        }
        header("Location: accounts.php");
        exit();
    }

    if (isset($_POST['reject'])) {
        // Handle Reject
        $users_id = $_POST['id'];
        try {
            // Debug: Log the current status before update
            error_log("Attempting to reject user ID: " . $users_id);
            
            // First check current status
            $check_before = $pdo->prepare("SELECT status FROM users WHERE id = ?");
            $check_before->execute([$users_id]);
            $current_status = $check_before->fetchColumn();
            error_log("Current status before update: " . $current_status);
            
            // Update status
            $stmt = $pdo->prepare("UPDATE users SET status = 'Rejected' WHERE id = ?");
            $result = $stmt->execute([$users_id]);
            error_log("Update query result: " . ($result ? "success" : "failed"));
            
            // Verify the update
            $check_after = $pdo->prepare("SELECT status FROM users WHERE id = ?");
            $check_after->execute([$users_id]);
            $updated_status = $check_after->fetchColumn();
            error_log("Status after update: " . $updated_status);
            
            if ($updated_status === 'Rejected') {
                $_SESSION['success'] = "Account rejected successfully.";
            } else {
                throw new Exception("Status update failed. Current status: " . $updated_status);
            }
        } catch (Exception $e) {
            error_log("Error in reject process: " . $e->getMessage());
            $_SESSION['error'] = "Error rejecting account: " . $e->getMessage();
        }
        header("Location: accounts.php");
        exit();
    }

    if (isset($_POST['delete'])) {
        $users_id = $_POST['id'];
    
        try {
            $pdo->beginTransaction();
            
            // Delete user record
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$users_id]);
            
            $pdo->commit();
            $_SESSION['success'] = "Account deleted successfully.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Error deleting account: " . $e->getMessage();
        }
    
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
                    $stmt = $pdo->query("SELECT * FROM users ORDER BY id ASC");
                    $userss = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (count($userss) > 0):
                        foreach ($userss as $row):
                ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($row['firstname'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($row['email'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($row['customer_phone'] ?? 'N/A') ?></td>
                        <td><?= !empty($row['status']) ? htmlspecialchars($row['status']) : 'Pending Approval' ?></td>
                        <td>
                            <button class="btn edit" onclick='openEditModal(<?= json_encode($row) ?>)'>Edit</button>
                            <button class="btn view" onclick='openViewModal(<?= json_encode($row) ?>)'>View Verification</button>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <button type="submit" name="delete" class="btn delete" onclick="return confirm('Are you sure you want to delete this account?')">Delete</button>
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

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeEditModal">&times;</span>
            <h2>Edit Customer</h2>
            <form method="POST">
                <input type="hidden" name="id" id="editUsersId">
                <label for="editName">Name:</label>
                <input type="text" id="editName" name="firstname" required>
                <label for="editEmail">Email:</label>
                <input type="email" id="editEmail" name="email" required>
                <label for="editCustomerPhone">Phone:</label>
                <input type="tel" id="editCustomerPhone" name="customer_phone" required pattern="^09\d{9}$">
                <button type="submit" name="update" class="btn edit">Update</button>
            </form>
        </div>
    </div>

    <!-- View Verification Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeViewModal">&times;</span>
            <h2>Customer Verification</h2>
            <p id="verificationDetails"></p>
            <img class="verification-image" id="verificationImage" src="" alt="Submitted ID" onclick="openImageModal()" style="max-width: 82%; width: 100vh;">
            <p id="statusBadge" class="status-badge"></p>
            <form method="POST">
                <input type="hidden" name="id" id="verificationUsersId">
                <button type="submit" name="approve" class="btn view">Approve</button>
                <button type="submit" name="reject" class="btn delete">Reject</button>
            </form>
        </div>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeImageModal">&times;</span>
            <img id="fullImage" src="" alt="Submitted ID" style="width:100%; height:auto;">
        </div>
    </div>

    <script>
        const editModal = document.getElementById("editModal");
        const viewModal = document.getElementById("viewModal");
        const imageModal = document.getElementById("imageModal");

        function openEditModal(user) {
            document.getElementById("editUsersId").value = user.id;
            document.getElementById("editName").value = user.firstname;
            document.getElementById("editEmail").value = user.email;
            document.getElementById("editCustomerPhone").value = user.customer_phone;
            editModal.style.display = "block";
        }

        function openViewModal(user) {
            document.getElementById("verificationDetails").innerHTML = `
                Name: ${user.firstname}<br>
                Email: ${user.email}<br>
                Phone: ${user.customer_phone}
            `;
            
            const verificationImage = document.getElementById("verificationImage");
            if (user.submitted_id) {
                const imagePath = `../uploads/${user.submitted_id}`;
                verificationImage.src = imagePath;
                verificationImage.style.display = "block";
                verificationImage.setAttribute("data-full-image", imagePath);
            } else {
                verificationImage.style.display = "none";
            }
            
            document.getElementById("verificationUsersId").value = user.id;
            const badge = document.getElementById("statusBadge");
            badge.textContent = user.status || 'Pending Approval';
            badge.className = `status-badge ${(user.status || 'pending').toLowerCase()}`;
            viewModal.style.display = "block";
        }

        function openImageModal() {
            const verificationImage = document.getElementById("verificationImage");
            const fullImage = document.getElementById("fullImage");
            const imagePath = verificationImage.getAttribute("data-full-image");
            
            if (imagePath) {
                fullImage.src = imagePath;
                imageModal.style.display = "block";
            }
        }

        // Close modal handlers
        document.querySelectorAll(".close").forEach(closeBtn => {
            closeBtn.onclick = function() {
                this.closest(".modal").style.display = "none";
            }
        });

        window.onclick = function(event) {
            if (event.target.classList.contains("modal")) {
                event.target.style.display = "none";
            }
        }
    </script>

    <script src="https://kit.fontawesome.com/b7bdbf86fb.js" crossorigin="anonymous"></script>
</body>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

</html>
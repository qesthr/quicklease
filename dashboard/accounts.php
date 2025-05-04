<?php include '../db.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin | Accounts</title>
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>  

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include 'includes/topbar.php'; ?>

        <div class="account-content">
            <div class="table-container">
                <table>
                    <thead>
                        <!-- Add a row for the "Customer" heading -->
                        <tr>
                            <th colspan="5" style="text-align: center; font-size: 1.5em; padding: 10px;">Customer</th>
                        </tr>
                        <!-- Table headers -->
                        <tr>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    try {
                        // Fetch all customers using PDO
                        $stmt = $pdo->query("SELECT * FROM customer ORDER BY customer_id ASC");
                        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        if (count($customers) > 0):
                            foreach ($customers as $row):
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($row['customer_id'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($row['customer_name'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($row['customer_email'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($row['customer_phone'] ?? 'N/A') ?></td>
                            <td>
                                <button class="btn edit">Edit</button>
                                <button class="btn view">View Verification</button>
                                <button class="btn delete">Delete</button>
                            </td>
                        </tr>
                    <?php
                            endforeach;
                        else:
                    ?>
                        <tr>
                            <td colspan="5">No customer accounts found.</td>
                        </tr>
                    <?php
                        endif;
                    } catch (PDOException $e) {
                        echo "<tr><td colspan='5'>Error fetching customer accounts: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>
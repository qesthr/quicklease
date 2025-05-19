<?php
require_once '../includes/session_handler.php';
require_once '../db.php';

// Start admin session and check access
startAdminSession();
requireAdmin();

// Handle Admin Account Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        switch ($_POST['action']) {
            case 'create_admin':
                $username = trim($_POST['username']);
                $email = trim($_POST['email']);
                $password = $_POST['password'];
                $firstname = trim($_POST['firstname']);
                $lastname = trim($_POST['lastname']);
                $phone = trim($_POST['customer_phone']);

                // Validate inputs
                if (empty($username) || empty($email) || empty($password) || empty($firstname) || empty($lastname)) {
                    throw new Exception("All fields are required.");
                }

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("Invalid email format.");
                }

                // Check if username or email already exists
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);
                if ($stmt->fetchColumn() > 0) {
                    throw new Exception("Username or email already exists.");
                }

                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert new admin user
                $stmt = $pdo->prepare("INSERT INTO users (firstname, lastname, username, email, password, customer_phone, status, user_type) VALUES (?, ?, ?, ?, ?, ?, 'Approved', 'admin')");
                $stmt->execute([$firstname, $lastname, $username, $email, $hashed_password, $phone]);

                $_SESSION['success'] = "Admin account created successfully.";
                break;

            case 'delete_admin':
                if (isset($_POST['admin_id'])) {
                    $admin_id = $_POST['admin_id'];
                    
                    // Prevent deleting your own account
                    if ($admin_id == $_SESSION['user_id']) {
                        throw new Exception("You cannot delete your own account.");
                    }

                    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND user_type = 'admin'");
                    $stmt->execute([$admin_id]);
                    $_SESSION['success'] = "Admin account deleted successfully.";
                }
                break;
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    
    header("Location: settings.php");
    exit;
}

// Fetch admin users
$admin_users = $pdo->query("SELECT * FROM users WHERE user_type = 'admin' ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | QuickLease Admin</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/settings.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="settings-content">
        <?php include 'includes/topbar.php'; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Quick Settings Cards -->
            <div class="settings-card">
                <h3><i class="fas fa-map-marker-alt"></i> Location Management</h3>
                <p>Manage pickup/drop-off locations, service areas, and branch information.</p>
                <a href="location_settings.php" class="btn-settings">
                    <i class="fas fa-cog"></i> Manage Locations
                </a>
            </div>


        <!-- Original Admin Management section continues below -->
        <div class="settings-container">
            <div class="admin-list">
                <div class="list-header">
                    <h3>Admin Management</h3>
                    <button class="btn-add" onclick="openModal('addAdminModal')">
                        <i class="fas fa-plus"></i> Add Admin
                    </button>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($admin_users as $admin): ?>
                        <tr>
                            <td><?= htmlspecialchars($admin['firstname'] . ' ' . $admin['lastname']) ?></td>
                            <td><?= htmlspecialchars($admin['username']) ?></td>
                            <td><?= htmlspecialchars($admin['email']) ?></td>
                            <td><?= htmlspecialchars($admin['customer_phone']) ?></td>
                            <td><?= date('M d, Y', strtotime($admin['created_at'])) ?></td>
                            <td class="actions">
                                <?php if ($admin['id'] !== $_SESSION['user_id']): ?>
                                <button class="btn-delete" onclick="deleteAdmin(<?= $admin['id'] ?>)">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Admin Modal -->
    <div class="modal" id="addAdminModal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('addAdminModal')">&times;</span>
            <h2>Add New Administrator</h2>
            <form method="POST" class="admin-form">
                <input type="hidden" name="action" value="create_admin">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="firstname">First Name</label>
                        <input type="text" name="firstname" id="firstname" required>
                    </div>

                    <div class="form-group">
                        <label for="lastname">Last Name</label>
                        <input type="text" name="lastname" id="lastname" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" required>
                </div>

                <div class="form-group">
                    <label for="customer_phone">Phone Number</label>
                    <input type="tel" name="customer_phone" id="customer_phone" required>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal('addAdminModal')">Cancel</button>
                    <button type="submit" class="btn-save">Create Admin</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) {
            document.getElementById(id).style.display = 'block';
        }

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }

        function deleteAdmin(adminId) {
            if (confirm('Are you sure you want to delete this administrator?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_admin">
                    <input type="hidden" name="admin_id" value="${adminId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>

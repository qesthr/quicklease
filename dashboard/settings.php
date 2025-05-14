<?php
session_start();
require_once __DIR__ . '/../db.php';

$email_error = '';
$username_error = '';
$phone_error = '';
$admin_message = '';
$show_form = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    $afirstname = trim($_POST['afirstname'] ?? '');
    $alastname = trim($_POST['alastname'] ?? '');
    $ausername = trim($_POST['ausername'] ?? '');
    $aemail = trim($_POST['aemail'] ?? '');
    $apassword = $_POST['apassword'] ?? '';
    $aphone = trim($_POST['aphone'] ?? '');

    $show_form = true;

    if (!$afirstname || !$alastname || !$ausername || !$aemail || !$apassword || !$aphone) {
        $admin_message = 'All fields are required.';
    } elseif (!filter_var($aemail, FILTER_VALIDATE_EMAIL)) {
        $email_error = 'Invalid email address.';
    } else {
        $stmt = $pdo->prepare("SELECT username, email, phone FROM users WHERE username = ? OR email = ? OR phone = ?");
        $stmt->execute([$ausername, $aemail, $aphone]);

        while ($row = $stmt->fetch()) {
            if ($row['email'] === $aemail) {
                $email_error = 'This email is already used.';
            }
            if ($row['username'] === $ausername) {
                $username_error = 'This username is already taken.';
            }
            if ($row['phone'] === $aphone) {
                $phone_error = 'This phone number is already registered.';
            }
        }

        if (!$email_error && !$username_error && !$phone_error) {
            $hashed_password = password_hash($apassword, PASSWORD_DEFAULT);
            $insert_stmt = $pdo->prepare("INSERT INTO users (firstname, lastname, username, email, password, phone, status, user_type, created_at) VALUES (?, ?, ?, ?, ?, ?, 'Approved', 'admin', NOW())");
            if ($insert_stmt->execute([$afirstname, $alastname, $ausername, $aemail, $hashed_password, $aphone])) {
                $admin_message = 'New admin added successfully.';
                $show_form = false;
            } else {
                $admin_message = 'Failed to add new admin.';
            }
        }
    }
}

// Fetch existing admins
$admins = $pdo->query("SELECT id, firstname, lastname, username, email, customer_phone, status, created_at FROM users WHERE user_type = 'admin'")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_admin'])) {
    $delete_admin_id = intval($_POST['delete_admin']);
    // Prevent deleting self
    if ($delete_admin_id === $_SESSION['user_id']) {
        $admin_message = 'You cannot delete your own admin account.';
    } else {
        $delete_stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND user_type = 'admin'");
        if ($delete_stmt->execute([$delete_admin_id])) {
            $admin_message = 'Admin account deleted successfully.';
        } else {
            $admin_message = 'Failed to delete admin account.';
        }
    }
    // Refresh the admin list after deletion
    $admins = $pdo->query("SELECT id, firstname, lastname, username, email, phone, status, created_at FROM users WHERE user_type = 'admin'")->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Settings</title>
    <link rel="stylesheet" href="../css/dashboard.css" />
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
        }
        .settings-form {
            max-width: 700px;
            margin: 30px auto;
            background: #ffffff;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .settings-form h2 {
            margin-bottom: 25px;
            font-size: 28px;
            color: #333;
            text-align: center;
        }
        .settings-form label {
            display: block;
            margin-top: 20px;
            font-weight: 600;
            color: #555;
        }
        .settings-form input[type="text"],
        .settings-form input[type="email"],
        .settings-form textarea {
            width: 100%;
            padding: 12px 15px;
            margin-top: 8px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
            transition: border-color 0.3s ease;
        }
        .settings-form input[type="text"]:focus,
        .settings-form input[type="email"]:focus,
        .settings-form textarea:focus {
            border-color: #007bff;
            outline: none;
        }
        .settings-form input[type="checkbox"] {
            margin-top: 15px;
            transform: scale(1.2);
            cursor: pointer;
        }
        .settings-form button {
            margin-top: 30px;
            padding: 12px 25px;
            background-color: #007bff;
            border: none;
            color: white;
            cursor: pointer;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            transition: background-color 0.3s ease;
            display: block;
            width: 100%;
        }
        .settings-form button:hover {
            background-color: #0056b3;
        }
        .message {
            margin-top: 20px;
            font-weight: 700;
            color: green;
            text-align: center;
        }
        .error {
            color: red;
            text-align: center;
            font-weight: 700;
        }
        /* Admin Users Section */
        section.admin-users {
            max-width: 700px;
            margin: 40px auto;
            background: #ffffff;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        section.admin-users h2 {
            margin-bottom: 25px;
            font-size: 28px;
            color: #333;
            text-align: center;
        }
        section.admin-users button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 12px 25px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: block;
            margin: 0 auto 25px auto;
        }
        section.admin-users button:hover {
            background-color: #1e7e34;
        }
        #addAdminForm {
            display: none;
            margin-top: 15px;
        }
        #addAdminForm label {
            display: block;
            margin-top: 15px;
            font-weight: 600;
            color: #555;
        }
        #addAdminForm input[type="text"],
        #addAdminForm input[type="email"],
        #addAdminForm input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            margin-top: 8px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
            transition: border-color 0.3s ease;
        }
        #addAdminForm input[type="text"]:focus,
        #addAdminForm input[type="email"]:focus,
        #addAdminForm input[type="password"]:focus {
            border-color: #28a745;
            outline: none;
        }
        #addAdminForm button {
            margin-top: 20px;
            padding: 12px 25px;
            background-color: #28a745;
            border: none;
            color: white;
            cursor: pointer;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            transition: background-color 0.3s ease;
            display: block;
            width: 100%;
        }
        #addAdminForm button:hover {
            background-color: #1e7e34;
        }
        section.admin-users table {
            width: 100%;
            max-width: 800px;
            border-collapse: collapse;
            margin-top: 25px;
        }
        section.admin-users table th,
        section.admin-users table td {
            border: 1px solid #ddd;
            padding: 10px 12px;
            text-align: left;
            font-size: 14px;
        }
        section.admin-users table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        section.admin-users table tbody tr:hover {
            background-color: #f1f1f1;
        }
        .error { color: red; font-weight: bold; margin-bottom: 8px; }
        .message { text-align: center; font-weight: bold; margin-top: 15px; }
        .success { color: green; }
        .fail { color: red; }
    </style>
</head>
<body>
<?php include 'includes/sidebar.php'; ?>
<div class="main-content" style="margin-left: 220px; padding: 20px;">

<section class="admin-users">
    <h2>Admin Users</h2>

    <?php if ($admin_message): ?>
        <p class="message <?= strpos($admin_message, 'successfully') !== false ? 'success' : 'fail' ?>">
            <?= htmlspecialchars($admin_message) ?>
        </p>
    <?php endif; ?>

    <button id="showAddAdminFormBtn">Add Admin</button>

    <form id="addAdminForm" method="POST" action="settings.php" style="display: <?= $show_form ? 'block' : 'none' ?>; margin-top: 15px;">
        <input type="hidden" name="add_admin" value="1" />
        <label for="afirstname">First Name</label>
        <input type="text" id="afirstname" name="afirstname" value="<?= htmlspecialchars($_POST['afirstname'] ?? '') ?>" required />

        <label for="alastname">Last Name</label>
        <input type="text" id="alastname" name="alastname" value="<?= htmlspecialchars($_POST['alastname'] ?? '') ?>" required />

        <label for="ausername">Username</label>
        <input type="text" id="ausername" name="ausername" value="<?= htmlspecialchars($_POST['ausername'] ?? '') ?>" required />

        <label for="aemail">Email</label>
        <?php if ($email_error): ?>
            <div class="error"><?= htmlspecialchars($email_error) ?></div>
        <?php endif; ?>
        <input type="email" id="aemail" name="aemail" value="<?= htmlspecialchars($_POST['aemail'] ?? '') ?>" required />

        <label for="apassword">Password</label>
        <input type="password" id="apassword" name="apassword" required />

        <label for="aphone">Phone Number</label>
        <input type="text" id="aphone" name="aphone" value="<?= htmlspecialchars($_POST['aphone'] ?? '') ?>" required />

        <button type="submit">Add Admin</button>
    </form>

    <h3>Existing Admins</h3>
    <table border="1" cellpadding="8" cellspacing="0" style="width: 100%; max-width: 800px;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($admins as $admin): ?>
                <tr>
                    <td><?= htmlspecialchars($admin['id']) ?></td>
                    <td><?= htmlspecialchars($admin['firstname'] . ' ' . $admin['lastname']) ?></td>
                    <td><?= htmlspecialchars($admin['username']) ?></td>
                    <td><?= htmlspecialchars($admin['email']) ?></td>
                    <td><?= htmlspecialchars($admin['customer_phone']) ?></td>
                    <td><?= htmlspecialchars($admin['status']) ?></td>
                    <td><?= htmlspecialchars($admin['created_at']) ?></td>
                    <td>
                        <?php if ($admin['id'] !== $_SESSION['user_id']): ?>
                            <form method="POST" action="settings.php" class="delete-admin-form" data-admin-id="<?= htmlspecialchars($admin['id']) ?>" style="display:inline;">
                                <input type="hidden" name="delete_admin" value="<?= htmlspecialchars($admin['id']) ?>" />
                                <button type="button" class="delete-admin-btn" style="background-color: #dc3545; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer;">Delete</button>
                            </form>
                        <?php else: ?>
                            <em>Current User</em>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div id="deleteConfirmationMessage" class="modal" style="display: none;">
        <div class="modal-content" style="background-color: #fff3cd; border: 1px solid #ffeeba; border-radius: 6px; color: #856404; padding: 20px; max-width: 500px; margin: 100px auto; position: relative;">
            <p id="deleteMessageText" style="margin: 0 0 20px 0; font-weight: bold;"></p>
            <button id="confirmDeleteBtn" style="background-color: #dc3545; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; margin-right: 10px;">Confirm</button>
            <button id="cancelDeleteBtn" style="background-color: #6c757d; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer;">Cancel</button>
        </div>
    </div>
    <style>
        .modal {
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .modal-content {
            background-color: #fff;
            border-radius: 8px;
            padding: 25px 30px;
            max-width: 450px;
            width: 90%;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            position: relative;
            text-align: center;
        }
        #deleteMessageText {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 25px;
        }
        #confirmDeleteBtn, #cancelDeleteBtn {
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
            min-width: 100px;
        }
        #confirmDeleteBtn {
            background-color: #dc3545;
            color: white;
            margin-right: 15px;
        }
        #confirmDeleteBtn:hover {
            background-color: #b02a37;
        }
        #cancelDeleteBtn {
            background-color: #6c757d;
            color: white;
        }
        #cancelDeleteBtn:hover {
            background-color: #565e64;
        }
        .modal-close {
            position: absolute;
            top: 12px;
            right: 15px;
            font-size: 22px;
            font-weight: bold;
            color: #888;
            cursor: pointer;
            transition: color 0.3s ease;
        }
        .modal-close:hover {
            color: #444;
        }
    </style>

<script>
    document.getElementById('showAddAdminFormBtn').addEventListener('click', () => {
        const form = document.getElementById('addAdminForm');
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    });

    const deleteButtons = document.querySelectorAll('.delete-admin-btn');
    const deleteConfirmationMessage = document.getElementById('deleteConfirmationMessage');
    const deleteMessageText = document.getElementById('deleteMessageText');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    let formToSubmit = null;

    deleteButtons.forEach(button => {
        button.addEventListener('click', () => {
            formToSubmit = button.closest('form');
            const adminId = formToSubmit.getAttribute('data-admin-id');
            const adminName = formToSubmit.closest('tr').querySelector('td:nth-child(2)').textContent;
            deleteMessageText.textContent = `Are you sure you want to delete the admin account: ${adminName}?`;
            deleteConfirmationMessage.style.display = 'flex';
        });
    });

    confirmDeleteBtn.addEventListener('click', () => {
        if (formToSubmit) {
            formToSubmit.submit();
        }
    });

    cancelDeleteBtn.addEventListener('click', () => {
        deleteConfirmationMessage.style.display = 'none';
        formToSubmit = null;
    });
</script>

</div>
</body>
</html>

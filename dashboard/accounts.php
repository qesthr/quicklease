<?php include '../db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Accounts</title>
    <link rel="stylesheet" href="../css/accounts.css">
</head>
<body>
<div class="sidebar">
  <div class="logo"><img src="/quicklease/images/logo.png" alt=""></div>
  <a href="reports.php"><button class="nav-btn">Reports</button></a>
  <a href="accounts.php"><button class="nav-btn">Accounts</button></a>
  <a href="cars.php"><button class="nav-btn">Cars</button></a>
  <a href="bookings.php"><button class="nav-btn active">Bookings</button></a>
  <button class="logout-btn" onclick="window.location.href='../loginpage/login.php'">Logout</button>
</div>

<!-- Sidebar HTML (same as previous response) -->

<div class="main">
    <header>
        <h1>Accounts</h1>
        <!-- top bar -->
    </header>

    <div class="account-content">
        <h2 class="section-title">Customer</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $sql = "SELECT * FROM users ORDER BY id ASC";
                $result = $conn->query($sql);

                if ($result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['status']) ?></td>
                        <td>
                            <button class="btn edit">Edit</button>
                            <button class="btn view">View Verification</button>
                            <button class="btn delete">Delete</button>
                        </td>
                    </tr>
                <?php
                    endwhile;
                else:
                ?>
                    <tr>
                        <td colspan="5">No user accounts found.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>

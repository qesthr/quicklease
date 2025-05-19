<?php
require_once '../../includes/session_handler.php';
startClientSession();

echo "Session variables:<br>";
print_r($_SESSION);

echo "<br><br>User ID: " . ($_SESSION['user_id'] ?? 'Not set');
echo "<br>User Type: " . ($_SESSION['user_type'] ?? 'Not set');
?> 
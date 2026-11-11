
<?php
session_start();

// Clear all session variables
$_SESSION = [];

// Destroy the current session
session_destroy();

// Redirect user to login page
header("Location: index.php?logged_out=1");
exit;
?>

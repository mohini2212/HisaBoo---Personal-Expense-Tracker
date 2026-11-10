<?php
// Database connection - included by all other PHP files using: include 'db.php';
$host   = "localhost";
$user   = "root";
$pass   = "";            // XAMPP default password is empty
$dbname = "expense_tracker";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>

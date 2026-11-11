
<?php

// ======================================
// HISABOO - DATABASE CONNECTION
// ======================================

// Database configuration
$host = "localhost";
$username = "root";
$password = "";
$database = "expense_tracker";

// Enable MySQLi error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {

    // Create database connection
    $conn = new mysqli(
        $host,
        $username,
        $password,
        $database
    );

    // Set character encoding
    $conn->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {

    // Display connection error during development
    die("Database Connection Failed: " . $e->getMessage());

}

?>

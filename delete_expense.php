
<?php
session_start();
require_once 'db.php';

// Check whether user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Allow only POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: expenses.php");
    exit;
}

// Check transaction ID
if (!isset($_POST['id']) || !filter_var($_POST['id'], FILTER_VALIDATE_INT) || (int)$_POST['id'] <= 0) {
    header("Location: expenses.php?error=invalid");
    exit;
}

$id = (int) $_POST['id'];
$user_id = (int) $_SESSION['user_id'];

try {
    // Delete only the logged-in user's transaction
    $stmt = $conn->prepare(
        "DELETE FROM expenses WHERE id = ? AND user_id = ?"
    );

    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header("Location: expenses.php?deleted=1");
    } else {
        header("Location: expenses.php?error=notfound");
    }

    $stmt->close();

} catch (mysqli_sql_exception $e) {
    header("Location: expenses.php?error=deletefailed");
}

exit;
?>

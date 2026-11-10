<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];
$search  = isset($_GET['search']) ? trim($_GET['search']) : "";

if ($search != "") {
    $like = "%" . $search . "%";
    $stmt = $conn->prepare("SELECT * FROM expenses WHERE user_id=? AND (title LIKE ? OR category LIKE ?) ORDER BY expense_date DESC");
    $stmt->bind_param("iss", $user_id, $like, $like);
} else {
    $stmt = $conn->prepare("SELECT * FROM expenses WHERE user_id=? ORDER BY expense_date DESC");
    $stmt->bind_param("i", $user_id);
}
$stmt->execute();
$result = $stmt->get_result();
$total = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Expenses</title>
    <!-- Student 1 will link the main CSS file here -->
    <style>
        body{font-family:Arial,sans-serif;max-width:900px;margin:30px auto;padding:0 15px}
        table{width:100%;border-collapse:collapse;margin-top:15px}
        th,td{border:1px solid #ccc;padding:8px;text-align:left}
        th{background:#f0f0f0}
        .msg{color:green}
        form.inline{display:inline}
        .del{background:none;border:none;color:#b00020;cursor:pointer;padding:0}
    </style>
</head>
<body>
    <h2>My Expenses</h2>
    <?php
    if (isset($_GET['msg'])) {
        $m = ["added"=>"Expense added.", "updated"=>"Expense updated.", "deleted"=>"Expense deleted."];
        if (isset($m[$_GET['msg']])) echo "<p class='msg'>" . $m[$_GET['msg']] . "</p>";
    }
    ?>
    <a href="add_expense.php">+ Add New Expense</a>

    <form method="GET" action="view_expenses.php" style="margin-top:12px">
        <input type="text" name="search" placeholder="Search by title or category" value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
        <?php if ($search != "") echo "<a href='view_expenses.php'>Clear</a>"; ?>
    </form>

    <table>
        <tr><th>#</th><th>Title</th><th>Category</th><th>Amount (₹)</th><th>Date</th><th>Description</th><th>Action</th></tr>
        <?php
        $i = 1;
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $total += $row['amount'];
                echo "<tr>";
                echo "<td>" . $i++ . "</td>";
                echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                echo "<td>" . number_format($row['amount'], 2) . "</td>";
                echo "<td>" . $row['expense_date'] . "</td>";
                echo "<td>" . htmlspecialchars($row['description'] ?? '') . "</td>";
                echo "<td><a href='edit_expense.php?id=" . $row['id'] . "'>Edit</a> | ";
                echo "<form class='inline' method='POST' action='delete_expense.php' onsubmit=\"return confirm('Delete this expense?');\">";
                echo "<input type='hidden' name='id' value='" . $row['id'] . "'>";
                echo "<button class='del' type='submit'>Delete</button></form></td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='7'>No expenses found.</td></tr>";
        }
        ?>
        <tr><th colspan="3">Total</th><th colspan="4">₹ <?php echo number_format($total, 2); ?></th></tr>
    </table>
</body>
</html>

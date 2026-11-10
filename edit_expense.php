<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];
$error = "";
$categories = ["Food", "Travel", "Shopping", "Bills", "Entertainment", "Health", "Education", "Other"];

// UPDATE (runs when the form is submitted)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id       = (int)$_POST['id'];
    $title    = trim($_POST['title']);
    $amount   = $_POST['amount'];
    $category = $_POST['category'];
    $date     = $_POST['expense_date'];
    $desc     = trim($_POST['description']);

    if ($title == "" || $category == "" || $date == "" || !is_numeric($amount) || $amount <= 0) {
        $error = "Please fill all fields correctly. Amount must be greater than 0.";
        $expense = ['id'=>$id,'title'=>$title,'amount'=>$amount,'category'=>$category,'expense_date'=>$date,'description'=>$desc];
    } else {
        $stmt = $conn->prepare("UPDATE expenses SET title=?, amount=?, category=?, expense_date=?, description=? WHERE id=? AND user_id=?");
        $stmt->bind_param("sdsssii", $title, $amount, $category, $date, $desc, $id, $user_id);
        $stmt->execute();
        header("Location: view_expenses.php?msg=updated");
        exit;
    }
} else {
    // Fetch the existing data to pre-fill the form
    if (!isset($_GET['id'])) { header("Location: view_expenses.php"); exit; }
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM expenses WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $expense = $stmt->get_result()->fetch_assoc();
    if (!$expense) { header("Location: view_expenses.php"); exit; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Expense</title>
    <!-- Student 1 will link the main CSS file here -->
    <style>
        body{font-family:Arial,sans-serif;max-width:480px;margin:30px auto;padding:0 15px}
        label{display:block;margin-top:12px}
        input,select,textarea{width:100%;padding:8px;box-sizing:border-box}
        button{margin-top:15px;padding:10px 18px}
        .error{color:#b00020}
    </style>
</head>
<body>
    <h2>Edit Expense</h2>
    <?php if ($error) echo "<p class='error'>$error</p>"; ?>
    <form method="POST" action="edit_expense.php">
        <input type="hidden" name="id" value="<?php echo (int)$expense['id']; ?>">

        <label>Title</label>
        <input type="text" name="title" value="<?php echo htmlspecialchars($expense['title']); ?>" required>

        <label>Amount (₹)</label>
        <input type="number" name="amount" step="0.01" min="0.01" value="<?php echo htmlspecialchars($expense['amount']); ?>" required>

        <label>Category</label>
        <select name="category" required>
            <?php foreach ($categories as $c) {
                $sel = ($c == $expense['category']) ? "selected" : "";
                echo "<option value='$c' $sel>$c</option>";
            } ?>
        </select>

        <label>Date</label>
        <input type="date" name="expense_date" value="<?php echo htmlspecialchars($expense['expense_date']); ?>" required>

        <label>Description</label>
        <textarea name="description" rows="3"><?php echo htmlspecialchars($expense['description'] ?? ''); ?></textarea>

        <button type="submit">Update Expense</button>
        <a href="view_expenses.php">Cancel</a>
    </form>
</body>
</html>

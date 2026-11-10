<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id  = $_SESSION['user_id'];
    $title    = trim($_POST['title']);
    $amount   = $_POST['amount'];
    $category = $_POST['category'];
    $date     = $_POST['expense_date'];
    $desc     = trim($_POST['description']);

    // Server-side validation (still safe if JavaScript is disabled)
    if ($title == "" || $category == "" || $date == "" || !is_numeric($amount) || $amount <= 0) {
        $error = "Please fill all fields correctly. Amount must be greater than 0.";
    } else {
        $stmt = $conn->prepare("INSERT INTO expenses (user_id, title, amount, category, expense_date, description) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isdsss", $user_id, $title, $amount, $category, $date, $desc);
        $stmt->execute();
        header("Location: view_expenses.php?msg=added");
        exit;
    }
}
$categories = ["Food", "Travel", "Shopping", "Bills", "Entertainment", "Health", "Education", "Other"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Expense</title>
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
    <h2>Add Expense</h2>
    <?php if ($error) echo "<p class='error'>$error</p>"; ?>
    <form method="POST" action="add_expense.php" id="expenseForm">
        <label>Title</label>
        <input type="text" name="title" required>

        <label>Amount (₹)</label>
        <input type="number" name="amount" step="0.01" min="0.01" required>

        <label>Category</label>
        <select name="category" required>
            <option value="">-- Select --</option>
            <?php foreach ($categories as $c) echo "<option value='$c'>$c</option>"; ?>
        </select>

        <label>Date</label>
        <input type="date" name="expense_date" value="<?php echo date('Y-m-d'); ?>" required>

        <label>Description (optional)</label>
        <textarea name="description" rows="3"></textarea>

        <button type="submit">Save Expense</button>
        <a href="view_expenses.php">Cancel</a>
    </form>
</body>
</html>

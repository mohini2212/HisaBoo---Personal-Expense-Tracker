
<?php
session_start();
require_once "db.php";

// Check whether the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'User';

$error = "";
$success = "";

// Allowed form values
$allowedCategories = [
    "Food & Dining",
    "Transportation",
    "Shopping",
    "Entertainment",
    "Education",
    "Healthcare",
    "Utilities",
    "Other"
];

$allowedPayments = [
    "Cash",
    "UPI",
    "Debit Card",
    "Credit Card",
    "Net Banking"
];

$allowedTypes = ["Expense", "Income"];

// Process submitted form
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST['title'] ?? '');
    $amount = $_POST['amount'] ?? '';
    $category = $_POST['category'] ?? '';
    $expense_date = $_POST['date'] ?? '';
    $payment_method = $_POST['payment'] ?? '';
    $type = $_POST['type'] ?? '';
    $description = trim($_POST['description'] ?? '');

    if (
        $title === "" ||
        $amount === "" ||
        $category === "" ||
        $expense_date === "" ||
        $payment_method === "" ||
        $type === ""
    ) {
        $error = "Please fill in all required fields.";

    } elseif (!is_numeric($amount) || (float)$amount <= 0) {
        $error = "Please enter a valid amount greater than zero.";

    } elseif (
        !in_array($category, $allowedCategories, true) ||
        !in_array($payment_method, $allowedPayments, true) ||
        !in_array($type, $allowedTypes, true)
    ) {
        $error = "Invalid category, payment method, or transaction type.";

    } else {

        $dateObject = DateTime::createFromFormat('!Y-m-d', $expense_date);

        if (
            !$dateObject ||
            $dateObject->format('Y-m-d') !== $expense_date
        ) {
            $error = "Please enter a valid transaction date.";

        } else {

            $amount = (float)$amount;

            $stmt = $conn->prepare("
                INSERT INTO expenses
                (
                    user_id,
                    title,
                    amount,
                    category,
                    expense_date,
                    description,
                    payment_method,
                    type
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "isdsssss",
                $user_id,
                $title,
                $amount,
                $category,
                $expense_date,
                $description,
                $payment_method,
                $type
            );

            $stmt->execute();
            $stmt->close();

            header("Location: dashboard.php?saved=1");
            exit;
        }
    }
}

function clean($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HISABOO | Add Transaction</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<input type="checkbox" id="theme-toggle">

<label for="theme-toggle" class="theme-switch">
    <span class="sun-icon">☀️</span>
    <span class="moon-icon">🌙</span>
    <span>Switch Theme</span>
</label>

<div class="app-layout">

    <aside class="sidebar">

        <a href="dashboard.php" class="brand-logo">
            <span class="logo-icon">H</span>
            HISABOO<span class="logo-dot">.</span>
        </a>

        <p class="sidebar-label">MAIN MENU</p>

        <nav class="side-nav">

            <a href="dashboard.php" class="side-link">
                ◈ Dashboard
            </a>

            <a href="add-expense.php" class="side-link active">
                ＋ Add Expense
            </a>

            <a href="expenses.php" class="side-link">
                ▤ All Expenses
            </a>

        </nav>

        <p class="sidebar-label">ACCOUNT</p>

        <nav class="side-nav">
            <a href="logout.php" class="side-link">
                ↪ Logout
            </a>
        </nav>

    </aside>

    <main class="main-content">

        <header class="topbar">

            <div>
                <p class="topbar-kicker">TRANSACTIONS</p>
                <h1>Add Expense</h1>
            </div>

            <div class="avatar">
                <?php echo clean(strtoupper(substr($user_name, 0, 1))); ?>
            </div>

        </header>

        <section class="page-intro">

            <span class="welcome-label">NEW TRANSACTION</span>

            <h2>Record your transaction</h2>

            <p>
                Enter the details below to record your income or expense.
            </p>

        </section>

        <section class="form-panel">

            <form class="app-form" action="add-expense.php" method="POST">

                <?php if ($error !== ""): ?>
                    <div class="error-message">
                        <?php echo clean($error); ?>
                    </div>
                <?php endif; ?>

                <div class="form-grid">

                    <div class="input-group">

                        <label for="expense-title">
                            Transaction Title
                        </label>

                        <input
                            type="text"
                            id="expense-title"
                            name="title"
                            class="field-control"
                            placeholder="e.g. Grocery Shopping"
                            value="<?php echo clean($_POST['title'] ?? ''); ?>"
                            maxlength="150"
                            required
                        >

                    </div>

                    <div class="input-group">

                        <label for="amount">
                            Amount (₹)
                        </label>

                        <input
                            type="number"
                            id="amount"
                            name="amount"
                            class="field-control"
                            placeholder="Enter amount"
                            min="0.01"
                            step="0.01"
                            value="<?php echo clean($_POST['amount'] ?? ''); ?>"
                            required
                        >

                    </div>

                    <div class="input-group">

                        <label for="category">Category</label>

                        <select
                            id="category"
                            name="category"
                            class="field-control"
                            required
                        >

                            <option value="">Select Category</option>

                            <?php foreach ($allowedCategories as $cat): ?>
                                <option
                                    value="<?php echo clean($cat); ?>"
                                    <?php echo (($_POST['category'] ?? '') === $cat) ? 'selected' : ''; ?>
                                >
                                    <?php echo clean($cat); ?>
                                </option>
                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="input-group">

                        <label for="date">Transaction Date</label>

                        <input
                            type="date"
                            id="date"
                            name="date"
                            class="field-control"
                            value="<?php echo clean($_POST['date'] ?? date('Y-m-d')); ?>"
                            required
                        >

                    </div>

                    <div class="input-group">

                        <label for="payment">Payment Method</label>

                        <select
                            id="payment"
                            name="payment"
                            class="field-control"
                            required
                        >

                            <option value="">Select Payment Method</option>

                            <?php foreach ($allowedPayments as $method): ?>
                                <option
                                    value="<?php echo clean($method); ?>"
                                    <?php echo (($_POST['payment'] ?? '') === $method) ? 'selected' : ''; ?>
                                >
                                    <?php echo clean($method); ?>
                                </option>
                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="input-group">

                        <label for="type">Transaction Type</label>

                        <select
                            id="type"
                            name="type"
                            class="field-control"
                            required
                        >

                            <option value="">Select Transaction Type</option>

                            <?php foreach ($allowedTypes as $transactionType): ?>
                                <option
                                    value="<?php echo clean($transactionType); ?>"
                                    <?php echo (($_POST['type'] ?? '') === $transactionType) ? 'selected' : ''; ?>
                                >
                                    <?php echo clean($transactionType); ?>
                                </option>
                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="input-group full-width">

                        <label for="description">Description</label>

                        <textarea
                            id="description"
                            name="description"
                            class="field-control"
                            placeholder="Write a short description..."
                        ><?php echo clean($_POST['description'] ?? ''); ?></textarea>

                    </div>

                </div>

                <div class="form-actions">

                    <a href="dashboard.php" class="outline-btn">
                        Cancel
                    </a>

                    <button type="submit" class="primary-btn">
                        Save Transaction →
                    </button>

                </div>

            </form>

        </section>

        <footer class="page-footer">
            © 2026 HISABOO | Personal Expense Tracker
        </footer>

    </main>

</div>

</body>
</html>

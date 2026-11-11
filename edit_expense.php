
<?php
session_start();
require_once "db.php";

// Check login session
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

$error = "";
$expense = null;

// Categories matching Add Expense form
$categories = [
    "Food & Dining",
    "Transportation",
    "Shopping",
    "Entertainment",
    "Education",
    "Healthcare",
    "Utilities",
    "Other"
];

$payments = [
    "Cash",
    "UPI",
    "Debit Card",
    "Credit Card",
    "Net Banking"
];

$types = ["Expense", "Income"];

// Get transaction ID
$id = 0;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = (int)($_POST['id'] ?? 0);
} else {
    $id = (int)($_GET['id'] ?? 0);
}

if ($id <= 0) {
    header("Location: expenses.php");
    exit;
}

// Fetch transaction belonging to the logged-in user
$stmt = $conn->prepare("
    SELECT *
    FROM expenses
    WHERE id = ? AND user_id = ?
");

$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();

$expense = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$expense) {
    header("Location: expenses.php");
    exit;
}

// Update transaction
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST['title'] ?? '');
    $amount = $_POST['amount'] ?? '';
    $category = $_POST['category'] ?? '';
    $date = $_POST['expense_date'] ?? '';
    $payment = $_POST['payment_method'] ?? '';
    $type = $_POST['type'] ?? '';
    $description = trim($_POST['description'] ?? '');

    if (
        $title === "" ||
        $amount === "" ||
        $category === "" ||
        $date === "" ||
        $payment === "" ||
        $type === ""
    ) {
        $error = "Please fill in all required fields.";

    } elseif (!is_numeric($amount) || (float)$amount <= 0) {
        $error = "Please enter a valid amount greater than zero.";

    } elseif (
        !in_array($category, $categories, true) ||
        !in_array($payment, $payments, true) ||
        !in_array($type, $types, true)
    ) {
        $error = "Please select valid category, payment method and transaction type.";

    } else {

        $dateObject = DateTime::createFromFormat('!Y-m-d', $date);

        if (!$dateObject || $dateObject->format('Y-m-d') !== $date) {
            $error = "Please enter a valid transaction date.";

        } else {

            $amount = (float)$amount;

            $updateStmt = $conn->prepare("
                UPDATE expenses
                SET
                    title = ?,
                    amount = ?,
                    category = ?,
                    expense_date = ?,
                    description = ?,
                    payment_method = ?,
                    type = ?
                WHERE id = ? AND user_id = ?
            ");

            $updateStmt->bind_param(
                "sdsssssii",
                $title,
                $amount,
                $category,
                $date,
                $description,
                $payment,
                $type,
                $id,
                $user_id
            );

            $updateStmt->execute();
            $updateStmt->close();

            header("Location: expenses.php?updated=1");
            exit;
        }
    }

    // Preserve entered values if validation fails
    $expense['title'] = $title;
    $expense['amount'] = $amount;
    $expense['category'] = $category;
    $expense['expense_date'] = $date;
    $expense['payment_method'] = $payment;
    $expense['type'] = $type;
    $expense['description'] = $description;
}

// Safe output function
function clean($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>HISABOO | Edit Transaction</title>

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

            <a href="add-expense.php" class="side-link">
                ＋ Add Expense
            </a>

            <a href="expenses.php" class="side-link active">
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
                <p class="topbar-kicker">TRANSACTION MANAGEMENT</p>
                <h1>Edit Transaction</h1>
            </div>

            <div class="avatar">
                <?php echo clean(strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1))); ?>
            </div>

        </header>

        <section class="page-intro">

            <span class="welcome-label">UPDATE RECORD</span>

            <h2>Edit your transaction</h2>

            <p>
                Make changes to the transaction details below.
            </p>

        </section>

        <section class="form-panel">

            <form
                class="app-form"
                action="edit_expense.php?id=<?php echo $id; ?>"
                method="POST"
            >

                <?php if ($error !== ""): ?>
                    <div class="error-message">
                        <?php echo clean($error); ?>
                    </div>
                <?php endif; ?>

                <input
                    type="hidden"
                    name="id"
                    value="<?php echo $id; ?>"
                >

                <div class="form-grid">

                    <div class="input-group">

                        <label for="title">Transaction Title</label>

                        <input
                            type="text"
                            id="title"
                            name="title"
                            class="field-control"
                            maxlength="150"
                            value="<?php echo clean($expense['title']); ?>"
                            required
                        >

                    </div>

                    <div class="input-group">

                        <label for="amount">Amount (₹)</label>

                        <input
                            type="number"
                            id="amount"
                            name="amount"
                            class="field-control"
                            min="0.01"
                            step="0.01"
                            value="<?php echo clean($expense['amount']); ?>"
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

                            <?php foreach ($categories as $cat): ?>
                                <option
                                    value="<?php echo clean($cat); ?>"
                                    <?php echo ($expense['category'] === $cat) ? 'selected' : ''; ?>
                                >
                                    <?php echo clean($cat); ?>
                                </option>
                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="input-group">

                        <label for="expense_date">Transaction Date</label>

                        <input
                            type="date"
                            id="expense_date"
                            name="expense_date"
                            class="field-control"
                            value="<?php echo clean($expense['expense_date']); ?>"
                            required
                        >

                    </div>

                    <div class="input-group">

                        <label for="payment_method">Payment Method</label>

                        <select
                            id="payment_method"
                            name="payment_method"
                            class="field-control"
                            required
                        >

                            <option value="">Select Payment Method</option>

                            <?php foreach ($payments as $method): ?>
                                <option
                                    value="<?php echo clean($method); ?>"
                                    <?php echo ($expense['payment_method'] === $method) ? 'selected' : ''; ?>
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

                            <?php foreach ($types as $transactionType): ?>
                                <option
                                    value="<?php echo clean($transactionType); ?>"
                                    <?php echo ($expense['type'] === $transactionType) ? 'selected' : ''; ?>
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
                        ><?php echo clean($expense['description'] ?? ''); ?></textarea>

                    </div>

                </div>

                <div class="form-actions">

                    <a href="expenses.php" class="outline-btn">
                        Cancel
                    </a>

                    <button type="submit" class="primary-btn">
                        Update Transaction →
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


<?php
session_start();
require_once "db.php";

// Check login session
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'User';

// Allowed categories
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

// Read search and filter values
$search = trim($_GET['search'] ?? '');
$category = $_GET['category'] ?? '';
$month = $_GET['month'] ?? '';

// Validate category filter
if ($category !== '' && !in_array($category, $categories, true)) {
    $category = '';
}

// Validate month filter (YYYY-MM)
if ($month !== '' && !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
    $month = '';
}

// Get summary statistics for the logged-in user
$summaryStmt = $conn->prepare("
    SELECT
        COALESCE(SUM(
            CASE
                WHEN type = 'Expense'
                AND YEAR(expense_date) = YEAR(CURDATE())
                AND MONTH(expense_date) = MONTH(CURDATE())
                THEN amount
                ELSE 0
            END
        ), 0) AS monthly_expenses,

        COUNT(*) AS total_records,

        COALESCE(AVG(
            CASE WHEN type = 'Expense' THEN amount ELSE NULL END
        ), 0) AS average_expense,

        COALESCE(MAX(
            CASE WHEN type = 'Expense' THEN amount ELSE NULL END
        ), 0) AS highest_expense

    FROM expenses
    WHERE user_id = ?
");

$summaryStmt->bind_param("i", $user_id);
$summaryStmt->execute();

$summary = $summaryStmt->get_result()->fetch_assoc();
$summaryStmt->close();

// Fetch transactions with search and filters
$stmt = $conn->prepare("
    SELECT
        id,
        title,
        amount,
        category,
        expense_date,
        payment_method,
        type,
        description
    FROM expenses
    WHERE user_id = ?
    AND (? = '' OR CONCAT_WS(' ', title, category, description)
        LIKE CONCAT('%', ?, '%'))
    AND (? = '' OR category = ?)
    AND (? = '' OR DATE_FORMAT(expense_date, '%Y-%m') = ?)
    ORDER BY expense_date DESC, id DESC
");

$stmt->bind_param(
    "issssss",
    $user_id,
    $search,
    $search,
    $category,
    $category,
    $month,
    $month
);

$stmt->execute();
$transactions = $stmt->get_result();

// Helper functions
function clean($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function money($amount) {
    return number_format((float)$amount, 2);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>HISABOO | All Transactions</title>

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

    <!-- Sidebar -->
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

    <!-- Main Content -->
    <main class="main-content">

        <header class="topbar">

            <div>
                <p class="topbar-kicker">TRANSACTION HISTORY</p>
                <h1>All Expenses</h1>
            </div>

            <div class="avatar">
                <?php echo clean(strtoupper(substr($user_name, 0, 1))); ?>
            </div>

        </header>

        <section class="page-intro">

            <span class="welcome-label">YOUR RECORDS</span>

            <h2>Expense History</h2>

            <p>
                View and manage your recorded transactions.
            </p>

        </section>

        <!-- Summary Cards -->
        <section class="summary-grid">

            <article class="summary-card">

                <div class="summary-top">
                    Current Month Expenses
                </div>

                <h3>
                    ₹<?php echo money($summary['monthly_expenses']); ?>
                </h3>

                <small>Expense transactions this month</small>

            </article>

            <article class="summary-card">

                <div class="summary-top">
                    Total Records
                </div>

                <h3>
                    <?php echo (int)$summary['total_records']; ?>
                </h3>

                <small>All recorded transactions</small>

            </article>

            <article class="summary-card">

                <div class="summary-top">
                    Average Expense
                </div>

                <h3>
                    ₹<?php echo money($summary['average_expense']); ?>
                </h3>

                <small>Average per expense transaction</small>

            </article>

            <article class="summary-card">

                <div class="summary-top">
                    Highest Expense
                </div>

                <h3>
                    ₹<?php echo money($summary['highest_expense']); ?>
                </h3>

                <small>Highest single expense</small>

            </article>

        </section>

        <!-- Search and Filter -->
        <section class="panel">

            <div class="panel-heading">

                <div>
                    <h3>All Transactions</h3>
                    <p>Complete list of your transactions</p>
                </div>

                <a href="add-expense.php" class="primary-btn">
                    + Add New
                </a>

            </div>

            <form action="expenses.php" method="GET">

                <div class="filter-bar">

                    <div class="search-box">

                        <input
                            type="search"
                            name="search"
                            placeholder="Search transactions..."
                            aria-label="Search transactions"
                            value="<?php echo clean($search); ?>"
                        >

                    </div>

                    <select
                        class="filter-select"
                        name="category"
                        aria-label="Filter category"
                    >

                        <option value="">All Categories</option>

                        <?php foreach ($categories as $cat): ?>

                            <option
                                value="<?php echo clean($cat); ?>"
                                <?php echo ($category === $cat) ? 'selected' : ''; ?>
                            >
                                <?php echo clean($cat); ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                    <input
                        type="month"
                        class="filter-select"
                        name="month"
                        value="<?php echo clean($month); ?>"
                        aria-label="Filter month"
                    >

                    <button type="submit" class="primary-btn">
                        Apply Filters
                    </button>

                    <a href="expenses.php" class="outline-btn">
                        Reset
                    </a>

                </div>

            </form>

            <!-- Transaction Table -->
            <div class="table-wrap">

                <table class="data-table">

                    <thead>

                        <tr>
                            <th>Transaction Name</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Payment</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Actions</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php if ($transactions->num_rows > 0): ?>

                            <?php while ($row = $transactions->fetch_assoc()): ?>

                                <tr>

                                    <td>
                                        <strong>
                                            <?php echo clean($row['title']); ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <?php echo clean($row['category']); ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo date(
                                            'd M Y',
                                            strtotime($row['expense_date'])
                                        );
                                        ?>
                                    </td>

                                    <td>
                                        <?php echo clean($row['payment_method']); ?>
                                    </td>

                                    <td>
                                        <?php echo clean($row['type']); ?>
                                    </td>

                                    <td class="amount-cell">

                                        <?php if ($row['type'] === 'Income'): ?>
                                            + ₹<?php echo money($row['amount']); ?>
                                        <?php else: ?>
                                            - ₹<?php echo money($row['amount']); ?>
                                        <?php endif; ?>

                                    </td>

                                    <td class="action-links">

                                        <a href="edit_expense.php?id=<?php echo (int)$row['id']; ?>">
                                            Edit
                                        </a>

                                        <form
                                            action="delete_expense.php"
                                            method="POST"
                                            style="display:inline;"
                                            onsubmit="return confirm('Are you sure you want to delete this transaction?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?php echo (int)$row['id']; ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="delete-link"
                                                style="background:none;border:none;cursor:pointer;font:inherit;"
                                            >
                                                Delete
                                            </button>

                                        </form>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="7" style="text-align:center;padding:25px;">
                                    No transactions found.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

        <footer class="page-footer">
            © 2026 HISABOO | Personal Expense Tracker
        </footer>

    </main>

</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>

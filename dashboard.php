
<?php
date_default_timezone_set('Asia/Kolkata');
session_start();
require_once "db.php";

// Check login session
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'User';

// Current month dates
$monthStart = date('Y-m-01');
$today = date('Y-m-d');

// 1. Calculate total income and expenses for all time
$stmt = $conn->prepare("
    SELECT
        COALESCE(SUM(CASE WHEN type = 'Income' THEN amount ELSE 0 END), 0) AS total_income,
        COALESCE(SUM(CASE WHEN type = 'Expense' THEN amount ELSE 0 END), 0) AS total_expenses
    FROM expenses
    WHERE user_id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$totals = $stmt->get_result()->fetch_assoc();
$stmt->close();

$totalBalance = (float)$totals['total_income'] - (float)$totals['total_expenses'];

// 2. Calculate current month's income and expenses
$stmt = $conn->prepare("
    SELECT
        COALESCE(SUM(CASE WHEN type = 'Income' THEN amount ELSE 0 END), 0) AS monthly_income,
        COALESCE(SUM(CASE WHEN type = 'Expense' THEN amount ELSE 0 END), 0) AS monthly_expenses
    FROM expenses
    WHERE user_id = ?
    AND expense_date BETWEEN ? AND ?
");

$stmt->bind_param("iss", $user_id, $monthStart, $today);
$stmt->execute();
$monthly = $stmt->get_result()->fetch_assoc();
$stmt->close();

$monthlyIncome = (float)$monthly['monthly_income'];
$monthlyExpenses = (float)$monthly['monthly_expenses'];
$monthlySavings = $monthlyIncome - $monthlyExpenses;

// 3. Weekly expense overview
$weeklyExpenses = [];

$startDate = date('Y-m-d', strtotime('-6 days'));

for ($i = 0; $i < 7; $i++) {
    $date = date('Y-m-d', strtotime($startDate . " +$i days"));

    $weeklyExpenses[$date] = [
        'day' => date('D', strtotime($date)),
        'amount' => 0
    ];
}

$stmt = $conn->prepare("
    SELECT expense_date, SUM(amount) AS daily_total
    FROM expenses
    WHERE user_id = ?
    AND type = 'Expense'
    AND expense_date BETWEEN ? AND ?
    GROUP BY expense_date
");

$stmt->bind_param("iss", $user_id, $startDate, $today);
$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $date = $row['expense_date'];

    if (isset($weeklyExpenses[$date])) {
        $weeklyExpenses[$date]['amount'] = (float)$row['daily_total'];
    }
}

$stmt->close();

$maxWeeklyExpense = 0;

foreach ($weeklyExpenses as $day) {
    $maxWeeklyExpense = max($maxWeeklyExpense, $day['amount']);
}

// 4. Category-wise expense summary for current month
$stmt = $conn->prepare("
    SELECT category, SUM(amount) AS category_total
    FROM expenses
    WHERE user_id = ?
    AND type = 'Expense'
    AND expense_date BETWEEN ? AND ?
    GROUP BY category
    ORDER BY category_total DESC
    LIMIT 2
");

$stmt->bind_param("iss", $user_id, $monthStart, $today);
$stmt->execute();

$categoryResult = $stmt->get_result();
$topCategories = [];

while ($row = $categoryResult->fetch_assoc()) {
    $topCategories[] = $row;
}

$stmt->close();

// 5. Fetch latest 5 transactions
$stmt = $conn->prepare("
    SELECT title, category, amount, expense_date, type
    FROM expenses
    WHERE user_id = ?
    ORDER BY expense_date DESC, id DESC
    LIMIT 5
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$recentTransactions = $stmt->get_result();

$stmt->close();

// Helper function for safe output
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
    <title>HISABOO | Dashboard</title>
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

    <!-- SIDEBAR -->
    <aside class="sidebar">

        <a href="dashboard.php" class="brand-logo">
            <span class="logo-icon">H</span>
            HISABOO<span class="logo-dot">.</span>
        </a>

        <p class="sidebar-label">MAIN MENU</p>

        <nav class="side-nav">

            <a href="dashboard.php" class="side-link active">
                ◈ Dashboard
            </a>

            <a href="add-expense.php" class="side-link">
                ＋ Add Expense
            </a>

            <a href="expenses.php" class="side-link">
                ▤ All Expenses
            </a>

            <a href="#yourself" class="side-link">
                💙 Yourself
            </a>

        </nav>

        <p class="sidebar-label">ACCOUNT</p>

        <nav class="side-nav">
            <a href="logout.php" class="side-link">
                ↪ Logout
            </a>
        </nav>

    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">

        <header class="topbar">
            <div>
                <p class="topbar-kicker">OVERVIEW</p>
                <h1>Dashboard</h1>
            </div>

            <div class="avatar">
                <?php echo clean(strtoupper(substr($user_name, 0, 1))); ?>
            </div>
        </header>

        <!-- WELCOME BANNER -->
        <section class="welcome-banner">

            <div>
                <span class="welcome-label">WELCOME BACK</span>

                <h2>
                    Hello, <?php echo clean($user_name); ?>! 👋
                </h2>

                <p>Here's your financial overview for today.</p>
            </div>

            <a href="add-expense.php" class="primary-btn">
                + Add Expense
            </a>

        </section>

        <!-- SUMMARY CARDS -->
        <section class="summary-grid">

            <article class="summary-card">
                <div class="summary-top">
                    Total Balance
                    <span class="summary-icon">◈</span>
                </div>

                <h3>₹<?php echo money($totalBalance); ?></h3>
                <small>Available balance (all time)</small>
            </article>

            <article class="summary-card">
                <div class="summary-top">
                    Total Income
                    <span class="summary-icon">↗</span>
                </div>

                <h3>₹<?php echo money($monthlyIncome); ?></h3>
                <small>Current month income</small>
            </article>

            <article class="summary-card">
                <div class="summary-top">
                    Total Expenses
                    <span class="summary-icon">↘</span>
                </div>

                <h3>₹<?php echo money($monthlyExpenses); ?></h3>
                <small>Current month expenses</small>
            </article>

            <article class="summary-card">
                <div class="summary-top">
                    Savings
                    <span class="summary-icon">✧</span>
                </div>

                <h3>₹<?php echo money($monthlySavings); ?></h3>
                <small>Current month remaining amount</small>
            </article>

        </section>

        <!-- CHART AND QUICK SUMMARY -->
        <section class="dashboard-grid">

            <article class="panel">

                <div class="panel-heading">
                    <div>
                        <h3>Expense Overview</h3>
                        <p>Your last 7 days spending summary</p>
                    </div>
                </div>

                <div class="chart-area">

                    <?php foreach ($weeklyExpenses as $day): ?>

                        <?php
                        $height = 0;

                        if ($maxWeeklyExpense > 0) {
                            $height = ($day['amount'] / $maxWeeklyExpense) * 100;
                        }
                        ?>

                        <div class="chart-column">

                            <i
                                style="height: <?php echo max(3, $height); ?>%;"
                                title="₹<?php echo money($day['amount']); ?>">
                            </i>

                            <small>
                                <?php echo clean($day['day']); ?>
                            </small>

                        </div>

                    <?php endforeach; ?>

                </div>

            </article>

            <article class="panel">

                <div class="panel-heading">
                    <div>
                        <h3>Quick Summary</h3>
                        <p>Your top spending categories this month</p>
                    </div>
                </div>

                <?php if (count($topCategories) > 0): ?>

                    <?php foreach ($topCategories as $category): ?>

                        <div class="summary-card">
                            <div class="summary-top">
                                <?php echo clean($category['category']); ?>
                            </div>

                            <h3>
                                ₹<?php echo money($category['category_total']); ?>
                            </h3>
                        </div>

                        <br>

                    <?php endforeach; ?>

                <?php else: ?>

                    <p>No expense records found for this month.</p>

                <?php endif; ?>

            </article>

        </section>

        <!-- RECENT TRANSACTIONS -->
        <section class="panel">

            <div class="panel-heading">
                <div>
                    <h3>Recent Transactions</h3>
                    <p>Your latest expense and income records</p>
                </div>

                <a href="expenses.php" class="text-link">
                    View All →
                </a>
            </div>

            <div class="table-wrap">

                <table class="data-table">

                    <thead>
                        <tr>
                            <th>Transaction</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Amount</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if ($recentTransactions->num_rows > 0): ?>

                            <?php while ($transaction = $recentTransactions->fetch_assoc()): ?>

                                <tr>

                                    <td>
                                        <strong>
                                            <?php echo clean($transaction['title']); ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <?php echo clean($transaction['category']); ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo date(
                                            'd M Y',
                                            strtotime($transaction['expense_date'])
                                        );
                                        ?>
                                    </td>

                                    <td>
                                        <?php echo clean($transaction['type']); ?>
                                    </td>

                                    <td class="amount-cell">
                                        <?php if ($transaction['type'] === 'Income'): ?>
                                            + ₹<?php echo money($transaction['amount']); ?>
                                        <?php else: ?>
                                            - ₹<?php echo money($transaction['amount']); ?>
                                        <?php endif; ?>
                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="5">
                                    No transactions found. Add your first transaction!
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

        <!-- YOURSELF NOTES SECTION -->
        <section class="personal-notes" id="yourself">

            <div class="notes-heading">
                <span class="notes-sticker">💌</span>

                <div>
                    <h2>A Note for You</h2>
                    <p>A little reminder for your financial journey ✨</p>
                </div>

                <span class="notes-sparkle">✦</span>
            </div>

            <div class="notes-grid">

                <div class="note-card note-blue">
                    <span class="note-icon">🫶</span>
                    <h3>Take It Easy</h3>

                    <p>
                        Don't take too much stress. We'll manage
                        everything one step at a time. You don't
                        have to figure it all out today.
                    </p>
                </div>

                <div class="note-card note-sky">
                    <span class="note-icon">🌱</span>
                    <h3>Every Expense Has a Story</h3>

                    <p>
                        Your expenses are not just numbers. They
                        are little chapters in your journey towards
                        becoming financially mature.
                    </p>
                </div>

                <div class="note-card note-navy">
                    <span class="note-icon">💙</span>
                    <h3>Money Is Not Everything</h3>

                    <p>
                        Money is a tool, not your worth. Spend wisely,
                        save patiently and never forget that your
                        peace matters too.
                    </p>
                </div>

            </div>

            <div class="my-note-box">

                <div class="my-note-title">
                    <span>📝</span>
                    <h3>A Little Note to Myself</h3>
                </div>

                <textarea
                    class="personal-note-input"
                    placeholder="Write something positive for yourself... 💙"
                ></textarea>

                <p>✨ Your journey, your pace, your story.</p>

            </div>

        </section>

        <footer class="page-footer">
            © 2026 HISABOO | Personal Expense Tracker
        </footer>

    </main>

</div>

</body>
</html>

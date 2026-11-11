<?php
session_start();
require_once "db.php";

$error = "";

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    echo "SIGNUP FORM SUBMITTED SUCCESSFULLY";

    // Receive form data
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $terms = isset($_POST['terms']);

    // Validate required fields
    if ($name === "" || $email === "" || $password === "" || $confirm_password === "") {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 8) {
        $error = "Password must contain at least 8 characters.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (!$terms) {
        $error = "Please accept the Terms and Conditions.";
    } else {

        // Check whether email already exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();

        $existingUser = $check->get_result();

        if ($existingUser->num_rows > 0) {

            $error = "This email address is already registered.";

        } else {

            // Securely hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $stmt = $conn->prepare(
                "INSERT INTO users (name, email, password) VALUES (?, ?, ?)"
            );

            $stmt->bind_param("sss", $name, $email, $hashedPassword);

            if ($stmt->execute()) {
                header("Location: index.php?registered=1");
                exit;
            } else {
                $error = "Registration failed. Please try again.";
            }

            $stmt->close();
        }

        $check->close();
    }
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hisabo | Create Account</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <input type="checkbox" id="theme-toggle">

<label for="theme-toggle" class="theme-switch">
    <span class="sun-icon">☀️</span>
    <span class="moon-icon">🌙</span>
    <span>Switch Theme</span>
</label>
<main class="auth-layout">
    <section class="brand-section">
        <a href="index.html" class="brand-logo">
            <span class="logo-icon">H</span>
            hisabo<span class="logo-dot">.</span>
        </a>
        <h1>
            Start your journey.
            <span>Manage money smarter.</span>
        </h1>
        <p class="brand-description">
            Create your account and take the first step
            towards better financial management.
        </p>
        <div class="visual-area">
            <div class="finance-card">
                <p>SMART MONEY MANAGEMENT</p>
                <p class="balance-label">Your financial future</p>
                <h2>Starts Here.</h2>
                <div class="finance-card-footer">
                    Track • Manage • Save
                </div>
            </div>
        </div>
    </section>
    <section class="form-section">
        <div class="form-wrapper">
            <div class="form-heading">
                <span class="welcome-label">GET STARTED</span>
                <h2>Create Account</h2>
                <p>Fill in your details to join Hisabo.</p>
            </div>
            <form class="app-form" action="signup.php" method="POST">
                <?php if ($error !== ""): ?>
                    <div class="error-message">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                <div class="input-group">
                    <label for="fullname">Full Name</label>
                    <div class="input-wrapper">
                        <input
                            type="text"
                            id="fullname"
                            name="name"
                            placeholder="Enter your full name"
                            required  >
                    </div>
                </div>
                <div class="input-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrapper">
                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="Enter your email"
                            required  >
                    </div>
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Create a password"
                            required  >
                    </div>
                </div>
                <div class="input-group">
                    <label for="confirm-password">Confirm Password</label>
                    <div class="input-wrapper">
                        <input
                            type="password"
                            id="confirm-password"
                            name="confirm_password"
                            placeholder="Confirm your password"
                            required  >
                    </div>
                </div>
                <label class="remember-option">
                    <input type="checkbox" name="terms" value="1" required>
                    I agree to the Terms and Conditions
                </label>
                <button type="submit" class="btn-primary">
                    Create Account →
                </button>
            </form>
            <p class="signup-text">
                Already have an account?
                <a href="index.html">Sign In</a>
            </p>
        </div>
        <footer class="form-footer">
            © 2026 Hisabo. All rights reserved.
        </footer>
    </section>
</main>
</body>
</html>
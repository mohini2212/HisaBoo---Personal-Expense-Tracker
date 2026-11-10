-- Personal Expense Tracker database
-- Import using phpMyAdmin -> Import tab
CREATE DATABASE IF NOT EXISTS expense_tracker;
USE expense_tracker;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    category VARCHAR(50) NOT NULL,
    expense_date DATE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Sample data (test user: test@gmail.com / password: password)
INSERT INTO users (name, email, password) VALUES
('Test User', 'test@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO expenses (user_id, title, amount, category, expense_date, description) VALUES
(1, 'Lunch', 150.00, 'Food', '2025-01-10', 'College canteen'),
(1, 'Bus Pass', 600.00, 'Travel', '2025-01-12', 'Monthly pass'),
(1, 'Notebook', 85.50, 'Education', '2025-01-14', NULL),
(1, 'Movie', 300.00, 'Entertainment', '2025-01-15', 'Weekend');

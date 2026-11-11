
# HISABOO - Personal Expense Tracker

HISABOO is a web-based Personal Expense Tracker designed to help users manage their daily income and expenses, monitor their financial balance, and maintain transaction records through a simple and user-friendly dashboard.

## Features

### 1. User Authentication
- User Registration (Sign Up)
- Secure Login and Logout
- Password Hashing
- Session-Based Authentication
- Duplicate Email Validation

### 2. Dashboard
- Total Income
- Total Expenses
- Available Balance
- Monthly Savings
- Weekly Expense Overview
- Top Spending Categories
- Recent Transactions

### 3. Transaction Management
- Add Income Transactions
- Add Expense Transactions
- Edit Existing Transactions
- Delete Transactions
- Category Selection
- Payment Method Selection
- Transaction Date and Description

### 4. Expense Management
- View All Transactions
- Search Transactions
- Filter by Category
- Filter by Month
- Income and Expense Identification

### 5. Security
- PHP Session Management
- Password Hashing
- Prepared SQL Statements
- User-Specific Transaction Access
- Input Validation

## Technologies Used

- HTML5
- CSS3
- JavaScript
- PHP
- MySQL
- XAMPP
- Visual Studio Code

## Database Structure

Database Name: `expense_tracker`

### Users Table
Stores registered user information.

- id
- name
- email
- password
- created_at

### Expenses Table
Stores income and expense transactions.

- id
- user_id
- title
- amount
- category
- expense_date
- description
- payment_method
- type
- created_at

## Installation and Setup

1. Install XAMPP on your computer.
2. Start Apache and MySQL from XAMPP Control Panel.
3. Copy the HISABOO project folder into:

   `C:\xampp\htdocs\`

4. Open phpMyAdmin:

   `http://localhost/phpmyadmin/`

5. Create or import the `expense_tracker` database using the provided SQL file.
6. Make sure the database connection details in `db.php` are correct.
7. Open the project in your browser:

   `http://localhost/HISABOO/`

8. Register a new account and log in to start managing transactions.

## Project Folder Structure

HISABOO/
- index.php
- signup.php
- dashboard.php
- add-expense.php
- expenses.php
- edit_expense.php
- delete_expense.php
- logout.php
- db.php
- style.css
- dashboard.js
- expense.js
- main.js
- validation.js
- expense_tracker.sql
- README.md

## Testing

The following functionalities have been tested:

- User Registration and Login
- Duplicate Email Validation
- Dashboard Access Protection
- Add Income and Expense
- Edit and Delete Transactions
- Transaction Search
- Category and Month Filters
- Dashboard Financial Calculations
- Logout and Session Protection

## Project Objective

The main objective of HISABOO is to provide users with a simple and efficient platform to record daily financial transactions, understand spending habits, and monitor their income and savings.

---

**Project Name:** HISABOO - Personal Expense Tracker

**Year:** 2026

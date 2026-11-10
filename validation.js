/* ==========================================
   HISABOO - FORM VALIDATION
========================================== */

document.addEventListener("DOMContentLoaded", function () {

    // ======================================
    // 1. EMAIL VALIDATION FUNCTION
    // ======================================

    function validateEmail(emailValue) {

        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        return emailPattern.test(emailValue);

    }


    // ======================================
    // 2. DISPLAY ERROR MESSAGE
    // ======================================

    function showError(input, message) {

        const error = document.createElement("small");

        error.className = "error-message";
        error.textContent = message;

        error.style.color = "#d93025";
        error.style.fontSize = "12px";
        error.style.display = "block";
        error.style.marginTop = "5px";

        input.parentElement.appendChild(error);

        input.style.borderColor = "#d93025";

    }


    // ======================================
    // 3. CLEAR PREVIOUS ERROR MESSAGES
    // ======================================

    function clearErrors(form) {

        form.querySelectorAll(".error-message").forEach(function (error) {
            error.remove();
        });

        form.querySelectorAll("input, select, textarea").forEach(function (input) {
            input.style.borderColor = "";
        });

    }


    // ======================================
    // 4. LOGIN FORM VALIDATION
    // ======================================

    const loginForm = document.querySelector(".login-form");

    if (loginForm) {

        const email = document.getElementById("email");
        const password = document.getElementById("password");

        loginForm.addEventListener("submit", function (event) {

            clearErrors(loginForm);

            let isValid = true;

            // Email validation

            if (email.value.trim() === "") {

                showError(email, "Email address is required.");
                isValid = false;

            } else if (!validateEmail(email.value.trim())) {

                showError(email, "Please enter a valid email address.");
                isValid = false;

            }

            // Password validation

            if (password.value.trim() === "") {

                showError(password, "Password is required.");
                isValid = false;

            } else if (password.value.length < 8) {

                showError(password, "Password must contain at least 8 characters.");
                isValid = false;

            }

            if (!isValid) {
                event.preventDefault();
            }

        });

    }


    // ======================================
    // 5. ADD EXPENSE FORM VALIDATION
    // ======================================

    const expenseForm = document.querySelector(".app-form");

    if (expenseForm) {

        const expenseTitle = document.getElementById("expense-title");
        const amount = document.getElementById("amount");
        const category = document.getElementById("category");
        const date = document.getElementById("date");
        const payment = document.getElementById("payment");
        const type = document.getElementById("type");

        expenseForm.addEventListener("submit", function (event) {

            clearErrors(expenseForm);

            let isValid = true;

            // Expense title

            if (expenseTitle.value.trim() === "") {

                showError(expenseTitle, "Expense title is required.");
                isValid = false;

            }

            // Amount

            if (
                amount.value.trim() === "" ||
                !Number.isFinite(Number(amount.value)) ||
                Number(amount.value) <= 0
            ) {

                showError(amount, "Please enter a valid amount greater than zero.");
                isValid = false;

            }

            // Category

            if (category.value === "") {

                showError(category, "Please select a category.");
                isValid = false;

            }

            // Expense date

            if (date.value === "") {

                showError(date, "Please select an expense date.");
                isValid = false;

            }

            // Payment method

            if (payment.value === "") {

                showError(payment, "Please select a payment method.");
                isValid = false;

            }

            // Transaction type

            if (type.value === "") {

                showError(type, "Please select a transaction type.");
                isValid = false;

            }

            // Stop submission if validation fails

            if (!isValid) {
                event.preventDefault();
            }

        });

    }

});
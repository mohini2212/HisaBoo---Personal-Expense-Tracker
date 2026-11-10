/* ==========================================
   HISABOO - EXPENSE MANAGEMENT
========================================== */

document.addEventListener("DOMContentLoaded", function () {

    // ======================================
    // 1. SELECT HTML ELEMENTS
    // ======================================

    const expenseForm = document.querySelector(".app-form");

    const expenseTitle = document.getElementById("expense-title");
    const expenseAmount = document.getElementById("amount");
    const expenseCategory = document.getElementById("category");
    const expenseDate = document.getElementById("date");
    const expensePayment = document.getElementById("payment");
    const expenseType = document.getElementById("type");
    const expenseDescription = document.getElementById("description");

    const expenseTable = document.querySelector(".data-table tbody");

    const searchInput = document.querySelector('input[type="search"]');

    const filterSelects = document.querySelectorAll(".filter-select");


    // ======================================
    // 2. INITIALIZE EXPENSE STORAGE
    // ======================================

    let expenses = [];

    try {

        expenses = JSON.parse(
            localStorage.getItem("hisabooExpenses")
        ) || [];

    } catch (error) {

        console.error("Unable to retrieve expenses:", error);

        expenses = [];

    }


    // ======================================
    // 3. SAVE EXPENSES
    // ======================================

    function saveExpenses() {

        localStorage.setItem(
            "hisabooExpenses",
            JSON.stringify(expenses)
        );

    }


    // ======================================
    // 4. FORMAT DATE
    // ======================================

    function formatDate(dateValue) {

        if (!dateValue) return "";

        const date = new Date(dateValue + "T00:00:00");

        return date.toLocaleDateString("en-IN", {
            day: "2-digit",
            month: "short",
            year: "numeric"
        });

    }


    // ======================================
    // 5. DISPLAY EXPENSE RECORDS
    // ======================================

    function displayExpenses() {

        if (!expenseTable) return;

        expenseTable.innerHTML = "";

        if (expenses.length === 0) {

            const row = document.createElement("tr");
            const cell = document.createElement("td");

            cell.colSpan = 6;
            cell.textContent = "No saved transactions found.";
            cell.style.textAlign = "center";

            row.appendChild(cell);
            expenseTable.appendChild(row);

            return;

        }

        expenses.forEach(function (expense) {

            const row = document.createElement("tr");

            row.dataset.category = expense.category;
            row.dataset.date = expense.date;

            // Expense title

            const titleCell = document.createElement("td");
            titleCell.textContent = expense.title;

            // Category

            const categoryCell = document.createElement("td");

            const categoryBadge = document.createElement("span");

            categoryBadge.className = "category-badge";
            categoryBadge.textContent = expense.category;

            categoryCell.appendChild(categoryBadge);

            // Date

            const dateCell = document.createElement("td");
            dateCell.textContent = formatDate(expense.date);

            // Payment method

            const paymentCell = document.createElement("td");
            paymentCell.textContent = expense.payment || "-";

            // Amount

            const amountCell = document.createElement("td");

            amountCell.className = "amount-cell";

            amountCell.textContent =
                (expense.type === "Income" ? "+" : "-") +
                "₹" + Number(expense.amount).toFixed(2);

            // Actions

            const actionCell = document.createElement("td");

            const editLink = document.createElement("a");

            editLink.href = "add-expense.html?edit=" +
                encodeURIComponent(expense.id);

            editLink.textContent = "Edit";

            const deleteLink = document.createElement("a");

            deleteLink.href = "#";
            deleteLink.className = "delete-link";
            deleteLink.textContent = "Delete";
            deleteLink.dataset.action = "delete";
            deleteLink.dataset.id = expense.id;

            actionCell.appendChild(editLink);
            actionCell.appendChild(document.createTextNode(" | "));
            actionCell.appendChild(deleteLink);

            row.appendChild(titleCell);
            row.appendChild(categoryCell);
            row.appendChild(dateCell);
            row.appendChild(paymentCell);
            row.appendChild(amountCell);
            row.appendChild(actionCell);

            expenseTable.appendChild(row);

        });

        filterExpenses();

    }


    // ======================================
    // 6. ADD OR UPDATE EXPENSE
    // ======================================

    if (expenseForm) {

        const urlParams = new URLSearchParams(window.location.search);

        const editingId = urlParams.get("edit");

        let editingExpense = null;

        if (editingId) {

            editingExpense = expenses.find(function (expense) {
                return expense.id === editingId;
            });

            if (editingExpense) {

                expenseTitle.value = editingExpense.title;
                expenseAmount.value = editingExpense.amount;
                expenseCategory.value = editingExpense.category;
                expenseDate.value = editingExpense.date;
                expensePayment.value = editingExpense.payment;
                expenseType.value = editingExpense.type;
                expenseDescription.value = editingExpense.description || "";

            }

        }

        expenseForm.addEventListener("submit", function (event) {

            event.preventDefault();

            const title = expenseTitle.value.trim();
            const amount = Number(expenseAmount.value);
            const category = expenseCategory.value;
            const date = expenseDate.value;
            const payment = expensePayment.value;
            const type = expenseType.value;
            const description = expenseDescription.value.trim();

            // Basic validation

            if (
                !title ||
                !Number.isFinite(amount) ||
                amount <= 0 ||
                !category ||
                !date ||
                !payment ||
                !type
            ) {

                alert("Please enter all required expense details.");

                return;

            }

            // Update existing transaction

            if (editingExpense) {

                editingExpense.title = title;
                editingExpense.amount = amount;
                editingExpense.category = category;
                editingExpense.date = date;
                editingExpense.payment = payment;
                editingExpense.type = type;
                editingExpense.description = description;

            }

            // Add new transaction

            else {

                const newExpense = {

                    id: Date.now().toString() +
                        Math.random().toString(36).slice(2),

                    title: title,
                    amount: amount,
                    category: category,
                    date: date,
                    payment: payment,
                    type: type,
                    description: description

                };

                expenses.push(newExpense);

            }

            saveExpenses();

            alert(
                editingExpense
                    ? "Transaction updated successfully!"
                    : "Transaction added successfully!"
            );

            window.location.href = "expenses.html";

        });

    }


    // ======================================
    // 7. DELETE EXPENSE
    // ======================================

    if (expenseTable) {

        expenseTable.addEventListener("click", function (event) {

            const deleteLink = event.target.closest(
                'a[data-action="delete"]'
            );

            if (!deleteLink) return;

            event.preventDefault();

            const expenseId = deleteLink.dataset.id;

            const confirmation = confirm(
                "Are you sure you want to delete this transaction?"
            );

            if (confirmation) {

                expenses = expenses.filter(function (expense) {
                    return expense.id !== expenseId;
                });

                saveExpenses();

                displayExpenses();

            }

        });

    }


    // ======================================
    // 8. SEARCH AND FILTER EXPENSES
    // ======================================

    function filterExpenses() {

        if (!expenseTable) return;

        const searchValue = searchInput
            ? searchInput.value.toLowerCase().trim()
            : "";

        const selectedCategory = filterSelects[0]
            ? filterSelects[0].value
            : "";

        const selectedMonth = filterSelects[1]
            ? filterSelects[1].value
            : "";

        const rows = expenseTable.querySelectorAll("tr");

        rows.forEach(function (row) {

            const rowText = row.textContent.toLowerCase();

            const category = row.cells[1]
                ? row.cells[1].textContent.trim()
                : "";

            const dateText = row.cells[2]
                ? row.cells[2].textContent.trim()
                : "";

            let month = "";

            if (dateText) {

                const parsedDate = new Date(dateText);

                if (!isNaN(parsedDate.getTime())) {

                    month = parsedDate.toLocaleString("en-US", {
                        month: "long"
                    });

                }

            }

            const matchesSearch = rowText.includes(searchValue);

            const matchesCategory =
                !selectedCategory ||
                selectedCategory === "All Categories" ||
                category === selectedCategory;

            const matchesMonth =
                !selectedMonth ||
                selectedMonth === "All Months" ||
                month === selectedMonth;

            row.style.display =
                matchesSearch && matchesCategory && matchesMonth
                    ? ""
                    : "none";

        });

    }


    if (searchInput) {

        searchInput.addEventListener("input", filterExpenses);

    }

    filterSelects.forEach(function (select) {

        select.addEventListener("change", filterExpenses);

    });


    // ======================================
    // 9. INITIALIZE EXPENSE TABLE
    // ======================================

    if (expenseTable && expenses.length > 0) {

        displayExpenses();

    }

});
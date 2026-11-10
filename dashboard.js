/* ==========================================
   HISABOO - DASHBOARD MANAGEMENT
========================================== */

document.addEventListener("DOMContentLoaded", function () {

    // ======================================
    // 1. GET SAVED TRANSACTIONS
    // ======================================

    function getExpenses() {

        try {

            return JSON.parse(
                localStorage.getItem("hisabooExpenses")
            ) || [];

        } catch (error) {

            console.error("Unable to retrieve transactions:", error);

            return [];

        }

    }


    // ======================================
    // 2. CALCULATE TOTALS
    // ======================================

    function calculateTotal(transactions, type) {

        return transactions
            .filter(function (item) {
                return item.type === type;
            })
            .reduce(function (total, item) {

                return total + Number(item.amount);

            }, 0);

    }


    // ======================================
    // 3. FORMAT CURRENCY
    // ======================================

    function formatCurrency(amount) {

        return "₹" + Number(amount).toLocaleString("en-IN", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

    }


    // ======================================
    // 4. UPDATE SUMMARY CARDS
    // ======================================

    function updateSummaryCards(transactions) {

        const income = calculateTotal(transactions, "Income");

        const expenses = calculateTotal(transactions, "Expense");

        const balance = income - expenses;

        const savings = balance;

        const summaryCards = document.querySelectorAll(
            ".summary-grid > .summary-card"
        );

        const values = [
            balance,
            income,
            expenses,
            savings
        ];

        summaryCards.forEach(function (card, index) {

            if (index >= values.length) return;

            const valueElement = Array.from(
                card.querySelectorAll("*")
            ).find(function (element) {

                return (
                    element.children.length === 0 &&
                    element.textContent.includes("₹")
                );

            });

            if (valueElement) {

                valueElement.textContent =
                    formatCurrency(values[index]);

            }

        });

    }


    // ======================================
    // 5. UPDATE CATEGORY SUMMARY
    // ======================================

    function updateCategorySummary(transactions) {

        const categoryTotals = {};

        transactions
            .filter(function (item) {
                return item.type === "Expense";
            })
            .forEach(function (item) {

                const category = item.category;

                categoryTotals[category] =
                    (categoryTotals[category] || 0) +
                    Number(item.amount);

            });

        const dashboardGrid = document.querySelector(".dashboard-grid");

        if (!dashboardGrid) return;

        const panels = dashboardGrid.querySelectorAll(".panel");

        const quickSummary = Array.from(panels).find(function (panel) {

            return panel.textContent.includes("Quick Summary");

        });

        if (!quickSummary) return;

        const categoryCards = quickSummary.querySelectorAll(".summary-card");

        categoryCards.forEach(function (card) {

            const categoryName = card.textContent.trim();

            const category = Object.keys(categoryTotals).find(
                function (name) {
                    return categoryName.includes(name);
                }
            );

            if (!category) return;

            const amountElement = Array.from(
                card.querySelectorAll("*")
            ).find(function (element) {

                return (
                    element.children.length === 0 &&
                    element.textContent.includes("₹")
                );

            });

            if (amountElement) {

                amountElement.textContent =
                    formatCurrency(categoryTotals[category]);

            }

        });

    }


    // ======================================
    // 6. UPDATE RECENT TRANSACTIONS
    // ======================================

    function updateRecentTransactions(transactions) {

        const dashboardGrid = document.querySelector(".dashboard-grid");

        if (!dashboardGrid) return;

        const panels = dashboardGrid.querySelectorAll(".panel");

        const recentPanel = Array.from(panels).find(function (panel) {

            return panel.textContent.includes("Recent Transactions");

        });

        if (!recentPanel) return;

        const tableBody = recentPanel.querySelector(".data-table tbody");

        if (!tableBody) return;

        tableBody.innerHTML = "";

        const recentTransactions = [...transactions]
            .sort(function (a, b) {

                return new Date(b.date) - new Date(a.date);

            })
            .slice(0, 5);

        if (recentTransactions.length === 0) {

            const row = document.createElement("tr");

            const cell = document.createElement("td");

            cell.colSpan = 4;
            cell.textContent = "No transactions recorded yet.";
            cell.style.textAlign = "center";

            row.appendChild(cell);
            tableBody.appendChild(row);

            return;

        }

        recentTransactions.forEach(function (item) {

            const row = document.createElement("tr");

            const values = [
                item.title,
                item.category,
                item.date,
                (item.type === "Income" ? "+" : "-") +
                    formatCurrency(item.amount)
            ];

            values.forEach(function (value) {

                const cell = document.createElement("td");

                cell.textContent = value;

                row.appendChild(cell);

            });

            tableBody.appendChild(row);

        });

    }


    // ======================================
    // 7. UPDATE EXPENSE OVERVIEW CHART
    // ======================================

    function updateExpenseChart(transactions) {

        const chartColumns = document.querySelectorAll(".chart-column");

        if (!chartColumns.length) return;

        const today = new Date();

        const weeklyTotals = [];

        for (let i = 6; i >= 0; i--) {

            const date = new Date(today);

            date.setDate(today.getDate() - i);

            const total = transactions
                .filter(function (item) {

                    if (item.type !== "Expense") return false;

                    const expenseDate = new Date(
                        item.date + "T00:00:00"
                    );

                    return expenseDate.toDateString() ===
                        date.toDateString();

                })
                .reduce(function (sum, item) {

                    return sum + Number(item.amount);

                }, 0);

            weeklyTotals.push(total);

        }

        const maximum = Math.max(...weeklyTotals, 1);

        chartColumns.forEach(function (column, index) {

            if (index >= weeklyTotals.length) return;

            const bar = column.querySelector("i");

            if (bar) {

                const height = weeklyTotals[index] === 0
                    ? 0
                    : (weeklyTotals[index] / maximum) * 100;

                bar.style.height = height + "%";

            }

        });

    }


    // ======================================
    // 8. UPDATE COMPLETE DASHBOARD
    // ======================================

    function updateDashboard() {

        const transactions = getExpenses();

        updateSummaryCards(transactions);

        updateCategorySummary(transactions);

        updateRecentTransactions(transactions);

        updateExpenseChart(transactions);

    }


    // ======================================
    // 9. LISTEN FOR STORAGE CHANGES
    // ======================================

    window.addEventListener("storage", function (event) {

        if (event.key === "hisabooExpenses") {

            updateDashboard();

        }

    });


    // ======================================
    // 10. INITIALIZE DASHBOARD
    // ======================================

    updateDashboard();

});
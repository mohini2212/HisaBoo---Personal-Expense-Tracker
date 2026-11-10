/* ==========================================
   HISABOO - MAIN JAVASCRIPT
========================================== */

document.addEventListener("DOMContentLoaded", function () {

    // ======================================
    // 1. SIDEBAR NAVIGATION
    // ======================================

    const sidebar = document.querySelector(".sidebar");

    const navLinks = document.querySelectorAll(".side-link");

    // Highlight active navigation link

    navLinks.forEach(function (link) {

        link.addEventListener("click", function () {

            navLinks.forEach(function (navItem) {
                navItem.classList.remove("active");
            });

            link.classList.add("active");

        });

    });


    // ======================================
    // 2. THEME SWITCHING
    // ======================================

    const themeToggle = document.getElementById("theme-toggle");

    if (themeToggle) {

        // Restore previously selected theme

        const savedTheme = localStorage.getItem("hisabooTheme");

        if (savedTheme === "dark") {
            themeToggle.checked = true;
        } else {
            themeToggle.checked = false;
        }

        // Save theme selection

        themeToggle.addEventListener("change", function () {

            if (themeToggle.checked) {

                localStorage.setItem("hisabooTheme", "dark");

            } else {

                localStorage.setItem("hisabooTheme", "light");

            }

        });

    }


    // ======================================
    // 3. AUTOMATIC CURRENT YEAR
    // ======================================

    const currentYear = document.getElementById("current-year");

    if (currentYear) {

        currentYear.textContent = new Date().getFullYear();

    }

});
<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="copyright">
                &copy; <?php echo date('Y'); ?> Indigene Certificate Management System. All rights reserved.
            </div>
            <div class="admin-info">
                <?php if(isset($_SESSION['admin_name'])): ?>
                    Logged in as: <strong><?php echo htmlspecialchars($_SESSION['admin_name']); ?></strong> 
                    (<a href="profile.php">My Account</a> | 
                    <a href="includes/logout.php">Logout</a>)
                <?php endif; ?>
            </div>
        </div>
    </div> 
    
    <!-- JavaScript Dependencies
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>  -->
    
     <script src="../assets/js/admin.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js" defer></script>
    <!-- Alert Auto-Dismiss -->
    <script>
        // Auto-dismiss alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
            
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 500);
                }, 5000);
            });
        });
    </script>
    
    

   <script>
/**
 * Dark Mode Implementation for Indigene Certificate Portal
 * This script handles theme toggling and persistence
 */

document.addEventListener('DOMContentLoaded', function() {
    // Check for saved theme preference or use user's system preference
    const savedTheme = localStorage.getItem('theme');
    const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
    
    // Set initial theme based on saved preference or system preference
    if (savedTheme === 'dark' || (!savedTheme && prefersDarkScheme.matches)) {
        document.documentElement.setAttribute('data-theme', 'dark');
        document.getElementById('theme-toggle').checked = true;
    } else {
        document.documentElement.setAttribute('data-theme', 'light');
        document.getElementById('theme-toggle').checked = false;
    }
    
    // Handle theme toggle click
    document.getElementById('theme-toggle').addEventListener('change', function(e) {
        if (e.target.checked) {
            // Dark mode
            document.documentElement.setAttribute('data-theme', 'dark');
            localStorage.setItem('theme', 'dark');
        } else {
            // Light mode
            document.documentElement.setAttribute('data-theme', 'light');
            localStorage.setItem('theme', 'light');
        }
    });
    
    // Listen for system preference changes
    prefersDarkScheme.addEventListener('change', (e) => {
        // Only auto-switch if user hasn't set a preference
        if (!localStorage.getItem('theme')) {
            if (e.matches) {
                document.documentElement.setAttribute('data-theme', 'dark');
                document.getElementById('theme-toggle').checked = true;
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
                document.getElementById('theme-toggle').checked = false;
            }
        }
    });
});    
</script>


<script>
	// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu for guests
    const navbarMenu = document.querySelector(".navbar .links");
    const hamburgerBtn = document.querySelector(".hamburger-btn");
    const hideMenuBtn = navbarMenu?.querySelector(".close-btn");

    // Dashboard navigation elements
    const dashboardNav = document.querySelector(".dashboard-nav");
    const dashboardToggle = document.querySelector(".dashboard-toggle");

    // Check and apply saved menu states on page load
    function applySavedMenuStates() {
        // For guest navigation menu
        if (navbarMenu && localStorage.getItem('guest-menu-open') === 'true') {
            navbarMenu.classList.add("show-menu");
        }
        
        // For dashboard navigation menu
        if (dashboardNav && localStorage.getItem('dashboard-menu-open') === 'true') {
            dashboardNav.classList.add("show-menu");
        }
    }

    // Apply saved states on page load
    applySavedMenuStates();

    // Guest menu toggle
    if (hamburgerBtn && navbarMenu) {
        hamburgerBtn.addEventListener("click", () => {
            navbarMenu.classList.toggle("show-menu");
            
            // Save state to localStorage
            if (navbarMenu.classList.contains("show-menu")) {
                localStorage.setItem('guest-menu-open', 'true');
            } else {
                localStorage.setItem('guest-menu-open', 'false');
            }
        });
    }

    if (hideMenuBtn) {
        hideMenuBtn.addEventListener("click", () => {
            navbarMenu.classList.remove("show-menu");
            localStorage.setItem('guest-menu-open', 'false');
        });
    }

    // Dashboard navigation mobile toggle
    if (dashboardToggle && dashboardNav) {
        dashboardToggle.addEventListener("click", () => {
            dashboardNav.classList.toggle("show-menu");
            
            // Save state to localStorage
            if (dashboardNav.classList.contains("show-menu")) {
                localStorage.setItem('dashboard-menu-open', 'true');
            } else {
                localStorage.setItem('dashboard-menu-open', 'false');
            }
        });
    }

    // Close mobile menus when clicking outside
    document.addEventListener('click', function(event) {
        // For guest navigation menu
        if (navbarMenu && navbarMenu.classList.contains('show-menu')) {
            const isClickInsideMenu = navbarMenu.contains(event.target);
            const isClickOnHamburger = hamburgerBtn?.contains(event.target);

            if (!isClickInsideMenu && !isClickOnHamburger) {
                navbarMenu.classList.remove('show-menu');
                localStorage.setItem('guest-menu-open', 'false');
            }
        }

        // For dashboard navigation menu
        if (dashboardNav && dashboardNav.classList.contains('show-menu')) {
            const isClickInsideDashMenu = dashboardNav.contains(event.target);
            const isClickOnDashToggle = dashboardToggle?.contains(event.target);

            if (!isClickInsideDashMenu && !isClickOnDashToggle) {
                dashboardNav.classList.remove('show-menu');
                localStorage.setItem('dashboard-menu-open', 'false');
            }
        }
    });

    // Login popup functionality
    const showPopupBtn = document.querySelector(".login-btn");
    const formPopup = document.querySelector(".form-popup");
    const hidePopupBtn = formPopup?.querySelector(".close-btn");
    
    if (showPopupBtn && formPopup) {
        showPopupBtn.addEventListener("click", () => {
            document.body.classList.add("show-popup");
            formPopup.classList.remove("show-signup"); // Reset to login form
        });
    }
    
    if (hidePopupBtn && formPopup) {
        hidePopupBtn.addEventListener("click", () => {
            document.body.classList.remove("show-popup");
        });
    }

    // Login/Signup form toggle
    const signupLoginLinks = document.querySelectorAll(".bottom-link a");
    if (signupLoginLinks.length > 0 && formPopup) {
        signupLoginLinks.forEach(link => {
            link.addEventListener("click", (e) => {
                e.preventDefault();
                formPopup.classList[link.id === 'signup-link' ? 'add' : 'remove']("show-signup");
            });
        });
    }

    // Apply Now button functionality
    const applyBtns = document.querySelectorAll(".apply-btn");
    applyBtns.forEach(btn => {
        btn.addEventListener("click", function() {
            // If there's a login button, show login popup
            if (showPopupBtn) {
                showPopupBtn.click();
            } else {
                // Redirect to application form if already logged in
                window.location.href = "application_form.php";
            }
        });
    });

    // Verify certificate button functionality
    const verifyBtn = document.querySelector('.verify-btn');
    if (verifyBtn) {
        verifyBtn.addEventListener('click', function() {
            const verificationSection = document.querySelector('.verification-section');
            if (verificationSection) {
                verificationSection.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    }

    // Password matching validation for registration
    const registrationForm = document.querySelector('.form-box.signup form');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    if (registrationForm) {
        // Real-time password matching feedback
        if (password && confirmPassword) {
            confirmPassword.addEventListener('input', function() {
                const passwordMatchMessage = document.getElementById('password_match_message');
                if (passwordMatchMessage) {
                    if (this.value === password.value) {
                        passwordMatchMessage.textContent = "Passwords match";
                        passwordMatchMessage.style.color = "green";
                    } else {
                        passwordMatchMessage.textContent = "Passwords do not match";
                        passwordMatchMessage.style.color = "red";
                    }
                }
            });
        }
        
        // Form submission validation
        registrationForm.addEventListener('submit', function(e) {
            if (password && confirmPassword && password.value !== confirmPassword.value) {
                e.preventDefault();
                alert('Passwords do not match. Please try again.');
                return false;
            }
        });
    }

    // Certificate verification form handling
    const verificationForm = document.querySelector('.verification-form');
    if (verificationForm) {
        verificationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const certificateNumber = this.querySelector('input').value.trim();
            
            if (!certificateNumber) {
                alert('Please enter a certificate number');
                return;
            }
            
            // Simulate verification (replace with actual AJAX in production)
            if (typeof simulateVerification === 'function') {
                simulateVerification(certificateNumber);
            }
        });
    }
});
</script>
</footer>

  </body>
</html>
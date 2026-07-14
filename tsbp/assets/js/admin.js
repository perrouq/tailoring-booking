document.addEventListener('DOMContentLoaded', function() {
    // Desktop Sidebar toggle functionality
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.main-content');
    
    // Mobile menu elements
    const mobileMenuButton = document.getElementById('mobileMenuButton');
    const mobileDropdownMenu = document.getElementById('mobileDropdownMenu');
    const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
    
    // Check viewport size and set appropriate UI
    function checkViewportAndSetNavigation() {
        const isMobile = window.innerWidth <= 767;
        
        // For desktop - manage sidebar state
        if (!isMobile) {
            // Restore desktop sidebar state from localStorage
            if (localStorage.getItem('sidebar-collapsed') === 'true') {
                document.body.classList.add('sidebar-collapsed');
            } else {
                document.body.classList.remove('sidebar-collapsed');
            }
        }
    }
    
    // Run on page load
    checkViewportAndSetNavigation();
    
    // Add resize listener to handle transitions between mobile/desktop
    window.addEventListener('resize', function() {
        checkViewportAndSetNavigation();
        
        // Close mobile menu if transitioning to desktop
        if (window.innerWidth > 767) {
            mobileDropdownMenu.classList.remove('open');
            mobileMenuOverlay.style.display = 'none';
        }
    });
    
    // Desktop sidebar toggle
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-collapsed');
            
            // Save desktop sidebar state to localStorage
            if (document.body.classList.contains('sidebar-collapsed')) {
                localStorage.setItem('sidebar-collapsed', 'true');
            } else {
                localStorage.setItem('sidebar-collapsed', 'false');
            }
        });
    }
    
    // Mobile menu toggle
    if (mobileMenuButton) {
        mobileMenuButton.addEventListener('click', function() {
            mobileDropdownMenu.classList.toggle('open');
            
            if (mobileDropdownMenu.classList.contains('open')) {
                mobileMenuOverlay.style.display = 'block';
            } else {
                mobileMenuOverlay.style.display = 'none';
            }
        });
    }
    
    // Close mobile menu when clicking outside
    if (mobileMenuOverlay) {
        mobileMenuOverlay.addEventListener('click', function() {
            mobileDropdownMenu.classList.remove('open');
            mobileMenuOverlay.style.display = 'none';
        });
    }
    
    // Close mobile menu when selecting an option (optional)
    const mobileMenuLinks = document.querySelectorAll('.mobile-dropdown-menu a');
    mobileMenuLinks.forEach(link => {
        link.addEventListener('click', function() {
            mobileDropdownMenu.classList.remove('open');
            mobileMenuOverlay.style.display = 'none';
        });
    });
    
    // Auto-hide notifications after 5 seconds
    const notifications = document.querySelectorAll('.notification');
    notifications.forEach(notification => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(notification);
            bsAlert.close();
        }, 5000);
    });
    
    // Initialize Bootstrap tooltips and popovers
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
    
    // Preview uploaded images
    const fileInputs = document.querySelectorAll('.custom-file-input');
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const preview = document.querySelector(this.dataset.preview);
            if (preview && this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
    
    // Status change confirmation
    const statusButtons = document.querySelectorAll('.status-action');
    statusButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to change the status?')) {
                e.preventDefault();
            }
        });
    });
    
    // Print certificate
    const printButton = document.querySelector('.print-certificate');
    if (printButton) {
        printButton.addEventListener('click', function() {
            window.print();
        });
    }
});
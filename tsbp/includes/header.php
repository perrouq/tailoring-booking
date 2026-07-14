<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

// Include database connection
require_once 'config.php'; // Make sure this file creates the $pdo connection
require_once 'unread_messages.php';

// Get unread message count if user is logged in
$unreadMsgCount = 0;
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] && isset($_SESSION['user_id'])) {
	$unreadMsgCount = getUnreadMessageCount($pdo, $_SESSION['user_id']);
}
?>
<!DOCTYPE
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Tailor Booking System</title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<link rel="stylesheet" href="assets/css/styles.css">
	<style>
:root {
		--primary: #5e35b1;
		--primary-light: #7c51d1;
		--primary-dark: #4527a0;
		--secondary: #ff9800;
		--secondary-light: #ffb74d;
		--secondary-dark: #f57c00;
		--text-light: #ffffff;
		--text-dark: #212121;
		--gray-50: #f5f7fa;
		--gray-100: #e4e7eb;
		--gray-800: #2d3748;
		--red-500: #ef4444;
		--success: #10b981;
		--container-width: 1500px;
		--shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
		--shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
		--transition: all 0.3s ease;
	}

		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
		}

		body {
			font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
			background-color: var(--gray-50);
			color: var(--text-dark);
		}

/* Header Styles */
.navbar {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    box-shadow: var(--shadow-md);
    position: sticky;
    top: 0;
    z-index: 100;
    width: 100%; /* Ensure navbar takes full width */
}

.navbar-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%; /* Already 100% in mobile view */
    margin: 0 auto;
    padding: 0.75rem 1rem;
    height: 4.5rem;
}

		/* Mobile Layout */
		.menu-toggle {
			color: var(--text-light);
			background: none;
			border: none;
			cursor: pointer;
			padding: 0.5rem;
			font-size: 1.5rem;
			transition: var(--transition);
			order: 1;
		}

		.menu-toggle:hover {
			color: var(--secondary-light);
		}

		.nav-brand {
			display: flex;
			flex-direction: row;
			align-items: center;
			justify-content: center;
			flex-grow: 1;
			order: 2;
		}

		.nav-logo {
			font-size: 1rem;
			font-weight: 700;
			color: var(--text-light);
			cursor: pointer;
			margin: 0;
			text-transform: uppercase;
			letter-spacing: 1px;
			text-align: center;
		}

		.brand-logo {
			height: 40px;
			margin-left: 0.5rem;
			order: 3;
		}

		/* Desktop Navigation Links */
		.desktop-nav {
			display: none;
		}

		.desktop-nav-links {
			display: flex;
			gap: 1.5rem;
			align-items: center;
		}

		.desktop-nav-links a {
			color: var(--text-light);
			text-decoration: none;
			font-weight: 500;
			padding: 0.5rem 1rem;
			border-radius: 4px;
			transition: var(--transition);
			display: flex;
			align-items: center;
			gap: 0.5rem;
		}

		.desktop-nav-links a:hover {
			background-color: rgba(255, 255, 255, 0.1);
		}

		.desktop-nav-links .count-badge {
			position: relative;
			top: -10px;
			right: -3px;
			background-color: var(--secondary);
			color: var(--text-dark);
			font-size: 0.75rem;
			font-weight: bold;
			padding: 0.2rem 0.5rem;
			border-radius: 50%;
			box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
		}

		/* Mobile Menu */
		.mobile-menu {
			position: fixed;
			top: 4.5rem;
			left: -100%;
			width: 80%;
			max-width: 350px;
			height: calc(100vh - 4.5rem);
			background: white;
			box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
			transition: left 0.3s ease-in-out;
			overflow-y: auto;
			z-index: 1000;
			display: flex;
			flex-direction: column;
		}

		.mobile-menu.active {
			left: 0;
		}

		.mobile-nav-links {
			display: flex;
			flex-direction: column;
			padding: 1.5rem;
		}

		.mobile-nav-links a {
			display: flex;
			align-items: center;
			gap: 0.75rem;
			padding: 1rem;
			color: var(--gray-800);
			text-decoration: none;
			border-bottom: 1px solid var(--gray-100);
			font-weight: 500;
			transition: background-color 0.3s ease;
			position: relative;
		}

		.mobile-nav-links a:hover {
			background-color: var(--gray-100);
		}

		.mobile-nav-links svg {
			width: 20px;
			height: 20px;
		}

		/* Category Dropdown */
		.category-dropdown {
			max-height: 0;
			overflow: hidden;
			transition: max-height 0.3s ease-out;
			background-color: var(--gray-50);
		}

		.category-dropdown.active {
			max-height: 300px;
		}

		.category-dropdown a {
			padding-left: 2.5rem;
			background-color: var(--gray-50);
		}

		.category-toggle {
			position: relative;
		}

		.category-toggle .dropdown-icon {
			position: absolute;
			right: 1rem;
			top: 50%;
			transform: translateY(-50%) rotate(0deg);
			transition: transform 0.3s ease;
		}

		.category-toggle.active .dropdown-icon {
			transform: translateY(-50%) rotate(180deg);
		}

		/* Menu Overlay */
		.menu-overlay {
			position: fixed;
			top: 4.5rem;
			left: 0;
			width: 100%;
			height: calc(100vh - 4.5rem);
			background-color: rgba(0, 0, 0, 0.5);
			opacity: 0;
			visibility: hidden;
			transition: opacity 0.3s ease;
			z-index: 999;
		}

		.menu-overlay.active {
			opacity: 1;
			visibility: visible;
		}

		/* Count Badge */
		.count-badge {
			position: absolute;
			top: -4px;
			right: -6px;
			background-color: var(--secondary);
			color: var(--text-dark);
			font-size: 0.75rem;
			font-weight: bold;
			padding: 0.3rem 0.6rem;
			border-radius: 50%;
			box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
		}

		.hidden {
			display: none;
		}

		/* Desktop Responsive Design */
@media (min-width: 768px) {
			 .navbar-container {
        width: 100%; /* Keep full width in desktop view */
        max-width: var(--container-width); /* Optional: constrain to max container width */
        padding: 0 2rem;
    }
    
    .menu-toggle {
        display: none;
    }

    .nav-brand {
        order: 1;
        flex-grow: 0;
    }
    
    .brand-logo {
        order: 3;
        margin-left: 1rem;
    }

    .desktop-nav {
        display: flex;
        align-items: center;
        order: 2;
        flex-grow: 1;
        justify-content: center;
    }

    .mobile-menu {
        display: none;
    }
			/* Desktop dropdown */
			.desktop-dropdown {
				position: relative;
			}

			.desktop-dropdown-content {
				position: absolute;
				top: 100%;
				left: 0;
				width: 200px;
				background-color: white;
				box-shadow: var(--shadow-md);
				border-radius: 4px;
				overflow: hidden;
				display: none;
				z-index: 1000;
			}

			.desktop-dropdown:hover .desktop-dropdown-content {
				display: block;
			}

			.desktop-dropdown-content a {
				color: var(--text-dark);
				padding: 0.75rem 1rem;
				display: block;
				border-bottom: 1px solid var(--gray-100);
			}

			.desktop-dropdown-content a:hover {
				background-color: var(--gray-50);
			}

			.desktop-dropdown .dropdown-icon {
				margin-left: 0.5rem;
				transition: transform 0.3s ease;
			}

			.desktop-dropdown:hover .dropdown-icon {
				transform: rotate(180deg);
			}

		}

		/* Social Icons */
		.social-icons {
			display: flex;
			gap: 15px;
			margin-top: 10px;
		}

		.social-icon {
			display: flex;
			align-items: center;
			justify-content: center;
			width: 36px;
			height: 36px;
			border-radius: 50%;
			background-color: var(--primary-light);
			color: white;
			transition: all 0.3s ease;
		}

		.social-icon:hover {
			background-color: var(--secondary);
			transform: translateY(-3px);
		}
		.logo{
			height: 50px;
			width: 50px;
			border-radius: 50px;
		}
	</style>
</head>
<body>
	<nav class="navbar">
		<div class="navbar-container">
			<!-- Menu Toggle (Left on Mobile) -->
			<button class="menu-toggle" id="menuToggleBtn">
				<i class="fas fa-bars"></i>
			</button>

			<!-- Center Brand Name -->
			<div class="nav-brand">
				<h3 class="nav-logo" onclick="window.location.href='index.php'">
					<span>Tailoring S.B Platform</span>
				</h3>
			</div>

			<!-- Logo (Right) -->
			<div class="brand-logo">
				<img src="images/logo.png" class="logo" style="color: var(--secondary); font-size: 24px;">
			</div>

			<!-- Desktop Navigation Links -->
			<div class="desktop-nav">
				<div class="desktop-nav-links">
					<!-- Clothes Dropdown-->
					<div class="desktop-dropdown">
						<a href="javascript:void(0)">
							<i class="fas fa-list"></i> Services Types
							<i class="fas fa-chevron-down dropdown-icon"></i>
						</a>
						<div class="desktop-dropdown-content">
							<a href="clothes_types.php?category=SHADDAS">Shaddah's</a>
							<a href="clothes_types.php?category=KAFTANIS">Kaftani's</a>
							<a href="clothes_types.php?category=YADIS">Yadi's</a>
						</div>
					</div>

					<!-- Cart Link -->
					<a href="cart.php" class="cart-link" id="cartLink">
						<i class="fas fa-shopping-cart"></i> CART
						<span id="desktop-cart-count" class="count-badge hidden">0</span>
					</a>

					<!-- User Account Links -->
					<?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
					<a href="profile.php">
						<i class="fas fa-user"></i> PROFILE
					</a>
					<a href="orders.php">
						<i class="fas fa-clipboard-list"></i> ORDERS
					</a>
					<a href="messages.php">
						<i class="fas fa-comments"></i> MESSAGES
						<?php if ($unreadMsgCount > 0): ?>
						<span class="count-badge"><?php echo $unreadMsgCount; ?></span>
						<?php endif; ?>
					</a>
					<a href="includes/logout.php">
						<i class="fas fa-sign-out-alt"></i> LOGOUT
					</a>
					<?php else : ?>
					<a href="login.php">
						<i class="fas fa-sign-in-alt"></i> LOGIN
					</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</nav>

	<!-- Overlay for when menu is open -->
	<div class="menu-overlay" id="menuOverlay"></div>

	<!-- Mobile menu - now slides from left -->
	<div class="mobile-menu" id="mobileMenu">
		<div class="mobile-nav-links">
			<a href="index.php">
				<i class="fas fa-home"></i> Home
			</a>
			<!-- Category links  -->
			<a href="javascript:void(0)" id="categoryToggle" class="category-toggle">
				<i class="fas fa-list"></i> Product Types
				<i class="fas fa-chevron-down dropdown-icon"></i>
			</a>
			<!-- Category Dropdown Content  	   -->
			<div class="category-dropdown" id="categoryDropdown">
				<a href="clothes_types.php?category=KAFTANIS">
					<i class="fas fa-tshirt"></i> Kaftani's
				</a>
				<a href="clothes_types.php?category=SHADDAS">
					<i class="fas fa-tshirt"></i> Shedda's
				</a>
				<a href="clothes_types.php?category=YADIS">
					<i class="fas fa-tshirt"></i> Yadi's
				</a>
			</div>

			<!-- Cart link with count badge -->
			<a href="cart.php" class="cart-link" id="cartLink">
				<i class="fas fa-shopping-cart"></i> My Cart
				<span id="cart-count" class="count-badge hidden">0</span>
			</a>

			<!-- User account section -->
			<?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
			<a href="orders.php">
				<i class="fas fa-clipboard-list"></i> Orders
			</a>
			<a href="messages.php">
				<i class="fas fa-comments"></i> Messages
				<?php if ($unreadMsgCount > 0): ?>
				<span class="count-badge"><?php echo $unreadMsgCount; ?></span>
				<?php endif; ?>
			</a>
			<a href="profile.php">
				<i class="fas fa-user"></i> My Account
			</a>
			<a href="includes/logout.php">
				<i class="fas fa-sign-out-alt"></i> Logout
			</a>
			<?php else : ?>
			<a href="login.php">
				<i class="fas fa-sign-in-alt"></i> Login
			</a>
			<?php endif; ?>
		</div>
	</div>

	<!-- Add JavaScript for toggle functionality -->
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			const menuToggleBtn = document.getElementById('menuToggleBtn');
			const mobileMenu = document.getElementById('mobileMenu');
			const menuOverlay = document.getElementById('menuOverlay');
			const categoryToggle = document.getElementById('categoryToggle');
			const categoryDropdown = document.getElementById('categoryDropdown');

			// Toggle mobile menu
			menuToggleBtn.addEventListener('click', function() {
				mobileMenu.classList.toggle('active');
				menuOverlay.classList.toggle('active');
				document.body.style.overflow = mobileMenu.classList.contains('active') ? 'hidden': '';
			});

			// Close menu when clicking outside
			menuOverlay.addEventListener('click', function() {
				mobileMenu.classList.remove('active');
				menuOverlay.classList.remove('active');
				document.body.style.overflow = '';
			});

			// Category dropdown toggle
			categoryToggle.addEventListener('click', function() {
				this.classList.toggle('active');
				categoryDropdown.classList.toggle('active');

				// Rotate dropdown icon
				const dropdownIcon = this.querySelector('.dropdown-icon');
				dropdownIcon.style.transform = this.classList.contains('active')
				? 'translateY(-50%) rotate(180deg)': 'translateY(-50%) rotate(0deg)';
			});

			// Update cart count (example functionality)
			function updateCartCount() {
				// This would typically be populated from a session or local storage
				const cartCount = localStorage.getItem('cartCount') || 0;
				const cartCountBadges = document.querySelectorAll('#cart-count, #desktop-cart-count');

				cartCountBadges.forEach(badge => {
					if (cartCount > 0) {
						badge.textContent = cartCount;
						badge.classList.remove('hidden');
					} else {
						badge.classList.add('hidden');
					}
				});
			}

			// Call initially to set up cart count
			updateCartCount();
		});
	</script>
	<script>
		// Add this script to the footer.php or at the bottom of your header.php
		document.addEventListener('DOMContentLoaded', function() {
			// Only run if user is logged in
			if (document.querySelector('.count-badge')) {
				// Function to refresh unread message count
				function refreshMessageCount() {
					fetch('includes/get_unread_count.php')
					.then(response => response.json())
					.then(data => {
						const countBadges = document.querySelectorAll('.count-badge');

						if (data.count > 0) {
							// Update all count badges (desktop and mobile)
							countBadges.forEach(badge => {
								badge.textContent = data.count;
								badge.classList.remove('hidden');
							});
						} else {
							// Hide badges if no unread messages
							countBadges.forEach(badge => {
								badge.classList.add('hidden');
							});
						}
					})
					.catch(error => console.error('Error fetching message count:', error));
				}

				// Check for new messages every 60 seconds
				setInterval(refreshMessageCount, 60000);
			}
		});
	</script>
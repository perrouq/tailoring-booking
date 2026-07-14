<?php
session_start();
require_once('includes/config.php');
require_once('functions/functions.php');

// Check if already logged in
if (isset($_SESSION['admin_id'])) {
	header("Location: dashboard.php");
	exit();
} elseif (isset($_SESSION['tailor_id'])) {
	header("Location: ../tailor/dashboard.php");
	exit();
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
	$password = $_POST['password'];
	$user_type = $_POST['user_type']; // New field to determine if admin or tailor

	if (empty($email) || empty($password)) {
		$error = "All fields are required";
	} else {
		if ($user_type === 'admin') {
			// Admin login process
			$stmt = $conn->prepare("SELECT * FROM admins WHERE email = ? AND status = 'active'");
			$stmt->bind_param("s", $email);
			$stmt->execute();
			$result = $stmt->get_result();

			if ($result->num_rows === 1) {
				$admin = $result->fetch_assoc();

				// Verify password
				if (password_verify($password, $admin['password'])) {
					// Set session variables
					$_SESSION['admin_id'] = $admin['admin_id'];
					$_SESSION['admin_name'] = $admin['fullname'];
					$_SESSION['admin_email'] = $admin['email'];
					$_SESSION['admin_role'] = $admin['role'];

					// Update last login time
					$updateStmt = $conn->prepare("UPDATE admins SET last_login = NOW() WHERE admin_id = ?");
					$updateStmt->bind_param("i", $admin['admin_id']);
					$updateStmt->execute();

					// Log the action
					logActivity(null, $admin['admin_id'], 'login', 'Admin logged in');

					header("Location: dashboard.php");
					exit();
				} else {
					$error = "Invalid email or password";
				}
			} else {
				$error = "Invalid email or password";
			}
		} else {
			// Tailor login process
			$stmt = $conn->prepare("SELECT * FROM tailors WHERE email = ? AND status = 'active'");
			$stmt->bind_param("s", $email);
			$stmt->execute();
			$result = $stmt->get_result();

			if ($result->num_rows === 1) {
				$tailor = $result->fetch_assoc();

				// Verify password
				if (password_verify($password, $tailor['password'])) {
					// Set session variables
					$_SESSION['tailor_id'] = $tailor['tailor_id'];
					$_SESSION['tailor_name'] = $tailor['fullname'];
					$_SESSION['tailor_email'] = $tailor['email'];

					// Update last login time
					$updateStmt = $conn->prepare("UPDATE tailors SET last_login = NOW() WHERE tailor_id = ?");
					$updateStmt->bind_param("i", $tailor['tailor_id']);
					$updateStmt->execute();

					// Log the action
					logActivity(null, null, 'login', 'Tailor ' . $tailor['fullname'] . ' logged in', $tailor['tailor_id']);

					header("Location: ../tailor/dashboard.php");
					exit();
				} else {
					$error = "Invalid email or password";
				}
			} else {
				$error = "Invalid email or password";
			}
		}
	}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Login | Tailoring Services Platform</title>
	<link rel="stylesheet" href="assets/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        --gray-200: #cbd2d9;
        --gray-300: #9aa5b1;
        --gray-800: #2d3748;
        --red-500: #ef4444;
        --success: #10b981;
        --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        --transition: all 0.3s ease;
    }

	* {
		margin: 0;
		padding: 0;
		box-sizing: border-box;
		font-family: 'Segoe UI', 'Roboto', 'Helvetica Neue', sans-serif;
	}

	body {
		background: linear-gradient(135deg, var(--primary-dark), var(--primary));
		height: 100vh;
		display: flex;
		align-items: center;
		justify-content: center;
		overflow: hidden;
		position: relative;
	}

	/* Background patterns and decorations */
	.background-pattern {
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		background-image: 
			radial-gradient(circle at 10% 20%, rgba(255, 255, 255, 0.05) 0%, transparent 20%),
			radial-gradient(circle at 90% 80%, rgba(255, 255, 255, 0.05) 0%, transparent 20%),
			radial-gradient(circle at 50% 50%, rgba(255, 255, 255, 0.03) 0%, transparent 40%);
		z-index: 0;
	}

	.floating-shapes {
		position: absolute;
		width: 100%;
		height: 100%;
		z-index: 0;
		overflow: hidden;
	}

	.shape {
		position: absolute;
		background-color: rgba(255, 255, 255, 0.05);
		border-radius: 50%;
		animation: float 15s infinite ease-in-out;
	}

	.shape-1 {
		width: 200px;
		height: 200px;
		top: 10%;
		left: 10%;
		animation-delay: 0s;
	}

	.shape-2 {
		width: 300px;
		height: 300px;
		top: 60%;
		left: 80%;
		animation-delay: 2s;
	}

	.shape-3 {
		width: 150px;
		height: 150px;
		top: 80%;
		left: 20%;
		animation-delay: 4s;
	}

	.shape-4 {
		width: 100px;
		height: 100px;
		top: 20%;
		left: 80%;
		animation-delay: 6s;
	}

	/* Ripple shape */
	.ripple {
		position: absolute;
		border: 2px solid rgba(255, 255, 255, 0.1);
		border-radius: 50%;
		animation: ripple 10s linear infinite;
		opacity: 0;
	}

	.ripple-1 {
		width: 100px;
		height: 100px;
		top: 30%;
		left: 30%;
	}

	.ripple-2 {
		width: 200px;
		height: 200px;
		top: 70%;
		left: 60%;
		animation-delay: 3s;
	}

	.ripple-3 {
		width: 300px;
		height: 300px;
		top: 40%;
		left: 50%;
		animation-delay: 6s;
	}

	@keyframes ripple {
		0% {
			transform: scale(0);
			opacity: 0.5;
		}
		100% {
			transform: scale(5);
			opacity: 0;
		}
	}

	@keyframes float {
		0%, 100% {
			transform: translateY(0) translateX(0) rotate(0deg);
		}
		25% {
			transform: translateY(-30px) translateX(15px) rotate(5deg);
		}
		50% {
			transform: translateY(15px) translateX(30px) rotate(10deg);
		}
		75% {
			transform: translateY(30px) translateX(-15px) rotate(5deg);
		}
	}

	/* Login container and card */
	.login-container {
		width: 430px;
		max-width: 95%;
		z-index: 10;
		perspective: 1000px;
	}

	.login-card {
		background: var(--text-light);
		border-radius: 24px;
		box-shadow: var(--shadow-lg), 0 25px 50px rgba(0, 0, 0, 0.15);
		padding: 40px;
		overflow: hidden;
		position: relative;
		transform-style: preserve-3d;
		animation: cardEnter 0.8s ease-out;
	}

	/* Card accent line */
	.card-accent {
		position: absolute;
		top: 0;
		left: 0;
		width: 6px;
		height: 100%;
		background: linear-gradient(to bottom, var(--secondary), var(--secondary-light));
	}

	@keyframes cardEnter {
		0% {
			opacity: 0;
			transform: translateY(30px) rotateX(10deg);
		}
		100% {
			opacity: 1;
			transform: translateY(0) rotateX(0);
		}
	}

	/* Logo and header */
	.login-header {
		text-align: center;
		margin-bottom: 32px;
		position: relative;
	}

	.login-header h2 {
		color: var(--primary);
		font-size: 32px;
		margin-bottom: 8px;
		font-weight: 700;
		letter-spacing: -0.5px;
	}

	.login-header p {
		color: var(--gray-800);
		font-size: 16px;
		opacity: 0.8;
	}

	.logo {
		text-align: center;
		margin-bottom: 24px;
	}

	.logo-icon {
		display: flex;
		align-items: center;
		justify-content: center;
		width: 80px;
		height: 80px;
		background: var(--primary-light);
		border-radius: 50%;
		margin: 0 auto;
		box-shadow: var(--shadow-md);
		position: relative;
		overflow: hidden;
	}

	.logo-icon::after {
		content: '';
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		background: linear-gradient(45deg, rgba(255,255,255,0.2), transparent);
	}

	.logo-icon i {
		font-size: 36px;
		color: var(--text-light);
	}

	/* Form styling */
	.form-group {
		margin-bottom: 24px;
		position: relative;
	}

	.form-group label {
		display: block;
		margin-bottom: 8px;
		color: var(--primary-dark);
		font-weight: 600;
		font-size: 14px;
	}

	.form-group input,
	.form-group select {
		width: 100%;
		padding: 16px 16px 16px 48px;
		border: 2px solid var(--gray-100);
		border-radius: 12px;
		font-size: 15px;
		transition: var(--transition);
		color: var(--text-dark);
		background-color: var(--gray-50);
	}

	.form-group input:focus,
	.form-group select:focus {
		border-color: var(--primary-light);
		box-shadow: 0 0 0 4px rgba(94, 53, 177, 0.15);
		outline: none;
		background-color: var(--text-light);
	}

	.form-group .input-icon {
		position: absolute;
		left: 16px;
		top: 39px;
		color: var(--primary);
		font-size: 18px;
		transition: var(--transition);
		opacity: 0.8;
	}

	.form-group input:focus + .input-icon,
	.form-group select:focus + .input-icon {
		color: var(--primary);
		transform: scale(1.1);
		opacity: 1;
	}

	.password-toggle {
		position: absolute;
		right: 16px;
		top: 42px;
		color: var(--gray-300);
		cursor: pointer;
		transition: var(--transition);
		z-index: 10;
	}

	.password-toggle:hover {
		color: var(--primary);
	}

	/* Select styling */
	.form-group select {
		appearance: none;
		background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%235e35b1' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
		background-repeat: no-repeat;
		background-position: right 16px center;
		cursor: pointer;
	}

	.form-group .select-wrapper {
		position: relative;
	}

	.form-group .input-icon.select-icon {
		position: absolute;
		left: 16px;
		top: 41px;
		color: var(--primary);
		font-size: 18px;
		z-index: 1;
		pointer-events: none;
	}

	/* Button styling */
	.btn {
		background: linear-gradient(45deg, var(--primary), var(--primary-light));
		color: var(--text-light);
		border: none;
		padding: 16px 20px;
		font-size: 16px;
		font-weight: 600;
		border-radius: 12px;
		cursor: pointer;
		transition: var(--transition);
		width: 100%;
		position: relative;
		overflow: hidden;
		display: flex;
		align-items: center;
		justify-content: center;
		box-shadow: var(--shadow-md), 0 6px 20px rgba(94, 53, 177, 0.3);
	}

	.btn::before {
		content: '';
		position: absolute;
		top: 0;
		left: -100%;
		width: 100%;
		height: 100%;
		background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
		transition: 0.6s;
	}

	.btn:hover {
		background: linear-gradient(45deg, var(--primary-dark), var(--primary));
		box-shadow: var(--shadow-lg), 0 8px 25px rgba(94, 53, 177, 0.4);
		transform: translateY(-2px);
	}

	.btn:hover::before {
		left: 100%;
	}

	.btn:active {
		transform: translateY(0);
		box-shadow: var(--shadow-md), 0 4px 15px rgba(94, 53, 177, 0.3);
	}

	.btn i {
		margin-right: 10px;
		font-size: 18px;
	}

	/* Alert styling */
	.alert {
		padding: 16px;
		border-radius: 12px;
		margin-bottom: 24px;
		position: relative;
		animation: alertEnter 0.5s ease;
		display: flex;
		align-items: center;
	}

	@keyframes alertEnter {
		0% {
			opacity: 0;
			transform: translateY(-10px);
		}
		100% {
			opacity: 1;
			transform: translateY(0);
		}
	}

	.alert-danger {
		background-color: rgba(239, 68, 68, 0.1);
		border-left: 4px solid var(--red-500);
		color: var(--red-500);
	}

	.alert i {
		margin-right: 12px;
		font-size: 18px;
	}

	/* Footer */
	.login-footer {
		text-align: center;
		margin-top: 28px;
		padding-top: 20px;
		border-top: 1px solid var(--gray-100);
	}

	.login-footer a {
		color: var(--primary);
		text-decoration: none;
		font-weight: 600;
		transition: var(--transition);
		display: inline-flex;
		align-items: center;
		padding: 8px 12px;
		border-radius: 8px;
	}

	.login-footer a:hover {
		color: var(--primary-dark);
		background-color: var(--gray-50);
		transform: translateY(-2px);
	}

	.login-footer a i {
		margin-right: 8px;
	}

	/* User type tabs */
	.user-type-tabs {
		display: flex;
		background-color: var(--gray-50);
		border-radius: 12px;
		padding: 4px;
		margin-bottom: 24px;
		position: relative;
		overflow: hidden;
	}

	.user-type-tab {
		flex: 1;
		text-align: center;
		padding: 12px;
		cursor: pointer;
		font-weight: 600;
		color: var(--gray-800);
		transition: var(--transition);
		border-radius: 8px;
		z-index: 1;
		position: relative;
	}

	.user-type-tab.active {
		color: var(--text-light);
	}

	.user-type-tab i {
		margin-right: 8px;
		font-size: 16px;
	}

	.tab-indicator {
		position: absolute;
		height: calc(100% - 8px);
		top: 4px;
		width: calc(50% - 4px);
		background: linear-gradient(45deg, var(--primary), var(--primary-light));
		border-radius: 8px;
		transition: var(--transition);
		left: 4px;
	}

	.tab-indicator.tailor {
		left: calc(50% + 0px);
	}

	/* Responsive adjustments */
	@media (max-width: 480px) {
		.login-card {
			padding: 30px 20px;
		}

		.login-header h2 {
			font-size: 28px;
		}

		.user-type-tab {
			padding: 10px 8px;
			font-size: 14px;
		}

		.user-type-tab i {
			margin-right: 4px;
		}

		.form-group input,
		.form-group select {
			padding: 14px 14px 14px 42px;
		}

		.form-group .input-icon {
			left: 14px;
			top: 38px;
		}
	}
	</style>
</head>
<body class="login-page">
	<!-- Enhanced background with patterns & shapes -->
	<div class="background-pattern"></div>
	<div class="floating-shapes">
		<div class="shape shape-1"></div>
		<div class="shape shape-2"></div>
		<div class="shape shape-3"></div>
		<div class="shape shape-4"></div>
		<div class="ripple ripple-1"></div>
		<div class="ripple ripple-2"></div>
		<div class="ripple ripple-3"></div>
	</div>

	<div class="login-container">
		<div class="login-card">
			<!-- Card accent line -->
			<div class="card-accent"></div>
			
			<div class="logo">
				<div class="logo-icon">
					<i class="fas fa-scissors"></i>
				</div>
			</div>
			
			<div class="login-header">
				<h2>Tailoring Platform</h2>
				<p>Sign in to your account</p>
			</div>

			<!-- Tabbed interface for user type selection -->
			<div class="user-type-tabs">
				<div class="tab-indicator" id="tabIndicator"></div>
				<div class="user-type-tab active" data-type="admin" id="adminTab">
					<i class="fas fa-user-shield"></i> Admin
				</div>
				<div class="user-type-tab" data-type="tailor" id="tailorTab">
					<i class="fas fa-cut"></i> Tailor
				</div>
			</div>

			<div id="errorContainer">
				<?php if (!empty($error)): ?>
				<div class="alert alert-danger">
					<i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
				</div>
				<?php endif; ?>
			</div>

			<form id="loginForm" action="" method="POST">
				<input type="hidden" id="user_type" name="user_type" value="admin">
				
				<div class="form-group">
					<label for="email">Email Address</label>
					<input type="email" id="email" name="email" required>
					<i class="fas fa-envelope input-icon"></i>
				</div>
				
				<div class="form-group">
					<label for="password">Password</label>
					<input type="password" id="password" name="password" required>
					<i class="fas fa-lock input-icon"></i>
					<i class="fas fa-eye password-toggle" id="togglePassword"></i>
				</div>
				
				<div class="form-group">
					<button type="submit" class="btn btn-primary btn-block">
						<i class="fas fa-sign-in-alt"></i> Sign In
					</button>
				</div>
			</form>
			
			<div class="login-footer">
				<a href="../index.php"><i class="fas fa-home"></i> Return to Homepage</a>
			</div>
		</div>
	</div>

	<script src="assets/js/bootstrap.bundle.min.js" defer></script>

	<script>
		// Toggle password visibility
		const togglePassword = document.getElementById('togglePassword');
		const passwordInput = document.getElementById('password');

		togglePassword.addEventListener('click', function() {
			const type = passwordInput.getAttribute('type') === 'password' ? 'text': 'password';
			passwordInput.setAttribute('type', type);
			this.classList.toggle('fa-eye');
			this.classList.toggle('fa-eye-slash');

			// Add animation
			this.style.transform = 'scale(1.2)';
			setTimeout(() => {
				this.style.transform = 'scale(1)';
			}, 200);
		});

		// Tab switching functionality
		const adminTab = document.getElementById('adminTab');
		const tailorTab = document.getElementById('tailorTab');
		const userTypeInput = document.getElementById('user_type');
		const tabIndicator = document.getElementById('tabIndicator');

		adminTab.addEventListener('click', function() {
			adminTab.classList.add('active');
			tailorTab.classList.remove('active');
			userTypeInput.value = 'admin';
			tabIndicator.classList.remove('tailor');
		});

		tailorTab.addEventListener('click', function() {
			tailorTab.classList.add('active');
			adminTab.classList.remove('active');
			userTypeInput.value = 'tailor';
			tabIndicator.classList.add('tailor');
		});

		// Input focus effects
		const inputs = document.querySelectorAll('input');
		inputs.forEach(input => {
			input.addEventListener('focus', function() {
				this.parentElement.querySelector('label').style.color = 'var(--primary)';
			});

			input.addEventListener('blur', function() {
				this.parentElement.querySelector('label').style.color = '';
			});
		});

		// Add loading state to button when form is submitted
		const loginForm = document.getElementById('loginForm');
		loginForm.addEventListener('submit', function() {
			const button = this.querySelector('button');
			button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in...';
			button.disabled = true;
		});
	</script>
</body>
</html>
<?php
session_start();

// Check if user has verified their identity
if (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_token'])) {
	$_SESSION['flash_message'] = "Please verify your identity first.";
	$_SESSION['flash_type'] = "alert-danger";
	header("Location: forgot_password.php");
	exit();
}

$email = $_SESSION['reset_email'];
$token = $_SESSION['reset_token'];
?>


<?php include 'includes/header.php'; ?>

<main class="auth-page">
	<div class="auth-container">
		<div class="auth-card">
			<div class="auth-header">
				<div class="logo-container">
					<i class="fas fa-key logo-icon"></i>
				</div>
				<h2>Reset Your Password</h2>
				<p>
					Create a strong new password for your account
				</p>
			</div>

			<div class="tab-content active">
				<?php if (isset($_SESSION['flash_message'])): ?>
				<div class="alert <?php echo $_SESSION['flash_type']; ?>">
					<?php
					echo $_SESSION['flash_message'];
					unset($_SESSION['flash_message']);
					unset($_SESSION['flash_type']);
					?>
				</div>
				<?php endif; ?>

				<form action="includes/process_reset_password.php" method="post">
					<input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
					<input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

					<div class="form-group">
						<label for="password"><i class="fas fa-lock"></i> New Password</label>
						<div class="password-input-container">
							<input type="password" id="password" name="password" required minlength="8"
							oninput="checkPasswordStrength(this.value)">
							<span class="toggle-password" onclick="togglePasswordVisibility('password')">
								<i class="fas fa-eye"></i>
							</span>
						</div>

						<!-- Password strength indicator -->
						<div class="password-strength">
							<div class="strength-bar">
								<div class="strength-progress" id="password-strength-bar"></div>
							</div>
							<div class="strength-text" id="password-strength-text">
								Password strength
							</div>
						</div>
					</div>

					<div class="form-group">
						<label for="confirm_password"><i class="fas fa-lock"></i> Confirm New Password</label>
						<div class="password-input-container">
							<input type="password" id="confirm_password" name="confirm_password" required minlength="8">
							<span class="toggle-password" onclick="togglePasswordVisibility('confirm_password')">
								<i class="fas fa-eye"></i>
							</span>
						</div>
					</div>

					<div class="form-group">
						<button type="submit" class="btn btn-primary">Reset Password</button>
					</div>
				</form>

				<div class="text-center" style="margin-top: 1.5rem;">
					<a href="login.php" class="forgot-password">
						<i class="fas fa-arrow-left"></i> Back to Login
					</a>
				</div>
			</div>
		</div>
	</div>
</main>

<?php include 'includes/footer.php'; ?>

<script>
	function togglePasswordVisibility(inputId) {
		const passwordInput = document.getElementById(inputId);
		const icon = passwordInput.nextElementSibling.querySelector('i');

		if (passwordInput.type === "password") {
			passwordInput.type = "text";
			icon.classList.remove("fa-eye");
			icon.classList.add("fa-eye-slash");
		} else {
			passwordInput.type = "password";
			icon.classList.remove("fa-eye-slash");
			icon.classList.add("fa-eye");
		}
	}

	function checkPasswordStrength(password) {
		const bar = document.getElementById('password-strength-bar');
		const text = document.getElementById('password-strength-text');

		// Calculate strength
		let strength = 0;

		if (password.length >= 8) strength += 1;
		if (password.match(/[a-z]+/)) strength += 1;
		if (password.match(/[A-Z]+/)) strength += 1;
		if (password.match(/[0-9]+/)) strength += 1;
		if (password.match(/[^a-zA-Z0-9]+/)) strength += 1;

		// Update UI
		switch (strength) {
			case 0:
				bar.style.width = "0%";
				bar.style.backgroundColor = "#f8d7da";
				text.textContent = "No password";
				text.style.color = "#721c24";
				break;
			case 1:
				bar.style.width = "20%";
				bar.style.backgroundColor = "#f8d7da";
				text.textContent = "Very weak";
				text.style.color = "#721c24";
				break;
			case 2:
				bar.style.width = "40%";
				bar.style.backgroundColor = "#f8d7da";
				text.textContent = "Weak";
				text.style.color = "#721c24";
				break;
			case 3:
				bar.style.width = "60%";
				bar.style.backgroundColor = "#fff3cd";
				text.textContent = "Medium";
				text.style.color = "#856404";
				break;
			case 4:
				bar.style.width = "80%";
				bar.style.backgroundColor = "#d4edda";
				text.textContent = "Strong";
				text.style.color = "#155724";
				break;
			case 5:
				bar.style.width = "100%";
				bar.style.backgroundColor = "#d4edda";
				text.textContent = "Very strong";
				text.style.color = "#155724";
				break;
		}
	}
</script>
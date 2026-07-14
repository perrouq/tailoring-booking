<?php
session_start();
?>
<?php include 'includes/header.php'; ?>

<main class="auth-page">
	<div class="auth-container">
		<div class="auth-card">
			<div class="auth-header">
				<div class="logo-container">
					<i class="fas fa-lock-open logo-icon"></i>
				</div>
				<h2>Account Recovery</h2>
				<p>
					Let's help you get back into your account
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

				<?php if (isset($_SESSION['show_verification'])): ?>
				<div class="verification-info" style="background-color: var(--info-light); border-left: 6px solid var(--info); padding: 15px; margin-bottom: 20px; border-radius: 4px;">
					<p>
						We found your account. Please verify your identity by entering the full email address and phone number associated with your account:
					</p>
					<p>
						<strong>Email:</strong> <span class="masked-info"><?php echo $_SESSION['masked_email']; ?></span>
					</p>
					<p>
						<strong>Phone:</strong> <span class="masked-info"><?php echo $_SESSION['masked_phone']; ?></span>
					</p>
				</div>

				<form action="includes/process_verify_identity.php" method="post">
					<div class="form-group">
						<label for="email"><i class="fas fa-envelope"></i> Full Email Address</label>
						<input type="email" id="email" name="email" required>
					</div>
					<div class="form-group">
						<label for="phone"><i class="fas fa-phone"></i> Full Phone Number</label>
						<input type="tel" id="phone" name="phone" required placeholder="e.g. +1234567890">
					</div>
					<div class="form-group">
						<button type="submit" class="btn btn-primary">Verify Identity</button>
					</div>
				</form>

				<?php else : ?>
				<p class="mb-4">
					Enter your last name to start the password recovery process.
				</p>

				<form action="includes/process_forgot_password.php" method="post">
					<div class="form-group">
						<label for="last_name"><i class="fas fa-user"></i> Last Name</label>
						<input type="text" id="last_name" name="last_name" required>
					</div>
					<div class="form-group">
						<button type="submit" class="btn btn-primary">Find Account</button>
					</div>
				</form>
				<?php endif; ?>

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
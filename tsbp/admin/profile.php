<?php
session_start();
require_once('includes/config.php');
require_once('functions/auth_functions.php');
require_once('functions/admin_functions.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
	header("Location: index.php");
	exit();
}

$admin_id = $_SESSION['admin_id'];

// Get admin details
$admin = getAdminDetails($conn, $admin_id);

if (!$admin) {
	$_SESSION['error'] = "Admin profile not found";
	header("Location: dashboard.php");
	exit();
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
	$fullname = htmlspecialchars(trim($_POST['fullname']), ENT_QUOTES, 'UTF-8');
	$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
	$phone = htmlspecialchars(trim($_POST['phone']), ENT_QUOTES, 'UTF-8');
	// Validate inputs
	$errors = [];

	if (empty($fullname)) {
		$errors[] = "Full name is required";
	}

	if (empty($email)) {
		$errors[] = "Email is required";
	} elseif (!htmlspecialchars($email, FILTER_VALIDATE_EMAIL)) {
		$errors[] = "Invalid email format";
	} else {
		// Check if email exists for other admins
		$stmt = $conn->prepare("SELECT admin_id FROM admins WHERE email = ? AND admin_id != ?");
		$stmt->bind_param("si", $email, $admin_id);
		$stmt->execute();
		$result = $stmt->get_result();

		if ($result->num_rows > 0) {
			$errors[] = "Email already exists for another admin";
		}
	}

	if (empty($phone)) {
		$errors[] = "Phone number is required";
	}

	// Process update if no errors
	if (empty($errors)) {
		$updateStmt = $conn->prepare("UPDATE admins SET fullname = ?, email = ?, phone = ? WHERE admin_id = ?");
		$updateStmt->bind_param("sssi", $fullname, $email, $phone, $admin_id);

		if ($updateStmt->execute()) {
			// Update session data
			$_SESSION['admin_name'] = $fullname;
			$_SESSION['admin_email'] = $email;

			// Log activity
			logActivity(null, $admin_id, 'profile_update', 'Admin updated profile information');

			$_SESSION['success'] = "Profile updated successfully";

			echo "<script>window.location.href = 'profile.php';</script>";

			// header("Location: profile.php");
			exit();
		} else {
			$errors[] = "Failed to update profile. Please try again.";
		}
	}
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
	$current_password = $_POST['current_password'];
	$new_password = $_POST['new_password'];
	$confirm_password = $_POST['confirm_password'];

	// Validate inputs
	$errors = [];

	if (empty($current_password)) {
		$errors[] = "Current password is required";
	}

	if (empty($new_password)) {
		$errors[] = "New password is required";
	} elseif (strlen($new_password) < 8) {
		$errors[] = "Password must be at least 8 characters long";
	}

	if ($new_password !== $confirm_password) {
		$errors[] = "New passwords do not match";
	}

	// Verify current password
	if (empty($errors)) {
		if (!password_verify($current_password, $admin['password'])) {
			$errors[] = "Current password is incorrect";
		}
	}

	// Process password change if no errors
	if (empty($errors)) {
		$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

		$updateStmt = $conn->prepare("UPDATE admins SET password = ? WHERE admin_id = ?");
		$updateStmt->bind_param("si", $hashed_password, $admin_id);

		if ($updateStmt->execute()) {
			// Log activity
			logActivity(null, $admin_id, 'password_change', 'Admin changed password');

			$_SESSION['success'] = "Password changed successfully";

			echo "<script>window.location.href = 'profile.php';</script>";

			//header("Location: profile.php");
			exit();
		} else {
			$errors[] = "Failed to change password. Please try again.";
		}
	}
}

// Get recent activity logs for this admin
$recentLogs = getAdminActivityLogs($conn, $admin_id, 5);
?>

<?php include('includes/header.php'); ?>
<?php include('includes/sidebar.php'); ?>

<main class="main-content">
	<div class="container">
		<div class="page-header">
			<h1>My Profile</h1>
			<p>
				Manage your account information
			</p>
		</div>

		<?php if (isset($_SESSION['success'])): ?>
		<div class="alert alert-success">
			<?php
			echo $_SESSION['success'];
			unset($_SESSION['success']);
			?>
		</div>
		<?php endif; ?>

		<?php if (isset($errors) && !empty($errors)): ?>
		<div class="alert alert-danger">
			<ul class="mb-0">
				<?php foreach ($errors as $error): ?>
				<li><?php echo $error; ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php endif; ?>

		<div class="row">
			<div class="col-lg-8">
				<div class="card">
					<div class="card-header">
						<h3>Edit Profile</h3>
					</div>
					<div class="card-body">
						<form action="" method="POST">
							<div class="form-group">
								<label for="fullname">Full Name</label>
								<input type="text" id="fullname" name="fullname" class="form-control" value="<?php echo htmlspecialchars($admin['fullname']); ?>" required>
							</div>

							<div class="form-group">
								<label for="email">Email Address</label>
								<input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
							</div>

							<div class="form-group">
								<label for="phone">Phone Number</label>
								<input type="text" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($admin['phone']); ?>" required>
							</div>

							<div class="form-group">
								<label for="role">Role</label>
								<input type="text" id="role" class="form-control" value="<?php echo ucfirst($admin['role']); ?>" readonly>
								<small class="form-text text-muted">Role cannot be changed</small>
							</div>

							<div class="form-group">
								<button type="submit" name="update_profile" class="btn btn-primary">
									<i class="fas fa-save"></i> Update Profile
								</button>
							</div>
						</form>
					</div>
				</div>

				<div class="card mt-4">
					<div class="card-header">
						<h3>Change Password</h3>
					</div>
					<div class="card-body">
						<form action="" method="POST">
							<div class="form-group">
								<label for="current_password">Current Password</label>
								<input type="password" id="current_password" name="current_password" class="form-control" required>
							</div>

							<div class="form-group">
								<label for="new_password">New Password</label>
								<input type="password" id="new_password" name="new_password" class="form-control" required>
								<small class="form-text text-muted">Password must be at least 8 characters long</small>
							</div>

							<div class="form-group">
								<label for="confirm_password">Confirm New Password</label>
								<input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
							</div>

							<div class="form-group">
								<button type="submit" name="change_password" class="btn btn-warning">
									<i class="fas fa-key"></i> Change Password
								</button>
							</div>
						</form>
					</div>
				</div>
			</div>

			<div class="col-lg-4">
				<div class="card">
					<div class="card-header">
						<h3>Account Information</h3>
					</div>
					<div class="card-body">
						<div class="admin-profile">
							<div class="admin-avatar">
								<span class="avatar-text"><?php echo strtoupper(substr($admin['fullname'], 0, 1)); ?></span>
							</div>

							<h2 class="admin-name"><?php echo htmlspecialchars($admin['fullname']); ?></h2>
							<span class="admin-role badge bg-primary"><?php echo ucfirst($admin['role']); ?></span>

							<div class="account-details mt-4">
								<div class="detail-item">
									<span class="label"><i class="fas fa-calendar"></i> Created</span>
									<span class="value"><?php echo date('M d, Y', strtotime($admin['created_at'])); ?></span>
								</div>

								<div class="detail-item">
									<span class="label"><i class="fas fa-clock"></i> Last Login</span>
									<span class="value">
										<?php echo $admin['last_login'] ? date('M d, Y H:i', strtotime($admin['last_login'])) : 'Never'; ?>
									</span>
								</div>

								<div class="detail-item">
									<span class="label"><i class="fas fa-toggle-on"></i> Status</span>
									<span class="value badge bg-<?php echo $admin['status'] === 'active' ? 'success' : 'danger'; ?>">
										<?php echo ucfirst($admin['status']); ?>
									</span>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="card mt-4">
					<div class="card-header">
						<h3>Recent Activity</h3>
					</div>
					<div class="card-body">
						<ul class="activity-list">
							<?php if (empty($recentLogs)): ?>
							<li class="no-activity">No recent activity</li>
							<?php else : ?>
							<?php foreach ($recentLogs as $log): ?>
							<li class="activity-item">
								<div class="activity-icon bg-<?php echo getActivityIconClass($log['action']); ?>">
									<i class="fas fa-<?php echo getActivityIcon($log['action']); ?>"></i>
								</div>
								<div class="activity-content">
									<p>
										<?php echo htmlspecialchars($log['description']); ?>
									</p>
									<small><?php echo date('M d, Y H:i', strtotime($log['timestamp'])); ?></small>
								</div>
							</li>
							<?php endforeach; ?>
							<?php endif; ?>
						</ul>

						<div class="text-center mt-3">
							<a href="activity_logs.php?admin=<?php echo $admin_id; ?>" class="btn btn-outline">
								View All Activity
							</a>
						</div>
					</div>
				</div>

				<?php if ($_SESSION['admin_role'] === 'super_admin'): ?>
				<div class="card mt-4">
					<div class="card-header">
						<h3>Admin Management</h3>
					</div>
					<div class="card-body">
						<p>
							As a Super Admin, you can manage other admin accounts in the system.
						</p>
						<a href="admins.php" class="btn btn-primary btn-block">
							<i class="fas fa-users-cog"></i> Manage Admins
						</a>
					</div>
				</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</main>

<?php include('includes/footer.php'); ?>
<script>
	// Password validation
	document.addEventListener('DOMContentLoaded', function() {
		const newPasswordInput = document.getElementById('new_password');
		const confirmPasswordInput = document.getElementById('confirm_password');

		confirmPasswordInput.addEventListener('input', function() {
			if (newPasswordInput.value !== confirmPasswordInput.value) {
				confirmPasswordInput.setCustomValidity("Passwords don't match");
			} else {
				confirmPasswordInput.setCustomValidity('');
			}
		});

		newPasswordInput.addEventListener('input',
			function() {
				if (confirmPasswordInput.value !== '' && newPasswordInput.value !== confirmPasswordInput.value) {
					confirmPasswordInput.setCustomValidity("Passwords don't match");
				} else {
					confirmPasswordInput.setCustomValidity('');
				}
			});
	});
</script>
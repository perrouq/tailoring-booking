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
$success_message = '';
$error_message = '';

// Fetch current admin data
$stmt = $conn->prepare("SELECT * FROM admins WHERE admin_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
	$_SESSION['error'] = "Admin account not found";
	header("Location: dashboard.php");
	exit();
}

$admin = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Basic info update
	if (isset($_POST['update_info'])) {
		$fullname = htmlspecialchars(trim($_POST['fullname']), ENT_QUOTES, 'UTF-8');
		$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
		$phone = htmlspecialchars(trim($_POST['phone']), ENT_QUOTES, 'UTF-8');
		// Validate inputs
		if (empty($fullname) || empty($email) || empty($phone)) {
			$error_message = "All fields are required";
		} else {
			// Check if email already exists for another admin
			$check_stmt = $conn->prepare("SELECT admin_id FROM admins WHERE email = ? AND admin_id != ?");
			$check_stmt->bind_param("si", $email, $admin_id);
			$check_stmt->execute();
			$check_result = $check_stmt->get_result();

			if ($check_result->num_rows > 0) {
				$error_message = "Email already in use by another admin";
			} else {
				// Update admin info
				$update_stmt = $conn->prepare("UPDATE admins SET fullname = ?, email = ?, phone = ? WHERE admin_id = ?");
				$update_stmt->bind_param("sssi", $fullname, $email, $phone, $admin_id);

				if ($update_stmt->execute()) {
					// Update session variables
					$_SESSION['admin_name'] = $fullname;
					$_SESSION['admin_email'] = $email;

					// Log the action
					logActivity(null, $admin_id, 'profile_update', 'Admin updated profile information');

					$success_message = "Profile information updated successfully";

					// Refresh admin data
					$stmt->execute();
					$admin = $result->fetch_assoc();
				} else {
					$error_message = "Failed to update profile information";
				}
			}
		}
	}

	// Password update
	if (isset($_POST['update_password'])) {
		$current_password = $_POST['current_password'];
		$new_password = $_POST['new_password'];
		$confirm_password = $_POST['confirm_password'];

		// Validate inputs
		if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
			$error_message = "All password fields are required";
		} elseif ($new_password !== $confirm_password) {
			$error_message = "New passwords do not match";
		} elseif (strlen($new_password) < 8) {
			$error_message = "New password must be at least 8 characters long";
		} else {
			// Verify current password
			if (password_verify($current_password, $admin['password'])) {
				// Hash new password
				$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

				// Update password
				$update_stmt = $conn->prepare("UPDATE admins SET password = ? WHERE admin_id = ?");
				$update_stmt->bind_param("si", $hashed_password, $admin_id);

				if ($update_stmt->execute()) {
					// Log the action
					logActivity(null, $admin_id, 'password_change', 'Admin changed account password');

					$success_message = "Password updated successfully";
				} else {
					$error_message = "Failed to update password";
				}
			} else {
				$error_message = "Current password is incorrect";
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
	<title>Edit Account | Admin Panel</title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
	<?php include('includes/header.php'); ?>
	<?php include('includes/sidebar.php'); ?>

	<main class="main-content">
		<div class="container">
			<div class="page-header">
				<h1>Edit Account</h1>
				<p>
					Update your account information and password
				</p>
			</div>

			<?php if (!empty($success_message)): ?>
			<div class="alert alert-success">
				<i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
			</div>
			<?php endif; ?>

			<?php if (!empty($error_message)): ?>
			<div class="alert alert-danger">
				<i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
			</div>
			<?php endif; ?>

			<div class="row">
				<div class="col-lg-6">
					<div class="card">
						<div class="card-header">
							<h3>Personal Information</h3>
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
									<input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($admin['phone']); ?>" required>
								</div>

								<div class="form-group">
									<label for="role">Role</label>
									<input type="text" id="role" class="form-control" value="<?php echo ucfirst($admin['role']); ?>" readonly>
									<small class="form-text text-muted">Your role cannot be changed</small>
								</div>

								<div class="form-group">
									<button type="submit" name="update_info" class="btn btn-primary">
										<i class="fas fa-save"></i> Update Information
									</button>
								</div>
							</form>
						</div>
					</div>
				</div>

				<div class="col-lg-6">
					<div class="card">
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
									<button type="submit" name="update_password" class="btn btn-primary">
										<i class="fas fa-key"></i> Change Password
									</button>
								</div>
							</form>
						</div>
					</div>

					<div class="card mt-4">
						<div class="card-header">
							<h3>Account Information</h3>
						</div>
						<div class="card-body">
							<div class="account-info">
								<div class="info-item">
									<span class="label">Status:</span>
									<span class="value">
										<span class="badge bg-<?php echo ($admin['status'] == 'active') ? 'success' : 'danger'; ?>">
											<?php echo ucfirst($admin['status']); ?>
										</span>
									</span>
								</div>

								<div class="info-item">
									<span class="label">Last Login:</span>
									<span class="value">
										<?php echo $admin['last_login'] ? date('M d, Y H:i', strtotime($admin['last_login'])) : 'Never'; ?>
									</span>
								</div>

								<div class="info-item">
									<span class="label">Created:</span>
									<span class="value">
										<?php echo date('M d, Y', strtotime($admin['created_at'])); ?>
									</span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</main>

	<?php include('includes/footer.php'); ?>

	<script src="../assets/js/admin.js"></script>
</body>
</html>
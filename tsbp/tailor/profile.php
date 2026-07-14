<?php
session_start();
require_once('includes/config.php');
require_once('includes/tailor_functions.php');

// Check if tailor is logged in
if (!isset($_SESSION['tailor_id'])) {
	header("Location: ../admin/index.php");
	exit();
}

$tailor_id = $_SESSION['tailor_id'];

// Get tailor details
$tailor = getTailorDetails($conn, $tailor_id);

if (!$tailor) {
	$_SESSION['error'] = "Tailor profile not found";
	header("Location: dashboard.php");
	exit();
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
	$fullname = htmlspecialchars(trim($_POST['fullname']), ENT_QUOTES, 'UTF-8');
	$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
	$phone = htmlspecialchars(trim($_POST['phone']), ENT_QUOTES, 'UTF-8');
	$address = htmlspecialchars(trim($_POST['address']), ENT_QUOTES, 'UTF-8');
	$specialty = htmlspecialchars(trim($_POST['specialty']), ENT_QUOTES, 'UTF-8');
	
	// Validate inputs
	$errors = [];

	if (empty($fullname)) {
		$errors[] = "Full name is required";
	}

	if (empty($email)) {
		$errors[] = "Email is required";
	} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$errors[] = "Invalid email format";
	} else {
		// Check if email exists for other tailors
		$stmt = $conn->prepare("SELECT tailor_id FROM tailors WHERE email = ? AND tailor_id != ?");
		$stmt->bind_param("si", $email, $tailor_id);
		$stmt->execute();
		$result = $stmt->get_result();

		if ($result->num_rows > 0) {
			$errors[] = "Email already exists for another tailor";
		}
	}

	if (empty($phone)) {
		$errors[] = "Phone number is required";
	}

	// Process update if no errors
	if (empty($errors)) {
		$updateStmt = $conn->prepare("UPDATE tailors SET fullname = ?, email = ?, phone = ?, address = ?, specialty = ? WHERE tailor_id = ?");
		$updateStmt->bind_param("sssssi", $fullname, $email, $phone, $address, $specialty, $tailor_id);

		if ($updateStmt->execute()) {
			// Update session data
			$_SESSION['tailor_name'] = $fullname;
			$_SESSION['tailor_email'] = $email;

			// Log activity
			logTailorActivity($conn, $tailor_id, 'profile_update', 'Tailor updated profile information');

			$_SESSION['success'] = "Profile updated successfully";

			echo "<script>window.location.href = 'profile.php';</script>";
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
		if (!password_verify($current_password, $tailor['password'])) {
			$errors[] = "Current password is incorrect";
		}
	}

	// Process password change if no errors
	if (empty($errors)) {
		$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

		$updateStmt = $conn->prepare("UPDATE tailors SET password = ? WHERE tailor_id = ?");
		$updateStmt->bind_param("si", $hashed_password, $tailor_id);

		if ($updateStmt->execute()) {
			// Log activity
			logTailorActivity($conn, $tailor_id, 'password_change', 'Tailor changed password');

			$_SESSION['success'] = "Password changed successfully";

			echo "<script>window.location.href = 'profile.php';</script>";
			exit();
		} else {
			$errors[] = "Failed to change password. Please try again.";
		}
	}
}

// Get recent activity logs for this tailor
$recentLogs = getTailorActivityLogs($conn, $tailor_id, 5);
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>My Profile | Tailor Portal</title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<link rel="stylesheet" href="../assets/css/tailor.css">
</head>
<body>
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
									<input type="text" id="fullname" name="fullname" class="form-control" value="<?php echo htmlspecialchars($tailor['fullname']); ?>" required>
								</div>

								<div class="form-group">
									<label for="email">Email Address</label>
									<input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($tailor['email']); ?>" required>
								</div>

								<div class="form-group">
									<label for="phone">Phone Number</label>
									<input type="text" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($tailor['phone']); ?>" required>
								</div>
                                
                                <div class="form-group">
									<label for="address">Address</label>
									<textarea id="address" name="address" class="form-control" rows="3"><?php echo htmlspecialchars($tailor['address']); ?></textarea>
								</div>
                                
                                <div class="form-group">
									<label for="specialty">Specialty</label>
									<input type="text" id="specialty" name="specialty" class="form-control" value="<?php echo htmlspecialchars($tailor['specialty']); ?>">
									<small class="form-text text-muted">Your area of expertise (e.g., Wedding Gowns, Suits, Traditional Wear)</small>
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
							<div class="tailor-profile">
								<div class="tailor-avatar">
									<span class="avatar-text"><?php echo strtoupper(substr($tailor['fullname'], 0, 1)); ?></span>
								</div>

								<h2 class="tailor-name"><?php echo htmlspecialchars($tailor['fullname']); ?></h2>
								<span class="tailor-role badge bg-primary">Tailor</span>

								<div class="account-details mt-4">
									<div class="detail-item">
										<span class="label"><i class="fas fa-calendar"></i> Joined</span>
										<span class="value"><?php echo date('M d, Y', strtotime($tailor['created_at'])); ?></span>
									</div>

									<div class="detail-item">
										<span class="label"><i class="fas fa-clock"></i> Last Login</span>
										<span class="value">
											<?php echo $tailor['last_login'] ? date('M d, Y H:i', strtotime($tailor['last_login'])) : 'Never'; ?>
										</span>
									</div>

									<div class="detail-item">
										<span class="label"><i class="fas fa-toggle-on"></i> Status</span>
										<span class="value badge bg-<?php echo $tailor['status'] === 'active' ? 'success' : 'danger'; ?>">
											<?php echo ucfirst($tailor['status']); ?>
										</span>
									</div>
                                    
                                    <div class="detail-item">
										<span class="label"><i class="fas fa-cut"></i> Specialty</span>
										<span class="value">
											<?php echo $tailor['specialty'] ? htmlspecialchars($tailor['specialty']) : 'Not specified'; ?>
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
									<div class="activity-icon bg-<?php echo getTailorActivityIconClass($log['action']); ?>">
										<i class="fas fa-<?php echo getTailorActivityIcon($log['action']); ?>"></i>
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
                            <!--
							<div class="text-center mt-3">
								<a href="activity_logs.php" class="btn btn-outline">
									View All Activity
								</a>
							</div>
							-->
						</div>
					</div>

					<div class="card mt-4">
						<div class="card-header">
							<h3>Performance Stats</h3>
						</div>
						<div class="card-body">
							<div class="performance-stats">
								<div class="stat-item">
									<span class="stat-label">Orders Completed</span>
									<div class="progress">
										<?php 
										$stats = getTailorStats($conn, $tailor_id);
										$completion_rate = ($stats['total_assigned'] > 0) ? ($stats['completed'] / $stats['total_assigned']) * 100 : 0;
										?>
										<div class="progress-bar" style="width: <?php echo $completion_rate; ?>%">
											<?php echo $stats['completed']; ?>/<?php echo $stats['total_assigned']; ?>
										</div>
									</div>
								</div>
								
								<div class="stat-item">
									<span class="stat-label">On-time Delivery</span>
									<div class="progress">
										<?php 
										$on_time_rate = isset($stats['on_time_delivery']) ? $stats['on_time_delivery'] : 0;
										?>
										<div class="progress-bar bg-success" style="width: <?php echo $on_time_rate; ?>%">
											<?php echo round($on_time_rate); ?>%
										</div>
									</div>
								</div>
								
								<div class="stat-row">
									<div class="stat-col">
										<span class="stat-number"><?php echo isset($stats['avg_rating']) ? number_format($stats['avg_rating'], 1) : 'N/A'; ?></span>
										<span class="stat-text">Avg. Rating</span>
									</div>
									<div class="stat-col">
										<span class="stat-number"><?php echo isset($stats['current_workload']) ? $stats['current_workload'] : 0; ?></span>
										<span class="stat-text">Current Workload</span>
									</div>
								</div>
							</div>
							<!--
							<div class="text-center mt-3">
								<a href="performance.php" class="btn btn-outline">
									View Detailed Stats
								</a>
							</div>
							-->
						</div>
					</div>
				</div>
			</div>
		</div>
	</main>

	<?php include('includes/footer.php'); ?>

	<script src="../assets/js/tailor.js"></script>
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

			newPasswordInput.addEventListener('input', function() {
				if (confirmPasswordInput.value !== '' && newPasswordInput.value !== confirmPasswordInput.value) {
					confirmPasswordInput.setCustomValidity("Passwords don't match");
				} else {
					confirmPasswordInput.setCustomValidity('');
				}
			});
		});
	</script>
</body>
</html>
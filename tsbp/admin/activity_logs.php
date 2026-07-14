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

// Set default filters
$action = isset($_GET['action']) ? $_GET['action'] : '';
$admin = isset($_GET['admin']) ? (int)$_GET['admin'] : 0;
$user = isset($_GET['user']) ? (int)$_GET['user'] : 0;
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Get activity logs with pagination
$logs = getActivityLogs($conn, $action, $admin, $user, $date_from, $date_to, $search, $limit, $offset);
$totalLogs = getTotalActivityLogs($conn, $action, $admin, $user, $date_from, $date_to, $search);
$totalPages = ceil($totalLogs / $limit);

// Get admins for filter dropdown
$admins = getAllAdmins($conn);

// Get common activity types for filter dropdown
$activityTypes = getCommonActivityTypes($conn);
?>
<?php include('includes/header.php'); ?>
<?php include('includes/sidebar.php'); ?>

<main class="main-content">
	<div class="container">
		<div class="page-header">
			<h1>Activity Logs</h1>
			<p>
				Monitor system activities and user actions
			</p>
		</div>

		<div class="card">
			<div class="card-body">
				<div class="filters">
					<form action="" method="GET" class="filter-form">
						<div class="filter-row">
							<div class="form-group">
								<label for="action">Action Type</label>
								<select name="action" id="action" class="form-control">
									<option value="">All Actions</option>
									<?php foreach ($activityTypes as $type): ?>
									<option value="<?php echo htmlspecialchars($type); ?>" <?php if ($action == $type) echo 'selected'; ?>>
										<?php echo ucfirst($type); ?>
									</option>
									<?php endforeach; ?>
								</select>
							</div>

							<div class="form-group">
								<label for="admin">Admin</label>
								<select name="admin" id="admin" class="form-control">
									<option value="">All Admins</option>
									<?php foreach ($admins as $adminUser): ?>
									<option value="<?php echo $adminUser['admin_id']; ?>" <?php if ($admin == $adminUser['admin_id']) echo 'selected'; ?>>
										<?php echo htmlspecialchars($adminUser['fullname']); ?>
									</option>
									<?php endforeach; ?>
								</select>
							</div>

							<div class="form-group">
								<label for="user">User ID</label>
								<input type="number" name="user" id="user" class="form-control" placeholder="User ID" value="<?php echo $user ? $user : ''; ?>">
							</div>
						</div>

						<div class="filter-row">
							<div class="form-group">
								<label for="date_from">Date From</label>
								<input type="date" name="date_from" id="date_from" class="form-control" value="<?php echo $date_from; ?>">
							</div>

							<div class="form-group">
								<label for="date_to">Date To</label>
								<input type="date" name="date_to" id="date_to" class="form-control" value="<?php echo $date_to; ?>">
							</div>

							<div class="form-group search-group">
								<label for="search">Search</label>
								<div class="search-input-group">
									<input type="text" name="search" id="search" class="form-control" placeholder="Search in description" value="<?php echo htmlspecialchars($search); ?>">
									<button type="submit" class="btn btn-primary">
										<i class="fas fa-search"></i>
									</button>
								</div>
							</div>
						</div>

						<div class="filter-actions">
							<button type="submit" class="btn btn-primary">Apply Filters</button>
							<a href="activity_logs.php" class="btn btn-outline">Reset</a>
						</div>
					</form>
				</div>

				<div class="table-responsive">
					<table class="table table-striped">
						<thead>
							<tr>
								<th>Log ID</th>
								<th>Action</th>
								<th>Description</th>
								<th>User</th>
								<th>Admin</th>
								<th>IP Address</th>
								<th>Timestamp</th>
							</tr>
						</thead>
						<tbody>
							<?php if (empty($logs)): ?>
							<tr>
								<td colspan="7" class="text-center">No activity logs found</td>
							</tr>
							<?php else : ?>
							<?php foreach ($logs as $log): ?>
							<tr>
								<td><?php echo $log['log_id']; ?></td>
								<td>
									<span class="badge bg-<?php echo getActivityIconClass($log['action']); ?>">
										<?php echo ucfirst($log['action']); ?>
									</span>
								</td>
								<td><?php echo htmlspecialchars($log['description']); ?></td>
								<td>
									<?php if ($log['user_id']): ?>
									<a href="view_user.php?id=<?php echo $log['user_id']; ?>" class="user-link">
										<?php echo getUserName($conn, $log['user_id']); ?>
									</a>
									<?php else : ?>
									<span class="text-muted">-</span>
									<?php endif; ?>
								</td>
								<td>
									<?php if ($log['admin_id']): ?>
									<?php echo getAdminName($conn, $log['admin_id']); ?>
									<?php else : ?>
									<span class="text-muted">-</span>
									<?php endif; ?>
								</td>
								<td><?php echo htmlspecialchars($log['ip_address']); ?></td>
								<td><?php echo date('M d, Y H:i:s', strtotime($log['timestamp'])); ?></td>
							</tr>
							<?php endforeach; ?>
							<?php endif; ?>
						</tbody>
					</table>
				</div>

				<?php if ($totalPages > 1): ?>
				<div class="pagination">
					<?php if ($page > 1): ?>
					<a href="?page=<?php echo ($page - 1); ?>&action=<?php echo urlencode($action); ?>&admin=<?php echo $admin; ?>&user=<?php echo $user; ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&search=<?php echo urlencode($search); ?>" class="page-link">Previous</a>
					<?php endif; ?>

					<?php
					// Determine range of page numbers to display
					$range = 3;
					$startPage = max(1, $page - $range);
					$endPage = min($totalPages, $page + $range);

					// Always show first page
					if ($startPage > 1) {
						echo '<a href="?page=1&action=' . urlencode($action) . '&admin=' . $admin . '&user=' . $user . '&date_from=' . urlencode($date_from) . '&date_to=' . urlencode($date_to) . '&search=' . urlencode($search) . '" class="page-link">1</a>';
						if ($startPage > 2) {
							echo '<span class="page-separator">...</span>';
						}
					}

					// Display page numbers in range
					for ($i = $startPage; $i <= $endPage; $i++) {
						echo '<a href="?page=' . $i . '&action=' . urlencode($action) . '&admin=' . $admin . '&user=' . $user . '&date_from=' . urlencode($date_from) . '&date_to=' . urlencode($date_to) . '&search=' . urlencode($search) . '" class="page-link ' . ($i == $page ? 'active' : '') . '">' . $i . '</a>';
					}

					// Always show last page
					if ($endPage < $totalPages) {
						if ($endPage < $totalPages - 1) {
							echo '<span class="page-separator">...</span>';
						}
						echo '<a href="?page=' . $totalPages . '&action=' . urlencode($action) . '&admin=' . $admin . '&user=' . $user . '&date_from=' . urlencode($date_from) . '&date_to=' . urlencode($date_to) . '&search=' . urlencode($search) . '" class="page-link">' . $totalPages . '</a>';
					}
					?>

					<?php if ($page < $totalPages): ?>
					<a href="?page=<?php echo ($page + 1); ?>&action=<?php echo urlencode($action); ?>&admin=<?php echo $admin; ?>&user=<?php echo $user; ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&search=<?php echo urlencode($search); ?>" class="page-link">Next</a>
					<?php endif; ?>
				</div>
				<?php endif; ?>

				<div class="export-actions mt-3">
					<a href="export_logs.php?action=<?php echo urlencode($action); ?>&admin=<?php echo $admin; ?>&user=<?php echo $user; ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&search=<?php echo urlencode($search); ?>" class="btn btn-secondary">
						<i class="fas fa-file-export"></i> Export to CSV
					</a>
				</div>
			</div>
		</div>
	</div>
</main>

<?php include('includes/footer.php'); ?>

<script>
	// Date range validation
	document.addEventListener('DOMContentLoaded', function() {
		const dateFrom = document.getElementById('date_from');
		const dateTo = document.getElementById('date_to');

		dateFrom.addEventListener('change', function() {
			if (dateTo.value && dateFrom.value > dateTo.value) {
				dateTo.value = dateFrom.value;
			}
		});

		dateTo.addEventListener('change',
			function() {
				if (dateFrom.value && dateTo.value < dateFrom.value) {
					dateFrom.value = dateTo.value;
				}
			});
	});
</script>
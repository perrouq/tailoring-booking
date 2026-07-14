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
$status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get orders with pagination
$query = "SELECT o.*, c.first_name, c.last_name, c.email, c.phone,
          ota.tailor_id, ota.status AS assignment_status, ota.due_date, t.fullname AS tailor_name
          FROM orders o
          LEFT JOIN customers c ON o.user_id = c.id
          LEFT JOIN order_tailor_assignments ota ON o.id = ota.order_id
          LEFT JOIN tailors t ON ota.tailor_id = t.tailor_id
          WHERE 1=1";

// Add filters
if (!empty($status)) {
	$query .= " AND o.payment_status = '$status'";
}

if (!empty($search)) {
	$search = $conn->real_escape_string($search);
	$query .= " AND (c.first_name LIKE '%$search%' OR c.last_name LIKE '%$search%'
                OR c.email LIKE '%$search%' OR o.payment_reference LIKE '%$search%'
                OR o.id LIKE '%$search%' OR t.fullname LIKE '%$search%')";
}

// Get total count for pagination
$countQuery = str_replace("o.*, c.first_name, c.last_name, c.email, c.phone,
          ota.tailor_id, ota.status AS assignment_status, ota.due_date, t.fullname AS tailor_name", "COUNT(*) as total", $query);
$countResult = $conn->query($countQuery);
$totalOrders = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalOrders / $limit);

// Add ordering and pagination
$query .= " ORDER BY o.created_at DESC LIMIT $offset, $limit";
$result = $conn->query($query);

// Handle order status toggle if necessary
if (isset($_GET['toggle_status']) && isset($_GET['order_id'])) {
	$orderId = (int)$_GET['order_id'];
	$newStatus = $_GET['toggle_status'];

	if (in_array($newStatus, ['pending', 'completed', 'failed'])) {
		$updateQuery = "UPDATE orders SET payment_status = '$newStatus' WHERE id = $orderId";
		if ($conn->query($updateQuery)) {
			$_SESSION['success'] = "Order status updated successfully";
		} else {
			$_SESSION['error'] = "Failed to update order status";
		}
	}

	// Redirect to remove the toggle parameters from URL
	header("Location: orders.php?status=$status&search=" . urlencode($search) . "&page=$page");
	exit();
}

// Get all tailors for dropdown
$tailorsQuery = "SELECT tailor_id, fullname, specialty FROM tailors WHERE status = 'active' ORDER BY fullname";
$tailorsResult = $conn->query($tailorsQuery);
$tailors = [];
while ($tailor = $tailorsResult->fetch_assoc()) {
	$tailors[] = $tailor;
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="main-content">
	<div class="container">
		<div class="page-header">
			<h1>Orders Management</h1>
			<div class="header-actions">
				<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addOrderModal">
					<i class="fas fa-plus me-2"></i>Add New Order
				</button>
			</div>
		</div>
		<div class="card">
			<div class="card-body">
				<div class="filters">
					<form action="" method="GET" class="filter-form">
						<div class="row">
							<div class="col-md-3 form-group">
								<select name="status" class="form-select">
									<option value="">All Status</option>
									<option value="pending" <?php if ($status == 'pending') echo 'selected'; ?>>Pending</option>
									<option value="completed" <?php if ($status == 'completed') echo 'selected'; ?>>Completed</option>
									<option value="failed" <?php if ($status == 'failed') echo 'selected'; ?>>Failed</option>
								</select>
							</div>

							<div class="col-md-6 form-group search-group">
								<div class="input-group">
									<input type="text" name="search" class="form-control" placeholder="Search by name, email, reference number..." value="<?php echo htmlspecialchars($search); ?>">
									<button type="submit" class="btn btn-primary">
										<i class="fas fa-search"></i>
									</button>
								</div>
							</div>
						</div>
					</form>
				</div>

				<div class="table-responsive">
					<table class="table table-striped">
						<thead>
							<tr>
								<th>ID</th>
								<th>Customer</th>
								<th>Amount</th>
								<th>Status</th>
								<th>Assigned Tailor</th>
								<th>Payment Ref</th>
								<th>Date</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
							<?php
							if ($result && $result->num_rows > 0) {
								while ($row = $result->fetch_assoc()):
								?>
								<tr>
									<td>#<?php echo $row['id']; ?></td>
									<td>
										<?php if (!empty($row['first_name']) || !empty($row['last_name'])): ?>
										<?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?><br>
										<small><?php echo htmlspecialchars($row['email']); ?></small>
										<?php else : ?>
										<span class="text-muted">Guest Order</span>
										<?php endif; ?>
									</td>
									<td>₦<?php echo number_format($row['total_amount'], 2); ?></td>
									<td>
										<span class="badge <?php
											echo $row['payment_status'] == 'completed' ? 'bg-success' :
											($row['payment_status'] == 'pending' ? 'bg-warning' : 'bg-danger');
											?>">
											<?php echo ucfirst($row['payment_status']); ?>
										</span>
									</td>
									<td>
										<?php if (!empty($row['tailor_id'])): ?>
										<span class="badge bg-info"><?php echo htmlspecialchars($row['tailor_name']); ?></span>
										<br><small class="text-muted">
											<?php
											//$assignmentStatus = ucwords(str_replace('_', ' ', $row['assignment_status']));
											//echo $assignmentStatus;
											//if (!empty($row['due_date'])) {
												//echo ' - Due: ' . date('M d, Y', strtotime($row['due_date']));
											//}
											?>
										</small>
										<?php else : ?>
										<span class="badge bg-secondary">Not Assigned</span>
										<?php endif; ?>
									</td>
									<td><?php echo !empty($row['payment_reference']) ? htmlspecialchars($row['payment_reference']) : '-'; ?></td>
									<td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
									<td class="actions">
										<div class="dropdown">
											<button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="actionDropdown<?php echo $row['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
												Actions
											</button>

											<ul class="dropdown-menu" aria-labelledby="actionDropdown<?php echo $row['id']; ?>">
												<li>
													<a class="dropdown-item" href="view_order.php?id=<?php echo $row['id']; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $page; ?>">
														<i class="fas fa-eye text-info"></i> View Details
													</a>
												</li>
												<li>
													<a class="dropdown-item edit-order-btn" href="#" data-bs-toggle="modal" data-bs-target="#editOrderModal"
														data-id="<?php echo $row['id']; ?>"
														data-user-id="<?php echo $row['user_id']; ?>"
														data-amount="<?php echo $row['total_amount']; ?>"
														data-status="<?php echo $row['payment_status']; ?>"
														data-reference="<?php echo htmlspecialchars($row['payment_reference']); ?>"
														data-address="<?php echo htmlspecialchars($row['delivery_address']); ?>"
														data-city="<?php echo htmlspecialchars($row['delivery_city']); ?>"
														data-state="<?php echo htmlspecialchars($row['delivery_state']); ?>"
														data-zip="<?php echo htmlspecialchars($row['delivery_zip']); ?>"
														data-instructions="<?php echo htmlspecialchars($row['delivery_instructions']); ?>">
														<i class="fas fa-edit text-primary"></i> Edit Order
													</a>
												</li>

												<li>
													<a class="dropdown-item assign-tailor-btn" href="#" data-bs-toggle="modal" data-bs-target="#assignTailorModal"
														data-order-id="<?php echo $row['id']; ?>"
														data-tailor-id="<?php echo $row['tailor_id'] ?? ''; ?>"
														data-assignment-status="<?php echo $row['assignment_status'] ?? ''; ?>"
														data-due-date="<?php echo !empty($row['due_date']) ? date('Y-m-d', strtotime($row['due_date'])) : ''; ?>">
														<i class="fas fa-user-tag text-primary"></i> Assign Tailor
													</a>
												</li>

												<?php if (!empty($row['tailor_id'])): ?>
												<li>
													<a class="dropdown-item" href="order_chat.php?order_id=<?php echo $row['id']; ?>">
														<i class="fas fa-comments text-primary"></i> View Chat
													</a>
												</li>
												<?php endif; ?>

												<?php if ($row['payment_status'] != 'completed'): ?>
												<li>
													<a class="dropdown-item" href="?toggle_status=completed&order_id=<?php echo $row['id']; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $page; ?>">
														<i class="fas fa-check-circle text-success"></i> Mark as Completed
													</a>
												</li>
												<?php endif; ?>

												<?php if ($row['payment_status'] != 'pending'): ?>
												<li>
													<a class="dropdown-item" href="?toggle_status=pending&order_id=<?php echo $row['id']; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $page; ?>">
														<i class="fas fa-clock text-warning"></i> Mark as Pending
													</a>
												</li>
												<?php endif; ?>

												<?php if ($row['payment_status'] != 'failed'): ?>
												<li>
													<a class="dropdown-item" href="?toggle_status=failed&order_id=<?php echo $row['id']; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $page; ?>">
														<i class="fas fa-times-circle text-danger"></i> Mark as Failed
													</a>
												</li>
												<?php endif; ?>

												<li><hr class="dropdown-divider"></li>
												<li>
													<a class="dropdown-item text-danger" href="includes/process_orders.php?action=delete&id=<?php echo $row['id']; ?>"
														onclick="return confirm('Are you sure you want to delete this order? This action cannot be undone.');">
														<i class="fas fa-trash"></i> Delete Order
													</a>
												</li>
											</ul>
										</div>
									</td>
								</tr>
								<?php
								endwhile;
							} else {
								echo '<tr><td colspan="8" class="text-center">No orders found</td></tr>';
							}
							?>
						</tbody>
					</table>
				</div>

				<!-- Pagination -->
				<?php if ($totalPages > 1): ?>
				<div class="d-flex justify-content-center mt-4">
					<nav aria-label="Orders page navigation">
						<ul class="pagination">
							<?php if ($page > 1): ?>
							<li class="page-item">
								<a class="page-link" href="?page=<?php echo $page - 1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>" aria-label="Previous">
									<span aria-hidden="true">&laquo;</span>
								</a>
							</li>
							<?php endif; ?>

							<?php
							// Show page numbers
							for ($i = 1; $i <= $totalPages; $i++) {
								echo "<li class='page-item " . ($i == $page ? 'active' : '') . "'>";
								echo "<a class='page-link' href='?page=$i&status=$status&search=" . urlencode($search) . "'>$i</a>";
								echo "</li>";
							}
							?>

							<?php if ($page < $totalPages): ?>
							<li class="page-item">
								<a class="page-link" href="?page=<?php echo $page + 1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>" aria-label="Next">
									<span aria-hidden="true">&raquo;</span>
								</a>
							</li>
							<?php endif; ?>
						</ul>
					</nav>
				</div>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<!-- Add Order Modal -->
	<div class="modal fade" id="addOrderModal" tabindex="-1" aria-labelledby="addOrderModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="addOrderModalLabel">Add New Order</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<form action="includes/process_orders.php" method="post">
					<input type="hidden" name="action" value="add">
					<div class="modal-body">
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="user_id" class="form-label">Customer</label>
								<select class="form-select" id="user_id" name="user_id">
									<option value="">-- Guest Order --</option>
									<?php
									$customers_query = "SELECT id, first_name, last_name, email FROM customers ORDER BY first_name";
									$customers_result = $conn->query($customers_query);
									while ($customer = $customers_result->fetch_assoc()) {
										echo '<option value="' . $customer['id'] . '">' .
										htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) .
										' (' . htmlspecialchars($customer['email']) . ')</option>';
									}
									?>
								</select>
							</div>
							<div class="col-md-6">
								<label for="total_amount" class="form-label">Total Amount</label>
								<div class="input-group">
									<span class="input-group-text">₦</span>
									<input type="number" step="0.01" class="form-control" id="total_amount" name="total_amount" required>
								</div>
							</div>
						</div>

						<div class="row mb-3">
							<div class="col-md-6">
								<label for="payment_status" class="form-label">Payment Status</label>
								<select class="form-select" id="payment_status" name="payment_status" required>
									<option value="pending">Pending</option>
									<option value="completed">Completed</option>
									<option value="failed">Failed</option>
								</select>
							</div>
							<div class="col-md-6">
								<label for="payment_reference" class="form-label">Payment Reference</label>
								<input type="text" class="form-control" id="payment_reference" name="payment_reference">
							</div>
						</div>

						<h6 class="mt-4 mb-3">Delivery Information</h6>

						<div class="mb-3">
							<label for="delivery_address" class="form-label">Address</label>
							<textarea class="form-control" id="delivery_address" name="delivery_address" rows="2"></textarea>
						</div>

						<div class="row mb-3">
							<div class="col-md-4">
								<label for="delivery_city" class="form-label">City</label>
								<input type="text" class="form-control" id="delivery_city" name="delivery_city">
							</div>
							<div class="col-md-4">
								<label for="delivery_state" class="form-label">State</label>
								<input type="text" class="form-control" id="delivery_state" name="delivery_state">
							</div>
							<div class="col-md-4">
								<label for="delivery_zip" class="form-label">Zip Code</label>
								<input type="text" class="form-control" id="delivery_zip" name="delivery_zip">
							</div>
						</div>

						<div class="mb-3">
							<label for="delivery_instructions" class="form-label">Delivery Instructions</label>
							<textarea class="form-control" id="delivery_instructions" name="delivery_instructions" rows="2"></textarea>
						</div>

						<div class="alert alert-info">
							<small><i class="fas fa-info-circle me-2"></i>After creating the order, you can add products to it from the order details view.</small>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
						<button type="submit" class="btn btn-primary">Add Order</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<!-- Edit Order Modal -->
	<div class="modal fade" id="editOrderModal" tabindex="-1" aria-labelledby="editOrderModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="editOrderModalLabel">Edit Order</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<form action="includes/process_orders.php" method="post">
					<input type="hidden" name="action" value="edit">
					<input type="hidden" id="edit_order_id" name="id">
					<div class="modal-body">
						<div class="row mb-3">
							<div class="col-md-6">
								<label for="edit_user_id" class="form-label">Customer</label>
								<select class="form-select" id="edit_user_id" name="user_id">
									<option value="">-- Guest Order --</option>
									<?php
									// Reset the pointer to the beginning
									$customers_result->data_seek(0);
									while ($customer = $customers_result->fetch_assoc()) {
										echo '<option value="' . $customer['id'] . '">' .
										htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) .
										' (' . htmlspecialchars($customer['email']) . ')</option>';
									}
									?>
								</select>
							</div>
							<div class="col-md-6">
								<label for="edit_total_amount" class="form-label">Total Amount</label>
								<div class="input-group">
									<span class="input-group-text">₦</span>
									<input type="number" step="0.01" class="form-control" id="edit_total_amount" name="total_amount" required>
								</div>
							</div>
						</div>

						<div class="row mb-3">
							<div class="col-md-6">
								<label for="edit_payment_status" class="form-label">Payment Status</label>
								<select class="form-select" id="edit_payment_status" name="payment_status" required>
									<option value="pending">Pending</option>
									<option value="completed">Completed</option>
									<option value="failed">Failed</option>
								</select>
							</div>
							<div class="col-md-6">
								<label for="edit_payment_reference" class="form-label">Payment Reference</label>
								<input type="text" class="form-control" id="edit_payment_reference" name="payment_reference">
							</div>
						</div>

						<hr>
						<h6 class="mt-4 mb-3">Delivery Information</h6>

						<div class="mb-3">
							<label for="edit_delivery_address" class="form-label">Address</label>
							<textarea class="form-control" id="edit_delivery_address" name="delivery_address" rows="2"></textarea>
						</div>

						<div class="row mb-3">
							<div class="col-md-4">
								<label for="edit_delivery_city" class="form-label">City</label>
								<input type="text" class="form-control" id="edit_delivery_city" name="delivery_city">
							</div>
							<div class="col-md-4">
								<label for="edit_delivery_state" class="form-label">State</label>
								<input type="text" class="form-control" id="edit_delivery_state" name="delivery_state">
							</div>
							<div class="col-md-4">
								<label for="edit_delivery_zip" class="form-label">Zip Code</label>
								<input type="text" class="form-control" id="edit_delivery_zip" name="delivery_zip">
							</div>
						</div>

						<div class="mb-3">
							<label for="edit_delivery_instructions" class="form-label">Delivery Instructions</label>
							<textarea class="form-control" id="edit_delivery_instructions" name="delivery_instructions" rows="2"></textarea>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
						<button type="submit" class="btn btn-primary">Save Changes</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<!-- Assign Tailor Modal -->
	<div class="modal fade" id="assignTailorModal" tabindex="-1" aria-labelledby="assignTailorModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="assignTailorModalLabel">Assign Tailor to Order</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<form action="includes/process_orders.php" method="post">
					<input type="hidden" name="action" value="assign_tailor">
					<input type="hidden" id="assign_order_id" name="order_id">
					<div class="modal-body">
						<div class="row mb-3">
							<div class="col-md-12">
								<label for="assign_tailor_id" class="form-label">Select Tailor</label>
								<select class="form-select" id="assign_tailor_id" name="tailor_id" required>
									<option value="">-- Select Tailor --</option>
									<?php foreach ($tailors as $tailor): ?>
									<option value="<?php echo $tailor['tailor_id']; ?>">
										<?php echo htmlspecialchars($tailor['fullname']); ?>
										<?php if (!empty($tailor['specialty'])): ?>
										(<?php echo htmlspecialchars($tailor['specialty']); ?>)
										<?php endif; ?>
									</option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>

						<div class="row mb-3">
							<div class="col-md-6">
								<label for="assign_status" class="form-label">Assignment Status</label>
								<select class="form-select" id="assign_status" name="status" required>
									<option value="assigned">Assigned</option>
									<option value="in_progress">In Progress</option>
									<option value="completed">Completed</option>
									<option value="cancelled">Cancelled</option>
								</select>
							</div>
							<div class="col-md-6">
								<label for="assign_due_date" class="form-label">Due Date</label>
								<input type="date" class="form-control" id="assign_due_date" name="due_date">
							</div>
						</div>

						<div class="mb-3">
							<label for="assignment_notes" class="form-label">Assignment Notes</label>
							<textarea class="form-control" id="assignment_notes" name="notes" rows="3" placeholder="Additional instructions for the tailor..."></textarea>
						</div>

						<div class="form-check mb-3">
							<input class="form-check-input" type="checkbox" id="assign_notify_tailor" name="notify_tailor" value="1">
							<label class="form-check-label" for="assign_notify_tailor">
								Notify tailor about this assignment
							</label>
						</div>

						<div class="alert alert-info">
							<small><i class="fas fa-info-circle me-2"></i>The tailor will be able to communicate with you and the customer about this order.</small>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
						<button type="submit" class="btn btn-primary">Assign Tailor</button>
					</div>
				</form>
			</div>
		</div>
	</div>

</main>

<script>
	document.addEventListener('DOMContentLoaded', function() {
		// Edit order modal functionality
		var editButtons = document.querySelectorAll('.edit-order-btn');
		editButtons.forEach(function(button) {
			button.addEventListener('click', function() {
				var id = this.getAttribute('data-id');
				var userId = this.getAttribute('data-user-id');
				var amount = this.getAttribute('data-amount');
				var status = this.getAttribute('data-status');
				var reference = this.getAttribute('data-reference');
				var address = this.getAttribute('data-address');
				var city = this.getAttribute('data-city');
				var state = this.getAttribute('data-state');
				var zip = this.getAttribute('data-zip');
				var instructions = this.getAttribute('data-instructions');
				var tailorId = this.getAttribute('data-tailor-id');
				var assignmentStatus = this.getAttribute('data-assignment-status');
				var dueDate = this.getAttribute('data-due-date');

				document.getElementById('edit_order_id').value = id;
				document.getElementById('edit_user_id').value = userId;
				document.getElementById('edit_total_amount').value = amount;
				document.getElementById('edit_payment_status').value = status;
				document.getElementById('edit_payment_reference').value = reference || '';
				document.getElementById('edit_delivery_address').value = address || '';
				document.getElementById('edit_delivery_city').value = city || '';
				document.getElementById('edit_delivery_state').value = state || '';
				document.getElementById('edit_delivery_zip').value = zip || '';
				document.getElementById('edit_delivery_instructions').value = instructions || '';

				// Set tailor assignment fields if available
				if (tailorId) {
					document.getElementById('edit_tailor_id').value = tailorId;
				}
				if (assignmentStatus) {
					document.getElementById('edit_assignment_status').value = assignmentStatus;
				}
				if (dueDate) {
					document.getElementById('edit_due_date').value = dueDate;
				}

				document.getElementById('editOrderModalLabel').textContent = 'Edit Order #' + id;
			});
		});

		// Assign tailor modal functionality
		var assignTailorButtons = document.querySelectorAll('.assign-tailor-btn');
		assignTailorButtons.forEach(function(button) {
			button.addEventListener('click',
				function() {
					var orderId = this.getAttribute('data-order-id');
					var tailorId = this.getAttribute('data-tailor-id');
					var assignmentStatus = this.getAttribute('data-assignment-status');
					var dueDate = this.getAttribute('data-due-date');

					document.getElementById('assign_order_id').value = orderId;
					document.getElementById('assignTailorModalLabel').textContent = 'Assign Tailor to Order #' + orderId;

					// Set values if available (for editing existing assignments)
					if (tailorId) {
						document.getElementById('assign_tailor_id').value = tailorId;
					}
					if (assignmentStatus) {
						document.getElementById('assign_status').value = assignmentStatus;
					}
					if (dueDate) {
						document.getElementById('assign_due_date').value = dueDate;
					}
				});
		});
	});
</script>

<?php include 'includes/footer.php'; ?>
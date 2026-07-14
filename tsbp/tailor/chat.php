<?php
session_start();
require_once('includes/config.php');
require_once('includes/tailor_functions.php');

// Check if tailor is logged in
if (!isset($_SESSION['tailor_id'])) {
	header("Location: ../admin/index.php");
	exit();
}

// Get order ID from URL parameter
$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$tailorId = $_SESSION['tailor_id'];

// Validate that this order is assigned to this tailor
$query = "SELECT o.*, c.first_name, c.last_name, c.email, c.phone,
          ota.assignment_id, ota.status AS assignment_status, ota.due_date, ota.notes
          FROM orders o
          LEFT JOIN customers c ON o.user_id = c.id
          INNER JOIN order_tailor_assignments ota ON o.id = ota.order_id
          WHERE o.id = $orderId AND ota.tailor_id = $tailorId";

$result = $conn->query($query);

if (!$result || $result->num_rows == 0) {
	$_SESSION['error'] = "You do not have access to this order";
	header("Location: tailor_orders.php");
	exit();
}

$order = $result->fetch_assoc();

// Get order items with color information
$itemsQuery = "SELECT oi.*, 
                      p.name as product_name, 
                      p.image as product_image,
                      pc.color_name,
                      pc.color_code
              FROM order_items oi
              JOIN products p ON oi.product_id = p.id
              LEFT JOIN product_colors pc ON oi.color_id = pc.id
              WHERE oi.order_id = $orderId";
$itemsResult = $conn->query($itemsQuery);

// Mark tailor's messages as read
$updateQuery = "UPDATE chat_messages
               SET read_status = 1
               WHERE order_id = $orderId
               AND sender_type != 'tailor'";
$conn->query($updateQuery);

// Get chat history
$chatQuery = "SELECT cm.*,
             CASE
                 WHEN cm.sender_type = 'customer' THEN CONCAT(c.first_name, ' ', c.last_name)
                 WHEN cm.sender_type = 'tailor' THEN t.fullname
                 WHEN cm.sender_type = 'admin' THEN a.fullname
             END as sender_name
             FROM chat_messages cm
             LEFT JOIN customers c ON cm.sender_type = 'customer' AND cm.sender_id = c.id
             LEFT JOIN tailors t ON cm.sender_type = 'tailor' AND cm.sender_id = t.tailor_id
             LEFT JOIN admins a ON cm.sender_type = 'admin' AND cm.sender_id = a.admin_id
             WHERE cm.order_id = $orderId
             ORDER BY cm.created_at ASC";
$chatResult = $conn->query($chatQuery);

// Function to format date
function formatMessageDate($dateString) {
	$messageDate = new DateTime($dateString);
	$now = new DateTime();
	$diff = $now->diff($messageDate);

	if ($diff->days == 0) {
		return $messageDate->format('h:i A');
	} elseif ($diff->days == 1) {
		return 'Yesterday at ' . $messageDate->format('h:i A');
	} else {
		return $messageDate->format('M d, Y, h:i A');
	}
}

// Send new message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
	$message = trim($_POST['message']);
	$attachmentPath = null;

	if (!empty($message) || isset($_FILES['attachment']['name']) && !empty($_FILES['attachment']['name'])) {

		if (isset($_FILES['attachment']['name']) && !empty($_FILES['attachment']['name'])) {
			$uploadDir = '../uploads/chat/';

			if (!file_exists($uploadDir)) {
				mkdir($uploadDir, 0777, true);
			}

			$fileName = time() . '_' . basename($_FILES['attachment']['name']);
			$targetFilePath = $uploadDir . $fileName;
			$fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

			$allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'pdf', 'doc', 'docx');
			if (in_array(strtolower($fileType), $allowTypes)) {
				if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetFilePath)) {
					$attachmentPath = $fileName;
				}
			}
		}

		$insertQuery = "INSERT INTO chat_messages (order_id, sender_type, sender_id, message, attachment, created_at)
                      VALUES ($orderId, 'tailor', $tailorId, ?, ?, NOW())";
		$stmt = $conn->prepare($insertQuery);
		$stmt->bind_param("ss", $message, $attachmentPath);
		$stmt->execute();

		header("Location: chat.php?order_id=$orderId");
		exit();
	}
}

include('includes/header.php');
include('includes/sidebar.php');
?>

<style>
	.chat-messages {
		display: flex;
		flex-direction: column;
	}

	.chat-container {
		display: flex;
		flex-direction: column;
		height: 500px;
		overflow: hidden;
		border-radius: 12px;
	}

	.chat-messages {
		flex: 1;
		overflow-y: auto;
		padding: 20px;
		background-color: #f8f9fa;
	}

	.date-separator {
		text-align: center;
		margin: 20px 0;
		position: relative;
	}

	.date-separator span {
		background-color: #fff;
		padding: 5px 15px;
		font-size: 12px;
		color: #6c757d;
		position: relative;
		z-index: 1;
		border-radius: 15px;
		box-shadow: 0 1px 3px rgba(0,0,0,0.1);
	}

	.date-separator:before {
		content: "";
		position: absolute;
		top: 50%;
		left: 0;
		right: 0;
		height: 1px;
		background-color: #e9ecef;
		z-index: 0;
	}

	.message {
		margin-bottom: 15px;
		display: flex;
		flex-direction: column;
		max-width: 70%;
		animation: fadeIn 0.3s ease-in;
	}

	@keyframes fadeIn {
		from { opacity: 0; transform: translateY(10px); }
		to { opacity: 1; transform: translateY(0); }
	}

	.message-received {
		align-self: flex-start;
	}

	.message-sent {
		align-self: flex-end;
	}

	.message-content {
		padding: 12px 16px;
		border-radius: 18px;
		position: relative;
		word-wrap: break-word;
		box-shadow: 0 1px 2px rgba(0,0,0,0.1);
	}

	.message-sent .message-content {
		background-color: #0d6efd;
		color: white;
		border-bottom-right-radius: 5px;
	}

	.message-received .message-content {
		background-color: #ffffff;
		color: #212529;
		border-bottom-left-radius: 5px;
	}

	.message-text {
		word-break: break-word;
		line-height: 1.4;
	}

	.message-meta {
		display: flex;
		font-size: 11px;
		margin-top: 5px;
		opacity: 0.7;
	}

	.message-sent .message-meta {
		justify-content: flex-end;
		color: #6c757d;
	}

	.message-received .message-meta {
		color: #6c757d;
	}

	.message-time {
		margin-right: 8px;
	}

	.message-sender {
		font-weight: 600;
	}

	.message-attachment {
		margin-top: 8px;
		width: 100%;
		max-width: 250px;
	}

	.message-attachment img {
		max-width: 100%;
		border-radius: 10px;
		display: block;
		height: auto;
	}

	.file-attachment {
		display: inline-flex;
		align-items: center;
		padding: 8px 12px;
		background-color: rgba(255,255,255,0.2);
		border-radius: 8px;
		color: inherit;
		text-decoration: none;
		transition: background-color 0.2s;
	}

	.message-sent .file-attachment {
		background-color: rgba(255,255,255,0.2);
	}

	.message-received .file-attachment {
		background-color: #f8f9fa;
	}

	.file-attachment:hover {
		background-color: rgba(255,255,255,0.3);
	}

	.file-attachment i {
		margin-right: 8px;
	}

	.chat-welcome {
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		height: 100%;
		color: #6c757d;
	}

	.chat-welcome i {
		font-size: 4rem;
		margin-bottom: 1rem;
		color: #dee2e6;
	}

	/* Modern Chat Input Styling */
	.chat-input-container {
		padding: 15px 20px;
		background-color: #fff;
		border-top: 1px solid #e9ecef;
	}

	.chat-input-wrapper {
		display: flex;
		align-items: flex-end;
		gap: 12px;
		background-color: #f8f9fa;
		border-radius: 25px;
		padding: 8px 12px;
		border: 2px solid transparent;
		transition: all 0.3s ease;
	}

	.chat-input-wrapper:focus-within {
		background-color: #fff;
		border-color: #0d6efd;
		box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
	}

	.attachment-btn {
		width: 40px;
		height: 40px;
		padding: 0;
		display: flex;
		align-items: center;
		justify-content: center;
		background-color: transparent;
		border: none;
		border-radius: 50%;
		color: #6c757d;
		cursor: pointer;
		transition: all 0.2s;
		flex-shrink: 0;
	}

	.attachment-btn:hover {
		background-color: #e9ecef;
		color: #0d6efd;
	}

	#messageInput {
		flex: 1;
		resize: none;
		overflow: hidden;
		min-height: 40px;
		max-height: 120px;
		border: none;
		background-color: transparent;
		font-size: 15px;
		line-height: 1.5;
		padding: 8px 0;
		outline: none;
	}

	#messageInput::placeholder {
		color: #adb5bd;
	}

	.send-btn {
		width: 40px;
		height: 40px;
		padding: 0;
		display: flex;
		align-items: center;
		justify-content: center;
		background: linear-gradient(135deg, #0d6efd, #0b5ed7);
		border: none;
		border-radius: 50%;
		color: white;
		cursor: pointer;
		transition: all 0.3s ease;
		flex-shrink: 0;
		box-shadow: 0 2px 5px rgba(13, 110, 253, 0.3);
	}

	.send-btn:hover {
		transform: scale(1.05);
		box-shadow: 0 4px 8px rgba(13, 110, 253, 0.4);
	}

	.send-btn:active {
		transform: scale(0.95);
	}

	#attachmentPreview {
		margin-top: 10px;
		padding: 10px;
		background-color: #f8f9fa;
		border-radius: 8px;
	}

	#attachmentPreview img {
		max-width: 150px;
		max-height: 150px;
		border-radius: 8px;
		border: 2px solid #dee2e6;
	}

	.remove-attachment-btn {
		background-color: #dc3545;
		color: white;
		border: none;
		border-radius: 50%;
		width: 24px;
		height: 24px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		cursor: pointer;
		margin-left: 10px;
		transition: all 0.2s;
	}

	.remove-attachment-btn:hover {
		background-color: #c82333;
		transform: scale(1.1);
	}

	.order-item-image {
		width: 40px;
		height: 40px;
		object-fit: cover;
		border-radius: 4px;
	}

	.order-item-placeholder {
		width: 40px;
		height: 40px;
		background-color: #f8f9fa;
		border-radius: 4px;
		display: flex;
		align-items: center;
		justify-content: center;
		color: #6c757d;
	}

	.order-item-color-badge {
		display: inline-flex;
		align-items: center;
		gap: 0.4rem;
		padding: 0.2rem 0.6rem;
		background-color: #f8f9fa;
		border-radius: 12px;
		font-size: 0.8rem;
		margin-top: 0.25rem;
	}

	.order-item-color-swatch {
		width: 14px;
		height: 14px;
		border-radius: 50%;
		border: 2px solid white;
		box-shadow: 0 0 0 1px #dee2e6;
	}

@media (max-width: 768px) {
		.main-content {
			margin: 10px;
		}

		.message {
			max-width: 85%;
		}

		.chat-container {
			height: 400px;
		}
	}
</style>
<main class="main-content">
	<div class="container-fluid">
		<div class="page-header">
			<div class="col">
				<h1 class="page-header-title">Order #<?php echo $orderId; ?> Chat</h1>
			</div>
			<div class="header-actions">
				<a href="tailor_orders.php" class="btn btn-outline-secondary">
					<i class="fas fa-arrow-left me-2"></i>Back to Orders
				</a>
				<div class="col-auto">
					<a class="btn btn-primary" href="view_order.php?id=<?php echo $orderId; ?>" class="btn btn-outline-secondary me-2">
						<i class="fas fa-eye me-1"></i> View Details
					</a>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-8">
				<div class="card mb-3">
					<div class="card-header d-flex justify-content-between align-items-center">
						<h5 class="mb-0">Chat History</h5>
						<span class="badge <?php
							switch ($order['assignment_status']) {
								case 'assigned': echo 'bg-secondary'; break;
								case 'in_progress': echo 'bg-primary'; break;
								case 'completed': echo 'bg-success'; break;
								case 'cancelled': echo 'bg-danger'; break;
							}
							?>">
							<?php echo ucwords(str_replace('_', ' ', $order['assignment_status'])); ?>
						</span>
					</div>

					<div class="card-body p-0">
						<div class="chat-container">
							<div class="chat-messages" id="chatMessages">
								<?php if ($chatResult && $chatResult->num_rows > 0): ?>
								<?php
								$currentDate = '';
								while ($message = $chatResult->fetch_assoc()):
								$messageDate = date('Y-m-d', strtotime($message['created_at']));

								if ($currentDate != $messageDate) {
									$currentDate = $messageDate;
									$dateDisplay = '';

									$today = date('Y-m-d');
									$yesterday = date('Y-m-d', strtotime('-1 day'));

									if ($messageDate == $today) {
										$dateDisplay = 'Today';
									} elseif ($messageDate == $yesterday) {
										$dateDisplay = 'Yesterday';
									} else {
										$dateDisplay = date('F j, Y', strtotime($messageDate));
									}
									?>
									<div class="date-separator">
										<span><?php echo $dateDisplay; ?></span>
									</div>
									<?php
								} ?>

								<div class="message <?php echo ($message['sender_type'] == 'tailor' && $message['sender_id'] == $tailorId) ? 'message-sent' : 'message-received'; ?>">
									<div class="message-content">
										<?php if (!empty($message['message'])): ?>
										<div class="message-text">
											<?php echo nl2br(htmlspecialchars($message['message'])); ?>
										</div>
										<?php endif; ?>

										<?php if (!empty($message['attachment'])): ?>
										<div class="message-attachment">
											<?php
											$ext = pathinfo($message['attachment'], PATHINFO_EXTENSION);
											$isImage = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif']);

											if ($isImage):
											?>
											<a href="../uploads/chat/<?php echo $message['attachment']; ?>" target="_blank">
												<img src="../uploads/chat/<?php echo $message['attachment']; ?>" alt="Attachment">
											</a>
											<?php else : ?>
											<a href="../uploads/chat/<?php echo $message['attachment']; ?>" target="_blank" class="file-attachment">
												<i class="fas fa-file"></i>
												<span>Download Attachment</span>
											</a>
											<?php endif; ?>
										</div>
										<?php endif; ?>
									</div>
									<div class="message-meta">
										<?php if ($message['sender_type'] != 'tailor' || $message['sender_id'] != $tailorId): ?>
											<span class="message-sender">
												<?php 
												echo htmlspecialchars($message['sender_name']); 
												echo ' (' . ucfirst($message['sender_type']) . ')';
												?>
											</span>
											<span class="mx-1">•</span>
										<?php endif; ?>
										<span class="message-time"><?php echo formatMessageDate($message['created_at']); ?></span>
									</div>
								</div>
								<?php endwhile; ?>
								<?php else : ?>
								<div class="chat-welcome">
									<i class="fas fa-comments"></i>
									<h5>No messages yet</h5>
									<p class="text-muted">
										Start the conversation with the customer about their order.
									</p>
								</div>
								<?php endif; ?>
							</div>
						</div>
					</div>
					
					<div class="chat-input-container">
						<form action="chat.php?order_id=<?php echo $orderId; ?>" method="post" enctype="multipart/form-data" id="chatForm">
							<div class="chat-input-wrapper">
								<label for="attachment" class="attachment-btn" title="Attach a file">
									<i class="fas fa-paperclip"></i>
									<input type="file" id="attachment" name="attachment" class="d-none">
								</label>
								<textarea class="form-control" id="messageInput" name="message" placeholder="Type your message..." rows="1"></textarea>
								<button class="send-btn" type="submit">
									<i class="fas fa-paper-plane"></i>
								</button>
							</div>
							<div id="attachmentPreview" style="display: none;"></div>
						</form>
					</div>
				</div>
			</div>

			<div class="col-md-4">
				<div class="card mb-3">
					<div class="card-header">
						<h5 class="mb-0">Order Summary</h5>
					</div>
					<div class="card-body">
						<div class="mb-3">
							<small class="text-muted">Customer</small>
							<p class="mb-0">
								<?php if (!empty($order['first_name']) || !empty($order['last_name'])): ?>
								<?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?>
								<?php else : ?>
								<span class="text-muted">Guest Order</span>
								<?php endif; ?>
							</p>
							<?php if (!empty($order['email'])): ?>
							<p class="mb-0">
								<small><?php echo htmlspecialchars($order['email']); ?></small>
							</p>
							<?php endif; ?>
							<?php if (!empty($order['phone'])): ?>
							<p class="mb-0">
								<small><?php echo htmlspecialchars($order['phone']); ?></small>
							</p>
							<?php endif; ?>
						</div>

						<div class="mb-3">
							<small class="text-muted">Due Date</small>
							<p class="mb-0">
								<?php if (!empty($order['due_date'])): ?>
								<?php
								$dueDate = strtotime($order['due_date']);
								$today = strtotime(date('Y-m-d'));
								$daysLeft = floor(($dueDate - $today) / (60 * 60 * 24));

								if ($daysLeft < 0) {
									echo '<span class="text-danger">Overdue by ' . abs($daysLeft) . ' days</span>';
								} elseif ($daysLeft == 0) {
									echo '<span class="text-warning">Due today</span>';
								} elseif ($daysLeft <= 2) {
									echo '<span class="text-warning">' . date('M d, Y', $dueDate) . ' (' . $daysLeft . ' days left)</span>';
								} else {
									echo '<span>' . date('M d, Y', $dueDate) . ' (' . $daysLeft . ' days left)</span>';
								}
								?>
								<?php else : ?>
								<span class="text-muted">Not set</span>
								<?php endif; ?>
							</p>
						</div>

						<div class="mb-3">
							<small class="text-muted">Items</small>
							<?php if ($itemsResult && $itemsResult->num_rows > 0): ?>
							<ul class="list-group list-group-flush mt-2">
								<?php while ($item = $itemsResult->fetch_assoc()): ?>
								<li class="list-group-item px-0 py-2 d-flex align-items-center">
									<?php if (!empty($item['product_image'])): ?>
									<img src="../images/<?php echo $item['product_image']; ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="order-item-image me-2">
									<?php else : ?>
									<div class="order-item-placeholder me-2">
										<i class="fas fa-tshirt"></i>
									</div>
									<?php endif; ?>
									<div>
										<p class="mb-0">
											<?php echo htmlspecialchars($item['product_name']); ?>
										</p>
										<?php if (!empty($item['color_name'])): ?>
											<div class="order-item-color-badge">
												<span class="order-item-color-swatch" style="background-color: <?php echo htmlspecialchars($item['color_code']); ?>"></span>
												<span><?php echo htmlspecialchars($item['color_name']); ?></span>
											</div>
										<?php endif; ?>
										<small class="text-muted">
											<?php echo $item['quantity']; ?> × ₦<?php echo number_format($item['price']); ?>
										</small>
									</div>
								</li>
								<?php endwhile; ?>
							</ul>
							<?php else : ?>
							<p class="text-muted mb-0">
								No items found
							</p>
							<?php endif; ?>
						</div>

						<?php if (!empty($order['notes'])): ?>
						<div class="mb-3">
							<small class="text-muted">Notes</small>
							<p class="mb-0">
								<?php echo nl2br(htmlspecialchars($order['notes'])); ?>
							</p>
						</div>
						<?php endif; ?>
					</div>
					<div class="card-footer">
						<button type="button" class="btn btn-sm btn-primary update-status-btn" data-bs-toggle="modal" data-bs-target="#updateStatusModal"
							data-assignment-id="<?php echo $order['assignment_id']; ?>"
							data-order-id="<?php echo $orderId; ?>"
							data-current-status="<?php echo $order['assignment_status']; ?>">
							<i class="fas fa-edit me-1"></i> Update Status
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Update Status Modal -->
	<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="updateStatusModalLabel">Update Order Status</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<form action="tailor_orders.php" method="post">
					<input type="hidden" name="update_status" value="1">
					<input type="hidden" name="assignment_id" id="modal_assignment_id">
					<div class="modal-body">
						<p>
							Order #<span id="modal_order_id"></span>
						</p>
						<div class="mb-3">
							<label for="status" class="form-label">Status</label>
							<select class="form-select" id="modal_status" name="status" required>
								<option value="assigned">Assigned</option>
								<option value="in_progress">In Progress</option>
								<option value="completed">Completed</option>
								<option value="cancelled">Cancelled</option>
							</select>
						</div>
						<div class="alert alert-info">
							<small>
								<i class="fas fa-info-circle me-2"></i>
								Updating the status to "Completed" will notify the customer and admin that the work has been finished.
							</small>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
						<button type="submit" class="btn btn-primary">Update Status</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</main>

<script>
	document.addEventListener('DOMContentLoaded', function() {
		// Scroll to bottom of chat on load
		const chatMessages = document.getElementById('chatMessages');
		chatMessages.scrollTop = chatMessages.scrollHeight;

		// Handle attachment preview
		const attachment = document.getElementById('attachment');
		const attachmentPreview = document.getElementById('attachmentPreview');

		attachment.addEventListener('change', function() {
			if (this.files && this.files[0]) {
				const file = this.files[0];
				const reader = new FileReader();

				attachmentPreview.innerHTML = '';
				attachmentPreview.style.display = 'block';

				if (file.type.match('image.*')) {
					reader.onload = function(e) {
						const img = document.createElement('img');
						img.src = e.target.result;

						const removeBtn = document.createElement('button');
						removeBtn.className = 'remove-attachment-btn';
						removeBtn.innerHTML = '<i class="fas fa-times"></i>';
						removeBtn.type = 'button';
						removeBtn.addEventListener('click', function(e) {
							e.preventDefault();
							attachment.value = '';
							attachmentPreview.style.display = 'none';
						});

						attachmentPreview.appendChild(img);
						attachmentPreview.appendChild(removeBtn);
					}
					reader.readAsDataURL(file);
				} else {
					const fileExt = file.name.split('.').pop().toLowerCase();
					let iconClass = 'fa-file';
					
					if (['pdf'].includes(fileExt)) iconClass = 'fa-file-pdf';
					else if (['doc', 'docx'].includes(fileExt)) iconClass = 'fa-file-word';
					
					const filePreview = document.createElement('div');
					filePreview.className = 'd-flex align-items-center';
					filePreview.innerHTML = `
						<i class="fas ${iconClass} me-2"></i>
						<span>${file.name}</span>
					`;

					const removeBtn = document.createElement('button');
					removeBtn.className = 'remove-attachment-btn';
					removeBtn.innerHTML = '<i class="fas fa-times"></i>';
					removeBtn.type = 'button';
					removeBtn.addEventListener('click', function() {
						attachment.value = '';
						attachmentPreview.style.display = 'none';
					});

					filePreview.appendChild(removeBtn);
					attachmentPreview.appendChild(filePreview);
				}
			}
		});

		// Auto-resize textarea as user types
		const messageInput = document.getElementById('messageInput');
		messageInput.addEventListener('input', function() {
			this.style.height = 'auto';
			this.style.height = (this.scrollHeight) + 'px';
		});

		// Form validation - prevent empty submissions
		const chatForm = document.getElementById('chatForm');
		chatForm.addEventListener('submit', function(e) {
			const message = messageInput.value.trim();
			const hasAttachment = attachment.files.length > 0;

			if (!message && !hasAttachment) {
				e.preventDefault();
			}
		});

		// Update status modal functionality
		var updateStatusButtons = document.querySelectorAll('.update-status-btn');
		updateStatusButtons.forEach(function(button) {
			button.addEventListener('click', function() {
				var assignmentId = this.getAttribute('data-assignment-id');
				var orderId = this.getAttribute('data-order-id');
				var currentStatus = this.getAttribute('data-current-status');

				document.getElementById('modal_assignment_id').value = assignmentId;
				document.getElementById('modal_order_id').textContent = orderId;
				document.getElementById('modal_status').value = currentStatus;

				document.getElementById('updateStatusModalLabel').textContent = 'Update Status for Order #' + orderId;
			});
		});
	});
</script>

<?php include('includes/footer.php'); ?>
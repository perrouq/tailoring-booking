<?php
session_start();
require_once('includes/config.php');
require_once('includes/tailor_functions.php');

// Check if tailor is logged in
if(!isset($_SESSION['tailor_id'])) {
    header("Location: ../admin/index.php");
    exit();
}

// Set default filters
$status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build the query for orders assigned to this tailor
$query = "SELECT o.*, c.first_name, c.last_name, c.email, c.phone,
          ota.assignment_id, ota.status AS assignment_status, ota.due_date, ota.notes,
          (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as product_count
          FROM orders o
          LEFT JOIN customers c ON o.user_id = c.id
          INNER JOIN order_tailor_assignments ota ON o.id = ota.order_id
          WHERE ota.tailor_id = " . $_SESSION['tailor_id'];

// Add filters
if (!empty($status)) {
    $query .= " AND ota.status = '$status'";
}

if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $query .= " AND (c.first_name LIKE '%$search%' OR c.last_name LIKE '%$search%'
                OR c.email LIKE '%$search%' OR o.payment_reference LIKE '%$search%'
                OR o.id LIKE '%$search%')";
}

// Get total count for pagination
$countQuery = str_replace("o.*, c.first_name, c.last_name, c.email, c.phone,
          ota.assignment_id, ota.status AS assignment_status, ota.due_date, ota.notes,
          (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as product_count", "COUNT(*) as total", $query);
$countResult = $conn->query($countQuery);
$totalOrders = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalOrders / $limit);

// Add ordering and pagination
$query .= " ORDER BY ota.due_date ASC, o.created_at DESC LIMIT $offset, $limit";
$result = $conn->query($query);

// Handle assignment status update if necessary
if (isset($_POST['update_status']) && isset($_POST['assignment_id'])) {
    $assignmentId = (int)$_POST['assignment_id'];
    $newStatus = $_POST['status'];
    
    if (in_array($newStatus, ['assigned', 'in_progress', 'completed', 'cancelled'])) {
        $updateQuery = "UPDATE order_tailor_assignments SET status = '$newStatus' WHERE assignment_id = $assignmentId AND tailor_id = " . $_SESSION['tailor_id'];
        
        if ($conn->query($updateQuery)) {
            $_SESSION['success'] = "Order status updated successfully";
        } else {
            $_SESSION['error'] = "Failed to update order status";
        }
    }
    
    // Redirect to remove the POST parameters
    header("Location: tailor_orders.php?status=$status&search=" . urlencode($search) . "&page=$page");
    exit();
}

include('includes/header.php');
include('includes/sidebar.php');
?>

<main class="main-content">
    <div class="container">
        <div class="page-header">
            <h1>My Orders</h1>
            <div class="header-actions">
                <a href="dashboard.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <!-- Display success/error messages -->
                <?php if(isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <div class="filters">
                    <form action="" method="GET" class="filter-form">
                        <div class="row">
                            <div class="col-md-3 form-group">
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="in_progress" <?php if ($status == 'in_progress') echo 'selected'; ?>>In Progress</option>
                                    <option value="completed" <?php if ($status == 'completed') echo 'selected'; ?>>Completed</option>
                                    <option value="cancelled" <?php if ($status == 'cancelled') echo 'selected'; ?>>Cancelled</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 form-group search-group">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Search by customer name, email, order ID..." value="<?php echo htmlspecialchars($search); ?>">
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
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Products</th>
                                <th>Due Date</th>
                                <th>Status</th>
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
                                        <?php else: ?>
                                        <span class="text-muted">Guest Order</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo $row['product_count']; ?> items</span>
                                    </td>
                                    <td>
                                        <?php if (!empty($row['due_date'])): ?>
                                            <?php
                                            $dueDate = strtotime($row['due_date']);
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
                                        <?php else: ?>
                                            <span class="text-muted">Not set</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = '';
                                        switch ($row['assignment_status']) {
                                            case 'assigned':
                                                $statusClass = 'bg-secondary';
                                                break;
                                            case 'in_progress':
                                                $statusClass = 'bg-primary';
                                                break;
                                            case 'completed':
                                                $statusClass = 'bg-success';
                                                break;
                                            case 'cancelled':
                                                $statusClass = 'bg-danger';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?>">
                                            <?php echo ucwords(str_replace('_', ' ', $row['assignment_status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="view_order.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="chat.php?order_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary" title="Chat">
                                                <?php if (hasUnreadMessages($conn, $row['id'], $_SESSION['tailor_id'], 'tailor')): ?>
                                                    <i class="fas fa-comments"></i>
                                                    <span class="badge bg-danger position-absolute top-0 start-100 translate-middle p-1">
                                                        <span class="visually-hidden">Unread messages</span>
                                                    </span>
                                                <?php else: ?>
                                                    <i class="fas fa-comments"></i>
                                                <?php endif; ?>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <button type="button" class="dropdown-item update-status-btn" data-bs-toggle="modal" data-bs-target="#updateStatusModal"
                                                        data-assignment-id="<?php echo $row['assignment_id']; ?>"
                                                        data-order-id="<?php echo $row['id']; ?>"
                                                        data-current-status="<?php echo $row['assignment_status']; ?>">
                                                        <i class="fas fa-edit text-primary"></i> Update Status
                                                    </button>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#notesModal" 
                                                        data-notes="<?php echo htmlspecialchars($row['notes']); ?>">
                                                        <i class="fas fa-sticky-note text-info"></i> View Notes
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                                endwhile;
                            } else {
                                echo '<tr><td colspan="6" class="text-center">No orders assigned to you yet</td></tr>';
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
    
    <!-- Update Status Modal -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateStatusModalLabel">Update Order Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post">
                    <input type="hidden" name="update_status" value="1">
                    <input type="hidden" name="assignment_id" id="modal_assignment_id">
                    <div class="modal-body">
                        <p>Order #<span id="modal_order_id"></span></p>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="modal_status" name="status" required>
                               <!-- <option value="assigned">Assigned</option>-->
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
    
    <!-- Notes Modal -->
    <div class="modal fade" id="notesModal" tabindex="-1" aria-labelledby="notesModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="notesModalLabel">Assignment Notes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="notes-content p-3">
                        <!-- Notes content will be inserted here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
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
        
        // Notes modal functionality
        var notesModal = document.getElementById('notesModal');
        notesModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var notes = button.getAttribute('data-notes');
            var notesContent = notesModal.querySelector('.notes-content');
            
            if (notes && notes.trim() !== '') {
                notesContent.innerHTML = `<p>${notes.replace(/\n/g, '<br>')}</p>`;
            } else {
                notesContent.innerHTML = '<p class="text-muted">No notes available for this assignment.</p>';
            }
        });
    });
</script>

<?php 
// Helper function to check for unread messages
function hasUnreadMessages($conn, $orderId, $tailorId, $recipientType) {
    $query = "SELECT COUNT(*) as count FROM chat_messages 
              WHERE order_id = $orderId 
              AND sender_type != '$recipientType' 
              AND read_status = 0";
              
    $result = $conn->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        return $row['count'] > 0;
    }
    return false;
}

include('includes/footer.php'); 
?>




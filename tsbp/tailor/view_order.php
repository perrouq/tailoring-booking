<?php
session_start();
require_once('includes/config.php');
require_once('includes/tailor_functions.php');

// Check if tailor is logged in
if(!isset($_SESSION['tailor_id'])) {
    header("Location: ../admin/index.php");
    exit();
}

// Get order ID from query string
if(!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Invalid order ID";
    header("Location: tailor_orders.php");
    exit();
}

$order_id = (int)$_GET['id'];
$tailor_id = $_SESSION['tailor_id'];

// Check if this order is assigned to this tailor
$query = "SELECT ota.*, o.*, c.first_name, c.last_name, c.email, c.phone
          FROM order_tailor_assignments ota
          JOIN orders o ON ota.order_id = o.id
          LEFT JOIN customers c ON o.user_id = c.id
          WHERE ota.order_id = $order_id AND ota.tailor_id = $tailor_id";
$result = $conn->query($query);

if($result->num_rows == 0) {
    $_SESSION['error'] = "You don't have access to this order";
    header("Location: tailor_orders.php");
    exit();
}

$order = $result->fetch_assoc();

// Get order items with color information
$items_query = "SELECT oi.*, 
                       p.name as product_name, 
                       p.image as product_image,
                       pc.color_name,
                       pc.color_code
               FROM order_items oi
               JOIN products p ON oi.product_id = p.id
               LEFT JOIN product_colors pc ON oi.color_id = pc.id
               WHERE oi.order_id = $order_id";
$items_result = $conn->query($items_query);

// Get customer measurements if available
$measurements_query = "SELECT * FROM customer_measurements WHERE customer_id = {$order['user_id']}";
$measurements_result = $conn->query($measurements_query);
$measurements = ($measurements_result && $measurements_result->num_rows > 0) ? $measurements_result->fetch_assoc() : null;

// Handle status update if submitted
if(isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $assignment_id = (int)$_POST['assignment_id'];
    
    if(in_array($new_status, ['assigned', 'in_progress', 'completed', 'cancelled'])) {
        $update_query = "UPDATE order_tailor_assignments 
                        SET status = '$new_status' 
                        WHERE assignment_id = $assignment_id AND tailor_id = $tailor_id";
        
        if($conn->query($update_query)) {
            $_SESSION['success'] = "Order status updated successfully";
            $order['status'] = $new_status;
        } else {
            $_SESSION['error'] = "Failed to update order status: " . $conn->error;
        }
    }
}

include('includes/header.php');
include('includes/sidebar.php');
?>

<style>
.item-color-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.3rem 0.75rem;
    background-color: #f8f9fa;
    border-radius: 15px;
    font-size: 0.875rem;
    margin-top: 0.35rem;
}

.item-color-swatch {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 0 0 1px #dee2e6;
}

.item-color-name {
    font-weight: 500;
    color: #495057;
}

.notes-box {
    max-height: 150px;
    overflow-y: auto;
}

.instruction-box {
    max-height: 100px;
    overflow-y: auto;
}

/* Responsive Design */
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        gap: 1rem;
    }
    
    .header-actions {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .header-actions a,
    .header-actions button {
        width: 100%;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
}

@media (max-width: 576px) {
    .page-title {
        font-size: 1.5rem;
    }
    
    .card-header h5 {
        font-size: 1rem;
    }
    
    .badge {
        font-size: 0.75rem;
    }
    
    .item-color-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
    
    .item-color-swatch {
        width: 14px;
        height: 14px;
    }
}
</style>

<main class="main-content">
    <div class="container">
        <div class="page-header">
            <h1>Order Details #<?php echo $order_id; ?></h1>
            <div class="header-actions">
                <a href="tailor_orders.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Orders
                </a>
                <a href="chat.php?order_id=<?php echo $order_id; ?>" class="btn btn-primary ms-2">
                    <i class="fas fa-comments me-2"></i>
                    Chat
                    <?php if (hasUnreadMessages($conn, $order_id, $tailor_id, 'tailor')): ?>
                        <span class="badge bg-danger position-absolute top-0 start-100 translate-middle p-1">
                            <span class="visually-hidden">Unread messages</span>
                        </span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
        
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
        
        <div class="row">
            <!-- Order Summary Card -->
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header text-white">
                        <h5 class="card-title mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6 class="text-muted">Order Status</h6>
                            <?php
                            $statusClass = '';
                            switch ($order['status']) {
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
                            <span class="badge <?php echo $statusClass; ?> fs-6 d-inline-block mb-3">
                                <?php echo ucwords(str_replace('_', ' ', $order['status'])); ?>
                            </span>
                            
                            <button type="button" class="btn btn-sm btn-outline-primary d-block" data-bs-toggle="modal" data-bs-target="#updateStatusModal">
                                <i class="fas fa-edit me-1"></i> Update Status
                            </button>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="text-muted">Due Date</h6>
                            <?php if (!empty($order['due_date'])): 
                                $dueDate = strtotime($order['due_date']);
                                $today = strtotime(date('Y-m-d'));
                                $daysLeft = floor(($dueDate - $today) / (60 * 60 * 24));
                                
                                if ($daysLeft < 0): ?>
                                    <div class="text-danger">
                                        <i class="fas fa-exclamation-circle"></i>
                                        Overdue by <?php echo abs($daysLeft); ?> days
                                    </div>
                                    <div><?php echo date('F d, Y', $dueDate); ?></div>
                                <?php elseif ($daysLeft == 0): ?>
                                    <div class="text-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        Due today
                                    </div>
                                    <div><?php echo date('F d, Y', $dueDate); ?></div>
                                <?php else: ?>
                                    <div class="<?php echo ($daysLeft <= 2) ? 'text-warning' : ''; ?>">
                                        <?php echo date('F d, Y', $dueDate); ?>
                                        (<?php echo $daysLeft; ?> days left)
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">Not set</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="text-muted">Assigned On</h6>
                            <div><?php echo date('F d, Y', strtotime($order['assignment_date'])); ?></div>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="text-muted">Order Date</h6>
                            <div><?php echo date('F d, Y', strtotime($order['created_at'])); ?></div>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="text-muted">Total Amount</h6>
                            <div class="fw-bold">₦<?php echo number_format($order['total_amount'], 2); ?></div>
                        </div>
                        
                        <?php if (!empty($order['notes'])): ?>
                        <div class="mb-0">
                            <h6 class="text-muted">Notes</h6>
                            <div class="notes-box p-2 bg-light rounded border">
                                <?php echo nl2br(htmlspecialchars($order['notes'])); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Customer Information Card -->
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header text-white">
                        <h5 class="card-title mb-0">Customer Information</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($order['first_name']) || !empty($order['last_name'])): ?>
                            <div class="mb-3">
                                <h6 class="text-muted">Customer Name</h6>
                                <div><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></div>
                            </div>
                            
                            <div class="mb-3">
                                <h6 class="text-muted">Contact Information</h6>
                                <div class="d-flex flex-column">
                                    <div><i class="fas fa-envelope me-2 text-muted"></i><?php echo htmlspecialchars($order['email']); ?></div>
                                    <?php if (!empty($order['phone'])): ?>
                                        <div><i class="fas fa-phone me-2 text-muted"></i><?php echo htmlspecialchars($order['phone']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                This appears to be a guest order without customer account details.
                            </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <h6 class="text-muted">Delivery Address</h6>
                            <div>
                                <?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?><br>
                                <?php echo htmlspecialchars($order['delivery_city']); ?>, 
                                <?php echo htmlspecialchars($order['delivery_state']); ?> 
                                <?php echo htmlspecialchars($order['delivery_zip']); ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($order['delivery_instructions'])): ?>
                            <div class="mb-0">
                                <h6 class="text-muted">Delivery Instructions</h6>
                                <div class="instruction-box p-2 bg-light rounded border">
                                    <?php echo nl2br(htmlspecialchars($order['delivery_instructions'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Measurements Card -->
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header text-white">
                        <h5 class="card-title mb-0">Customer Measurements</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($measurements): ?>
                            <div class="row">
                                <?php 
                                $measurement_fields = [
                                    'neck' => 'Neck',
                                    'chest' => 'Chest',
                                    'shoulder' => 'Shoulder',
                                    'sleeve' => 'Sleeve',
                                    'bicep' => 'Bicep',
                                    'wrist' => 'Wrist',
                                    'waist' => 'Waist',
                                    'hip' => 'Hip',
                                    'inseam' => 'Inseam',
                                    'thigh' => 'Thigh',
                                    'knee' => 'Knee',
                                    'ankle' => 'Ankle'
                                ];
                                
                                foreach ($measurement_fields as $field => $label):
                                    if (!empty($measurements[$field])):
                                ?>
                                    <div class="col-6 mb-2">
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted"><?php echo $label; ?>:</span>
                                            <span class="fw-bold"><?php echo $measurements[$field]; ?> in</span>
                                        </div>
                                    </div>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </div>
                            
                            <?php if (!empty($measurements['notes'])): ?>
                                <div class="mt-3">
                                    <h6 class="text-muted">Measurement Notes</h6>
                                    <div class="notes-box p-2 bg-light rounded border">
                                        <?php echo nl2br(htmlspecialchars($measurements['notes'])); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-ruler me-2"></i>
                                No measurements available for this customer.
                            </div>
                            <p class="text-muted">
                                Consider requesting the customer to provide their measurements via the chat to ensure proper fitting.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Order Items Section -->
        <div class="card mb-4">
            <div class="card-header text-white">
                <h5 class="card-title mb-0">Order Items</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 60px;">Image</th>
                                <th>Product</th>
                                <th class="text-end">Price</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $totalItems = 0;
                            if ($items_result && $items_result->num_rows > 0):
                                while ($item = $items_result->fetch_assoc()):
                                    $totalItems += $item['quantity'];
                            ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($item['product_image'])): ?>
                                            <a href="../images/<?php echo $item['product_image']; ?>" target="_blank">
                                                <img src="../images/<?php echo $item['product_image']; ?>" alt="<?php echo $item['product_name']; ?>" 
                                                     class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover; cursor: pointer;">
                                            </a>
                                        <?php else: ?>
                                            <div class="bg-light text-center" style="width: 50px; height: 50px; line-height: 50px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                        <?php if (!empty($item['color_name'])): ?>
                                            <div class="item-color-badge">
                                                <div class="item-color-swatch" style="background-color: <?php echo htmlspecialchars($item['color_code']); ?>"></div>
                                                <span class="item-color-name"><?php echo htmlspecialchars($item['color_name']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <small class="text-muted d-block">SKU: PRD-<?php echo $item['product_id']; ?></small>
                                    </td>
                                    <td class="text-end">₦<?php echo number_format($item['price'], 2); ?></td>
                                    <td class="text-center"><?php echo $item['quantity']; ?></td>
                                    <td class="text-end">₦<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <div class="text-muted">No items found for this order</div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="table-group-divider">
                            <tr>
                                <th colspan="3" class="text-end">Subtotal (<?php echo $totalItems; ?> items):</th>
                                <th class="text-end" colspan="2">₦<?php echo number_format($order['total_amount'], 2); ?></th>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end">Shipping:</th>
                                <th class="text-end" colspan="2">₦0.00</th>
                            </tr>
                            <tr class="fw-bold">
                                <th colspan="3" class="text-end">Total:</th>
                                <th class="text-end" colspan="2">₦<?php echo number_format($order['total_amount'], 2); ?></th>
                            </tr>
                        </tfoot>
                    </table>
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
                <form action="" method="post">
                    <input type="hidden" name="update_status" value="1">
                    <input type="hidden" name="assignment_id" value="<?php echo $order['assignment_id']; ?>">
                    <div class="modal-body">
                        <p>Order #<?php echo $order_id; ?></p>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="in_progress" <?php echo ($order['status'] == 'in_progress') ? 'selected' : ''; ?>>In Progress</option>
                                <option value="completed" <?php echo ($order['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo ($order['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
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
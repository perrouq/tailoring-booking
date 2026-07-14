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

// Check if order ID is provided
$order_id = null;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $order_id = $_GET['id'];
} elseif (isset($_GET['view']) && is_numeric($_GET['view'])) {
    $order_id = $_GET['view'];
}

// If no valid order ID is found, redirect to orders page
if (!$order_id) {
    $_SESSION['error'] = "Invalid order ID";
    header("Location: orders.php");
    exit();
}

// Get order details
$query = "SELECT o.*, c.first_name, c.last_name, c.email, c.phone
          FROM orders o 
          LEFT JOIN customers c ON o.user_id = c.id
          WHERE o.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    $_SESSION['error'] = "Order not found";
    header("Location: orders.php");
    exit();
}
$order = $result->fetch_assoc();
$stmt->close();

// Get order items with color information
$items_query = "SELECT oi.*, 
                       p.name as product_name, 
                       p.image as product_image,
                       pc.color_name,
                       pc.color_code
                FROM order_items oi
                LEFT JOIN products p ON oi.product_id = p.id
                LEFT JOIN product_colors pc ON oi.color_id = pc.id
                WHERE oi.order_id = ?";
$items_stmt = $conn->prepare($items_query);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
$items_stmt->close();

// Get status from URL for return link
$status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<style>
.item-color-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.25rem 0.75rem;
    background-color: #f8f9fa;
    border-radius: 12px;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.item-color-swatch {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 0 0 1px #dee2e6;
}

.item-color-name {
    font-weight: 500;
    color: #495057;
}
</style>

<main class="main-content">
    <div class="container">
        <div class="page-header">
            <h1>Order #<?php echo $order_id; ?> Details</h1>
            <div class="header-actions">
                <a href="orders.php?status=<?php echo urlencode($status); ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $page; ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Orders
                </a>
                <button class="btn btn-primary" id="printOrderBtn">
                    <i class="fas fa-print me-1"></i> Print Order
                </button>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body" id="orderDetailsContent">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6>Order Information</h6>
                        <table class="table table-sm">
                            <tr>
                                <th>Order ID:</th>
                                <td>#<?php echo $order['id']; ?></td>
                            </tr>
                            <tr>
                                <th>Date:</th>
                                <td><?php echo date('F d, Y h:i A', strtotime($order['created_at'])); ?></td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $order['payment_status'] == 'completed' ? 'success' : 
                                            ($order['payment_status'] == 'pending' ? 'warning' : 'danger'); 
                                    ?>">
                                        <?php echo ucfirst($order['payment_status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Payment Reference:</th>
                                <td><?php echo htmlspecialchars($order['payment_reference'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Total Amount:</th>
                                <td>₦<?php echo number_format($order['total_amount'], 2); ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <h6>Customer Information</h6>
                        <?php if ($order['user_id']): ?>
                            <table class="table table-sm">
                                <tr>
                                    <th>Name:</th>
                                    <td><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td><?php echo htmlspecialchars($order['email']); ?></td>
                                </tr>
                                <tr>
                                    <th>Phone:</th>
                                    <td><?php echo htmlspecialchars($order['phone'] ?? 'N/A'); ?></td>
                                </tr>
                            </table>
                        <?php else: ?>
                            <div class="alert alert-secondary">
                                <i class="fas fa-user-tag me-2"></i>This is a guest order (no registered customer).
                            </div>
                        <?php endif; ?>
                        
                        <h6 class="mt-3">Delivery Information</h6>
                        <table class="table table-sm">
                            <tr>
                                <th>Address:</th>
                                <td><?php echo htmlspecialchars($order['delivery_address'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>City:</th>
                                <td><?php echo htmlspecialchars($order['delivery_city'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>State:</th>
                                <td><?php echo htmlspecialchars($order['delivery_state'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Zip Code:</th>
                                <td><?php echo htmlspecialchars($order['delivery_zip'] ?? 'N/A'); ?></td>
                            </tr>
                            <?php if (!empty($order['delivery_instructions'])): ?>
                            <tr>
                                <th>Instructions:</th>
                                <td><?php echo htmlspecialchars($order['delivery_instructions']); ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <h6 class="border-bottom pb-2 mb-3">Order Items</h6>

                <?php if ($items_result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total = 0;
                                $items_result->data_seek(0); // Reset pointer
                                while ($item = $items_result->fetch_assoc()): 
                                    $subtotal = $item['quantity'] * $item['price'];
                                    $total += $subtotal;
                                ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if (!empty($item['product_image'])): ?>
                                                    <img src="../images/<?php echo htmlspecialchars($item['product_image']); ?>" alt="Product" class="me-2" style="width: 50px; height: 50px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="me-2 bg-light text-center" style="width: 50px; height: 50px; line-height: 50px;">
                                                        <i class="fas fa-box"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <div><?php echo htmlspecialchars($item['product_name'] ?? 'Unknown Product'); ?></div>
                                                    <?php if (!empty($item['color_name'])): ?>
                                                        <div class="item-color-badge">
                                                            <div class="item-color-swatch" style="background-color: <?php echo htmlspecialchars($item['color_code']); ?>"></div>
                                                            <span class="item-color-name"><?php echo htmlspecialchars($item['color_name']); ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="small text-muted">Product ID: <?php echo $item['product_id']; ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>₦<?php echo number_format($item['price'], 2); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td>₦<?php echo number_format($subtotal, 2); ?></td>
                                        <td>
                                            <a href="includes/process_orders.php?action=delete_item&item_id=<?php echo $item['id']; ?>&order_id=<?php echo $order_id; ?>&return=view_order" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Are you sure you want to remove this item from the order?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Total:</th>
                                    <th>₦<?php echo number_format($total, 2); ?></th>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>No items found in this order.
                    </div>
                <?php endif; ?>

                <div class="mt-4">
                    <h6 class="border-bottom pb-2 mb-3">Add Product to Order</h6>
                    <form action="includes/process_orders.php" method="post" class="row g-3" id="addItemForm">
                        <input type="hidden" name="action" value="add_item">
                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                        <input type="hidden" name="return" value="view_order">
                        
                        <div class="col-md-4">
                            <label for="product_id" class="form-label">Product:</label>
                            <select class="form-select" id="product_id" name="product_id" required>
                                <option value="">-- Select Product --</option>
                                <?php
                                $products_query = "SELECT id, name, price FROM products ORDER BY name";
                                $products_result = $conn->query($products_query);
                                while ($product = $products_result->fetch_assoc()) {
                                    echo '<option value="' . $product['id'] . '" data-price="' . $product['price'] . '">' . 
                                         htmlspecialchars($product['name']) . ' - ₦' . number_format($product['price'], 2) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3" id="colorSelectionContainer" style="display: none;">
                            <label for="color_id" class="form-label">Color:</label>
                            <select class="form-select" id="color_id" name="color_id">
                                <option value="">-- Select Color --</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="quantity" class="form-label">Quantity:</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" min="1" value="1" required>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="price" class="form-label">Unit Price:</label>
                            <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                        </div>
                        
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">Add</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productSelect = document.getElementById('product_id');
    const colorSelect = document.getElementById('color_id');
    const colorContainer = document.getElementById('colorSelectionContainer');
    const priceInput = document.getElementById('price');
    const quantityInput = document.getElementById('quantity');
    
    // When product is selected
    productSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (selectedOption.value) {
            const price = selectedOption.getAttribute('data-price');
            priceInput.value = price;
            
            const productId = selectedOption.value;
            
            // Fetch colors for the selected product
            fetch(`includes/get_product_colors.php?product_id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    colorSelect.innerHTML = '<option value="">-- Select Color --</option>';
                    
                    if (data.success && data.colors.length > 0) {
                        data.colors.forEach(color => {
                            const option = document.createElement('option');
                            option.value = color.id;
                            option.textContent = `${color.color_name} (Stock: ${color.quantity})`;
                            option.dataset.maxStock = color.quantity;
                            
                            if (color.quantity <= 0) {
                                option.disabled = true;
                                option.textContent += ' - Out of Stock';
                            }
                            
                            colorSelect.appendChild(option);
                        });
                        
                        colorContainer.style.display = 'block';
                        colorSelect.required = true;
                    } else {
                        colorContainer.style.display = 'none';
                        colorSelect.required = false;
                    }
                })
                .catch(error => {
                    console.error('Error fetching colors:', error);
                    colorContainer.style.display = 'none';
                });
        } else {
            priceInput.value = '';
            colorContainer.style.display = 'none';
            colorSelect.required = false;
        }
    });
    
    // Validate quantity against stock
    colorSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            const maxStock = parseInt(selectedOption.dataset.maxStock);
            quantityInput.max = maxStock;
            
            if (parseInt(quantityInput.value) > maxStock) {
                quantityInput.value = maxStock;
            }
        }
    });
    
    // Print order functionality
    document.getElementById('printOrderBtn').addEventListener('click', function() {
        var printContents = document.getElementById('orderDetailsContent').innerHTML;
        var originalContents = document.body.innerHTML;

        document.body.innerHTML = '<div class="container print-content">' +
        '<h2 class="text-center my-4">Order Details</h2>' +
        printContents + '</div>';

        window.print();
        
        document.body.innerHTML = originalContents;
        location.reload();
    });
});
</script>

<?php include 'includes/footer.php'; ?>
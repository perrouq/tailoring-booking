<?php
session_start();
require_once 'includes/config.php';

// Redirect if user is not logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    $_SESSION['redirect_after_login'] = 'cart.php';
    $_SESSION['flash_message'] = "Please log in to view your cart";
    $_SESSION['flash_type'] = "alert-info";
    header('Location: login.php');
    exit;
}

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

include 'includes/header.php';
?>

<style>
    /* Cart Page Specific Styles */
    :root {
        --primary: #5e35b1;
        --primary-light: #7c51d1;
        --primary-dark: #4527a0;
        --secondary: #ff9800;
        --secondary-light: #ffb74d;
        --secondary-dark: #f57c00;
        --text-light: #ffffff;
        --text-dark: #212121;
        --text-muted: #757575;
        --gray-50: #f5f7fa;
        --gray-100: #e4e7eb;
        --gray-200: #cbd2d9;
        --gray-300: #9aa5b1;
        --gray-800: #2d3748;
        --red-500: #ef4444;
        --success: #10b981;
        --success-light: #d1fae5;
        --warning: #f59e0b;
        --container-width: 1200px;
        --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .main-container {
        max-width: var(--container-width);
        margin: 2rem auto;
        padding: 0 1rem;
    }

    .page-header {
        margin-bottom: 2rem;
        text-align: center;
        position: relative;
    }

    .page-title {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary-dark);
        margin: 0;
        padding-bottom: 0.5rem;
        position: relative;
        display: inline-block;
    }

    .page-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 3px;
        background: var(--secondary);
        border-radius: 3px;
    }

    .cart-container {
        background-color: white;
        border-radius: 12px;
        box-shadow: var(--shadow-lg);
        overflow: hidden;
    }

    /* Empty Cart */
    .cart-empty {
        padding: 3rem 1.5rem;
        text-align: center;
        background: linear-gradient(135deg, #ffffff, var(--gray-50));
    }

    .cart-empty h2 {
        color: var(--primary-dark);
        font-size: 1.8rem;
        margin-bottom: 1rem;
    }

    .cart-empty p {
        color: var(--text-muted);
        margin-bottom: 2rem;
    }

    .cart-empty svg {
        width: 100px;
        height: 100px;
        color: var(--gray-300);
        margin-bottom: 1.5rem;
    }

    .btn-primary {
        display: inline-block;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        box-shadow: 0 4px 6px rgba(94, 53, 177, 0.2);
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, var(--primary-dark), var(--primary));
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(94, 53, 177, 0.3);
    }

    .btn-secondary {
        display: inline-block;
        background-color: var(--gray-50);
        color: var(--text-dark);
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        border: 1px solid var(--gray-200);
        cursor: pointer;
    }

    .btn-secondary:hover {
        background-color: var(--gray-100);
    }

    /* Cart Items */
    .cart-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }

    @media (min-width: 768px) {
        .cart-grid {
            grid-template-columns: 2fr 1fr;
        }
    }

    .cart-items {
        padding: 1.5rem;
    }

    .cart-items-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid var(--gray-100);
    }

    .cart-items-header h2 {
        color: var(--primary-dark);
        font-size: 1.5rem;
        margin: 0;
    }

    .cart-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background-color: var(--secondary);
        color: var(--text-dark);
        font-weight: 700;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.875rem;
    }

    .cart-item {
        position: relative;
        background-color: white;
        border-radius: 8px;
        box-shadow: var(--shadow-sm);
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
        border: 1px solid var(--gray-100);
    }

    .cart-item:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }

    .cart-item-content {
        display: grid;
        grid-template-columns: 100px 1fr auto;
        gap: 1rem;
        padding: 1.25rem;
        align-items: center;
    }

    @media (max-width: 640px) {
        .cart-item-content {
            grid-template-columns: 80px 1fr;
            gap: 0.75rem;
            padding: 1rem;
        }
        
        .cart-item-actions {
            grid-column: span 2;
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
        }
    }

    .cart-item img {
        width: 100%;
        height: auto;
        border-radius: 6px;
        object-fit: cover;
        aspect-ratio: 1;
        box-shadow: var(--shadow-sm);
    }

    .cart-item-info {
        display: flex;
        flex-direction: column;
    }

    .cart-item-info h3 {
        margin: 0 0 0.5rem;
        font-size: 1.125rem;
        color: var(--text-dark);
    }

    .cart-item-info .price {
        font-weight: 700;
        color: var(--primary);
        margin: 0;
        font-size: 1.125rem;
    }

    .cart-item-info .category {
        font-size: 0.875rem;
        color: var(--text-muted);
        margin: 0.5rem 0;
    }

    .cart-item-color {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.5rem;
        padding: 0.375rem 0.75rem;
        background-color: var(--gray-50);
        border-radius: 20px;
        font-size: 0.875rem;
    }

    .cart-item-color-swatch {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 2px solid white;
        box-shadow: 0 0 0 1px var(--gray-200);
    }

    .cart-item-color-name {
        font-weight: 600;
        color: var(--text-dark);
    }

    .cart-item-actions {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        align-items: center;
    }

    .quantity-control {
        display: inline-flex;
        align-items: center;
        border: 1px solid var(--gray-200);
        border-radius: 8px;
        overflow: hidden;
    }

    .quantity-btn {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: var(--gray-50);
        border: none;
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-dark);
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .quantity-btn:hover {
        background-color: var(--gray-100);
    }

    .quantity-input {
        width: 40px;
        height: 36px;
        border: none;
        text-align: center;
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-dark);
        -moz-appearance: textfield;
    }

    .quantity-input::-webkit-outer-spin-button,
    .quantity-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .remove-item {
        background: none;
        border: none;
        color: var(--red-500);
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 50%;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .remove-item:hover {
        background-color: rgba(239, 68, 68, 0.1);
    }

    /* Order Summary */
    .order-summary {
        background: linear-gradient(135deg, var(--primary-light), var(--primary-dark));
        color: white;
        padding: 1.5rem;
        border-radius: 0 0 12px 12px;
    }

    @media (min-width: 768px) {
        .order-summary {
            border-radius: 0 12px 12px 0;
            height: 100%;
        }
    }

    .order-summary h2 {
        font-size: 1.5rem;
        margin: 0 0 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        text-align: center;
    }

    .summary-items {
        margin-bottom: 1.5rem;
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.75rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        font-size: 0.95rem;
    }

    .summary-item-details {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .summary-item-color {
        font-size: 0.8rem;
        opacity: 0.8;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .summary-item:last-child {
        border-bottom: none;
    }

    .summary-total {
        display: flex;
        justify-content: space-between;
        padding: 1.5rem 0;
        margin: 1.5rem 0;
        border-top: 1px solid rgba(255, 255, 255, 0.2);
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        font-size: 1.25rem;
        font-weight: 700;
    }

    .checkout-button {
        display: block;
        width: 100%;
        background-color: var(--secondary);
        color: var(--text-dark);
        text-align: center;
        padding: 1rem;
        border-radius: 8px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        margin-top: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .checkout-button:hover {
        background-color: var(--secondary-light);
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }

    .continue-shopping {
        display: block;
        text-align: center;
        margin-top: 1rem;
        color: rgba(255, 255, 255, 0.7);
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .continue-shopping:hover {
        color: white;
    }

    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .cart-item {
        animation: fadeIn 0.5s ease forwards;
    }

    .cart-item:nth-child(2) { animation-delay: 0.1s; }
    .cart-item:nth-child(3) { animation-delay: 0.2s; }
    .cart-item:nth-child(4) { animation-delay: 0.3s; }
    .cart-item:nth-child(5) { animation-delay: 0.4s; }

    /* Alert Messages */
    .alert {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        animation: fadeIn 0.5s ease forwards;
    }

    .alert-success {
        background-color: var(--success-light);
        color: var(--success);
        border-left: 4px solid var(--success);
    }

    .alert-warning {
        background-color: rgba(245, 158, 11, 0.1);
        color: var(--warning);
        border-left: 4px solid var(--warning);
    }
</style>

<div class="main-container">
    <div class="page-header">
        <h1 class="page-title">Shopping Cart</h1>
    </div>
    
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert <?php echo $_SESSION['flash_type']; ?>">
            <?php echo $_SESSION['flash_message']; ?>
        </div>
        <?php unset($_SESSION['flash_message']); unset($_SESSION['flash_type']); ?>
    <?php endif; ?>
    
    <div id="cart-container" class="cart-container">
        <?php if (empty($_SESSION['cart'])): ?>
            <div class="cart-empty">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <h2>Your cart is empty</h2>
                <p>Looks like you haven't added any items to your cart yet.</p>
                <a href="index.php" class="btn-primary">Start Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-grid">
                <div class="cart-items">
                    <div class="cart-items-header">
                        <h2>Your Items</h2>
                        <span class="cart-count"><?php echo count($_SESSION['cart']); ?> items</span>
                    </div>
                    
                    <?php 
                    $total = 0;
                    foreach ($_SESSION['cart'] as $index => $item): 
                        $product_id = $item['product_id'];
                        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
                        $stmt->bind_param("i", $product_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $product = $result->fetch_assoc();
                        
                        if ($product):
                            // Get color information if color_id exists
                            $color_info = null;
                            if (isset($item['color_id'])) {
                                $color_stmt = $conn->prepare("SELECT * FROM product_colors WHERE id = ?");
                                $color_stmt->bind_param("i", $item['color_id']);
                                $color_stmt->execute();
                                $color_result = $color_stmt->get_result();
                                if ($color_result->num_rows > 0) {
                                    $color_info = $color_result->fetch_assoc();
                                }
                            }
                            
                            $item_total = $product['price'] * $item['quantity'];
                            $total += $item_total;
                            
                            $cart_key = isset($item['color_id']) ? $product['id'] . '_' . $item['color_id'] : $product['id'];
                    ?>
                        <div class="cart-item" data-product-id="<?php echo $product['id']; ?>" data-color-id="<?php echo isset($item['color_id']) ? $item['color_id'] : ''; ?>" data-cart-key="<?php echo $cart_key; ?>">
                            <div class="cart-item-content">
                                <img src="images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <div class="cart-item-info">
                                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                    <?php if (isset($product['category']) && !empty($product['category'])): ?>
                                        <div class="category"><?php echo htmlspecialchars($product['category']); ?></div>
                                    <?php endif; ?>
                                    
                                    <?php if ($color_info): ?>
                                        <div class="cart-item-color">
                                            <div class="cart-item-color-swatch" style="background-color: <?php echo htmlspecialchars($color_info['color_code']); ?>"></div>
                                            <span class="cart-item-color-name"><?php echo htmlspecialchars($color_info['color_name']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <p class="price">₦<?php echo number_format($product['price'], 2); ?></p>
                                </div>
                                <div class="cart-item-actions">
                                    <div class="quantity-control">
                                        <button class="quantity-btn decrease" data-product-id="<?php echo $product['id']; ?>" data-color-id="<?php echo isset($item['color_id']) ? $item['color_id'] : ''; ?>">-</button>
                                        <input type="number" class="quantity-input" min="1" max="99" value="<?php echo $item['quantity']; ?>" 
                                               data-product-id="<?php echo $product['id']; ?>" data-color-id="<?php echo isset($item['color_id']) ? $item['color_id'] : ''; ?>"
                                               data-max-stock="<?php echo $color_info ? $color_info['quantity'] : 99; ?>">
                                        <button class="quantity-btn increase" data-product-id="<?php echo $product['id']; ?>" data-color-id="<?php echo isset($item['color_id']) ? $item['color_id'] : ''; ?>">+</button>
                                    </div>
                                    <button class="remove-item" data-product-id="<?php echo $product['id']; ?>" data-color-id="<?php echo isset($item['color_id']) ? $item['color_id'] : ''; ?>" title="Remove item">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>
                
                <div class="order-summary">
                    <h2>Order Summary</h2>
                    <div class="summary-items">
                        <?php 
                        foreach ($_SESSION['cart'] as $item): 
                            $product_id = $item['product_id'];
                            $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
                            $stmt->bind_param("i", $product_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $product = $result->fetch_assoc();
                            
                            if ($product):
                                // Get color information if color_id exists
                                $color_info = null;
                                if (isset($item['color_id'])) {
                                    $color_stmt = $conn->prepare("SELECT * FROM product_colors WHERE id = ?");
                                    $color_stmt->bind_param("i", $item['color_id']);
                                    $color_stmt->execute();
                                    $color_result = $color_stmt->get_result();
                                    if ($color_result->num_rows > 0) {
                                        $color_info = $color_result->fetch_assoc();
                                    }
                                }
                                
                                $item_total = $product['price'] * $item['quantity'];
                        ?>
                            <div class="summary-item">
                                <div class="summary-item-details">
                                    <span><?php echo htmlspecialchars($product['name']); ?> (x<?php echo $item['quantity']; ?>)</span>
                                    <?php if ($color_info): ?>
                                        <span class="summary-item-color">
                                            <span style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; background-color: <?php echo htmlspecialchars($color_info['color_code']); ?>; border: 1px solid rgba(255,255,255,0.3);"></span>
                                            <?php echo htmlspecialchars($color_info['color_name']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <span>₦<?php echo number_format($item_total, 2); ?></span>
                            </div>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                    <div class="summary-total">
                        <span>Total</span>
                        <span>₦<?php echo number_format($total, 2); ?></span>
                    </div>
                    <a href="checkout.php" class="checkout-button">
                        <i class="fas fa-lock"></i> Proceed to Checkout
                    </a>
                    <a href="index.php" class="continue-shopping">
                        <i class="fas fa-arrow-left"></i> Continue Shopping
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle quantity change
    const quantityInputs = document.querySelectorAll('.quantity-input');
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            const productId = this.getAttribute('data-product-id');
            const colorId = this.getAttribute('data-color-id');
            const maxStock = parseInt(this.getAttribute('data-max-stock'));
            let quantity = parseInt(this.value);
            
            if (quantity < 1) {
                quantity = 1;
            } else if (quantity > maxStock) {
                quantity = maxStock;
                alert(`Only ${maxStock} items available in stock`);
            } else if (quantity > 99) {
                quantity = 99;
            }
            
            this.value = quantity;
            updateCartItem(productId, quantity, colorId);
        });
    });
    
    // Handle quantity decrease
    const decreaseButtons = document.querySelectorAll('.quantity-btn.decrease');
    decreaseButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const colorId = this.getAttribute('data-color-id');
            const input = document.querySelector(`.quantity-input[data-product-id="${productId}"][data-color-id="${colorId}"]`);
            let quantity = parseInt(input.value);
            
            if (quantity > 1) {
                quantity--;
                input.value = quantity;
                updateCartItem(productId, quantity, colorId);
            }
        });
    });
    
    // Handle quantity increase
    const increaseButtons = document.querySelectorAll('.quantity-btn.increase');
    increaseButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const colorId = this.getAttribute('data-color-id');
            const input = document.querySelector(`.quantity-input[data-product-id="${productId}"][data-color-id="${colorId}"]`);
            const maxStock = parseInt(input.getAttribute('data-max-stock'));
            let quantity = parseInt(input.value);
            
            if (quantity < maxStock && quantity < 99) {
                quantity++;
                input.value = quantity;
                updateCartItem(productId, quantity, colorId);
            } else if (quantity >= maxStock) {
                alert(`Only ${maxStock} items available in stock`);
            }
        });
    });
    
    // Handle remove item
    const removeButtons = document.querySelectorAll('.remove-item');
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const colorId = this.getAttribute('data-color-id');
            removeCartItem(productId, colorId);
        });
    });
    
    function updateCartItem(productId, quantity, colorId = '') {
        const formData = new FormData();
        formData.append('action', 'update');
        formData.append('product_id', productId);
        formData.append('quantity', quantity);
        if (colorId) {
            formData.append('color_id', colorId);
        }
        
        fetch('cart_process.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
    
    function removeCartItem(productId, colorId = '') {
        if (!confirm('Are you sure you want to remove this item from your cart?')) {
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'remove');
        formData.append('product_id', productId);
        if (colorId) {
            formData.append('color_id', colorId);
        }
        
        fetch('cart_process.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const itemElement = document.querySelector(`.cart-item[data-product-id="${productId}"][data-color-id="${colorId}"]`);
                if (itemElement) {
                    itemElement.style.opacity = '0';
                    itemElement.style.transform = 'translateY(20px)';
                    itemElement.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    
                    setTimeout(() => {
                        window.location.reload();
                    }, 300);
                } else {
                    window.location.reload();
                }
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
    
    // Optional: Add animation when page loads
    const cartItems = document.querySelectorAll('.cart-item');
    cartItems.forEach((item, index) => {
        item.style.opacity = '0';
        setTimeout(() => {
            item.style.opacity = '1';
        }, 100 * index);
    });
});
</script>

<?php include 'includes/footer.php'; ?>
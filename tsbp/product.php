<?php
// Initialize the session
session_start();
// Include database connection
require_once 'includes/config.php';

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
	// Store the intended URL to redirect back after login
	$_SESSION['redirect_after_login'] = 'product.php?id=' . (isset($_GET['id']) ? $_GET['id'] : '');

	// Set flash message
	$_SESSION['flash_message'] = "Please log in to view product details";
	$_SESSION['flash_type'] = "alert-info";

	// Redirect to login page
	header("Location: login.php");
	exit;
}

// Check if product ID is set
if (!isset($_GET['id'])) {
	header("Location: index.php");
	exit;
}
$productId = $_GET['id'];

// Fetch product details
$product = null;
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
	$product = $result->fetch_assoc();
} else {
	header("Location: index.php");
	exit;
}

// Fetch product colors
$colors = [];
$sql = "SELECT * FROM product_colors WHERE product_id = ? ORDER BY color_name";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
	$colors[] = $row;
}

// Fetch related products (same category)
$related_products = [];
$sql = "SELECT * FROM products WHERE category = ? AND id != ? LIMIT 4";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $product['category'], $productId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
	$related_products[] = $row;
}

$stmt->close();
$conn->close();
?>
<?php include 'includes/header.php'; ?>

<style>
/* Color Selection Styles */
.color-selection {
    margin: 1.5rem 0;
}

.color-selection-label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.75rem;
    color: var(--text-dark);
    font-size: 1rem;
}

.color-options {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.color-option {
    position: relative;
    cursor: pointer;
    transition: all 0.3s ease;
}

.color-option input[type="radio"] {
    position: absolute;
    opacity: 0;
    cursor: pointer;
}

.color-option-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    background-color: white;
    transition: all 0.3s ease;
    min-width: 100px;
}

.color-swatch {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid #e5e7eb;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.color-name {
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    text-align: center;
}

.color-stock {
    font-size: 0.75rem;
    color: #6b7280;
}

.color-stock.low-stock {
    color: #f59e0b;
    font-weight: 600;
}

.color-stock.out-of-stock {
    color: #ef4444;
    font-weight: 600;
}

.color-option input[type="radio"]:checked + .color-option-label {
    border-color: var(--primary);
    background-color: #f3f4f6;
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(94, 53, 177, 0.2);
}

.color-option input[type="radio"]:checked + .color-option-label .color-swatch {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(94, 53, 177, 0.2);
}

.color-option.disabled {
    cursor: not-allowed;
    opacity: 0.5;
}

.color-option.disabled .color-option-label {
    background-color: #f9fafb;
}

.color-error {
    color: #ef4444;
    font-size: 0.875rem;
    margin-top: 0.5rem;
    display: none;
}

.color-error.show {
    display: block;
}

@media (max-width: 768px) {
    .color-options {
        gap: 0.5rem;
    }
    
    .color-option-label {
        min-width: 80px;
        padding: 0.5rem;
    }
    
    .color-swatch {
        width: 35px;
        height: 35px;
    }
}
</style>

<main id="app" class="container">
	<!-- Breadcrumb Navigation -->
	<div class="breadcrumb">
		<a href="index.php">Home</a>
		<span class="breadcrumb-separator"><i class="fas fa-chevron-right"></i></span>
		<a href="category.php?category=<?php echo urlencode($product['category']); ?>"><?php echo htmlspecialchars($product['category']); ?></a>
		<span class="breadcrumb-separator"><i class="fas fa-chevron-right"></i></span>
		<span><?php echo htmlspecialchars($product['name']); ?></span>
	</div>

	<!-- Cart Notification -->
	<div id="cart-notification" class="alert"></div>

	<!-- Product Details Section -->
	<div class="product-details">
		<div class="product-image">
			<div class="product-category">
				<?php echo htmlspecialchars($product['category']); ?>
			</div>
			<img src="images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
		</div>
		<div class="product-info">
			<h1><?php echo htmlspecialchars($product['name']); ?></h1>
			<p class="price">
				₦<?php echo number_format($product['price'], 2); ?>
			</p>
			<p class="description">
				<?php echo htmlspecialchars($product['description']); ?>
			</p>

			<?php if (!empty($colors)): ?>
			<div class="color-selection">
				<label class="color-selection-label">
					Select Color: <span class="required">*</span>
				</label>
				<div class="color-options" id="colorOptions">
					<?php foreach ($colors as $color): ?>
						<?php 
							$isOutOfStock = $color['quantity'] <= 0;
							$isLowStock = $color['quantity'] > 0 && $color['quantity'] <= 5;
						?>
						<div class="color-option <?php echo $isOutOfStock ? 'disabled' : ''; ?>">
							<input 
								type="radio" 
								name="color" 
								id="color_<?php echo $color['id']; ?>" 
								value="<?php echo $color['id']; ?>"
								data-color-name="<?php echo htmlspecialchars($color['color_name']); ?>"
								data-quantity="<?php echo $color['quantity']; ?>"
								<?php echo $isOutOfStock ? 'disabled' : ''; ?>
							>
							<label class="color-option-label" for="color_<?php echo $color['id']; ?>">
								<div class="color-swatch" style="background-color: <?php echo htmlspecialchars($color['color_code']); ?>"></div>
								<span class="color-name"><?php echo htmlspecialchars($color['color_name']); ?></span>
								<span class="color-stock <?php echo $isOutOfStock ? 'out-of-stock' : ($isLowStock ? 'low-stock' : ''); ?>">
									<?php 
										if ($isOutOfStock) {
											echo 'Out of Stock';
										} elseif ($isLowStock) {
											echo 'Only ' . $color['quantity'] . ' left';
										} else {
											echo $color['quantity'] . ' in stock';
										}
									?>
								</span>
							</label>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="color-error" id="colorError">Please select a color</div>
			</div>
			<?php endif; ?>

			<div class="product-features">
				<h3 class="features-title">Product Features</h3>
				<ul class="features-list">
					<li>Premium quality materials</li>
					<li>Custom tailored to your measurements</li>
					<li>Fast delivery</li>
					<li>Perfect fit guarantee</li>
				</ul>
			</div>

			<div id="add-to-cart-form">
				<input type="hidden" id="product_id" value="<?php echo $product['id']; ?>">
				<div class="quantity-input">
					<label for="quantity">Quantity:</label>
					<div class="custom-number-input">
						<input type="number" id="quantity" min="1" value="1" max="99">
					</div>
				</div>
				<button type="button" id="add-to-cart-btn" class="add-to-cart">
					<i class="fas fa-shopping-cart"></i>
					<span>Add to Cart</span>
				</button>
			</div>
		</div>
	</div>

	<!-- Related Products Section -->
	<?php if (!empty($related_products)): ?>
	<div class="related-products">
		<h2 class="section-title">You May Also Like</h2>
		<div class="products-grid">
			<?php foreach ($related_products as $related): ?>
			<div class="product-card">
				<div class="product-card-image">
					<img src="images/<?php echo htmlspecialchars($related['image']); ?>" alt="<?php echo htmlspecialchars($related['name']); ?>">
				</div>
				<div class="product-card-info">
					<h3 class="product-card-title"><?php echo htmlspecialchars($related['name']); ?></h3>
					<p class="product-card-price">
						₦<?php echo number_format($related['price'], 2); ?>
					</p>
					<a href="product.php?id=<?php echo $related['id']; ?>" class="product-card-link">View Details</a>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>

<script>
	document.addEventListener('DOMContentLoaded', function() {
		const addToCartBtn = document.getElementById('add-to-cart-btn');
		const notification = document.getElementById('cart-notification');
		const colorOptions = document.querySelectorAll('input[name="color"]');
		const colorError = document.getElementById('colorError');
		const quantityInput = document.getElementById('quantity');

		// Update max quantity when color is selected
		colorOptions.forEach(option => {
			option.addEventListener('change', function() {
				const maxQty = parseInt(this.dataset.quantity);
				quantityInput.max = maxQty;
				
				// Reset quantity if it exceeds available stock
				if (parseInt(quantityInput.value) > maxQty) {
					quantityInput.value = maxQty;
				}
				
				// Hide error when color is selected
				colorError.classList.remove('show');
			});
		});

		addToCartBtn.addEventListener('click', function() {
			const productId = document.getElementById('product_id').value;
			const quantity = document.getElementById('quantity').value;
			const hasColors = colorOptions.length > 0;
			
			// Validate color selection if colors are available
			let selectedColor = null;
			let selectedColorName = '';
			
			if (hasColors) {
				const selectedColorInput = document.querySelector('input[name="color"]:checked');
				if (!selectedColorInput) {
					colorError.classList.add('show');
					colorError.textContent = 'Please select a color';
					return;
				}
				selectedColor = selectedColorInput.value;
				selectedColorName = selectedColorInput.dataset.colorName;
				
				// Validate quantity against available stock
				const availableQty = parseInt(selectedColorInput.dataset.quantity);
				if (parseInt(quantity) > availableQty) {
					notification.className = 'alert alert-danger';
					notification.innerHTML = '<i class="fas fa-exclamation-circle"></i>Only ' + availableQty + ' items available in ' + selectedColorName;
					notification.style.display = 'block';
					setTimeout(() => {
						notification.style.display = 'none';
					}, 5000);
					return;
				}
			}

			// Disable button while processing
			addToCartBtn.disabled = true;
			addToCartBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Adding...</span>';

			// Create form data
			const formData = new FormData();
			formData.append('action', 'add');
			formData.append('product_id', productId);
			formData.append('quantity', quantity);
			if (selectedColor) {
				formData.append('color_id', selectedColor);
				formData.append('color_name', selectedColorName);
			}

			// Send AJAX request
			fetch('cart_process.php', {
				method: 'POST',
				body: formData
			})
			.then(response => response.json())
			.then(data => {
				// Show notification
				if (data.success) {
					notification.className = 'alert alert-success';
					let message = data.message;
					if (selectedColorName) {
						message += ' (Color: ' + selectedColorName + ')';
					}
					notification.innerHTML = '<i class="fas fa-check-circle"></i>' + message;

					// Update cart count in header if exists
					const cartCountBadges = document.querySelectorAll('#cart-count, #desktop-cart-count');
					cartCountBadges.forEach(badge => {
						if (badge) {
							badge.textContent = data.cart_count;
							badge.classList.remove('hidden');
						}
					});

					// Store cart count in localStorage for persistence
					localStorage.setItem('cartCount', data.cart_count);
					
					// Reset form
					if (hasColors) {
						document.querySelector('input[name="color"]:checked').checked = false;
					}
					quantityInput.value = 1;
				} else {
					notification.className = 'alert alert-danger';
					notification.innerHTML = '<i class="fas fa-exclamation-circle"></i>' + (data.message || 'Error adding product to cart');
				}

				// Show notification
				notification.style.display = 'block';

				// Hide notification after 5 seconds
				setTimeout(() => {
					notification.style.display = 'none';
				}, 5000);

				// Re-enable button
				addToCartBtn.disabled = false;
				addToCartBtn.innerHTML = '<i class="fas fa-shopping-cart"></i><span>Add to Cart</span>';
			})
			.catch(error => {
				console.error('Error:', error);
				notification.className = 'alert alert-danger';
				notification.innerHTML = '<i class="fas fa-exclamation-circle"></i>Error adding product to cart. Please try again.';
				notification.style.display = 'block';

				setTimeout(() => {
					notification.style.display = 'none';
				}, 5000);

				// Re-enable button
				addToCartBtn.disabled = false;
				addToCartBtn.innerHTML = '<i class="fas fa-shopping-cart"></i><span>Add to Cart</span>';
			});
		});
	});
</script>
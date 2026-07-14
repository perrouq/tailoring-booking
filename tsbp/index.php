<?php
// Initialize the session
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
// Include config file
require_once 'includes/config.php';

// Pagination setup
$productsPerPage = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $productsPerPage;

// Fetch products for the slider (remains the same)
$featuredProducts = [];
$sql = "SELECT * FROM products LIMIT 5";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
	while ($row = $result->fetch_assoc()) {
		$featuredProducts[] = $row;
	}
}

// Fetch total number of products
$totalProductsQuery = "SELECT COUNT(*) as total FROM products";
$totalProductsResult = $conn->query($totalProductsQuery);
$totalProducts = $totalProductsResult->fetch_assoc()['total'];
$totalPages = ceil($totalProducts / $productsPerPage);

// Fetch paginated products
$allProducts = [];
$sql = "SELECT * FROM products LIMIT $offset, $productsPerPage";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
	while ($row = $result->fetch_assoc()) {
		$allProducts[] = $row;
	}
}

$conn->close();
?>

<?php include 'includes/header.php'; ?>



<main id="app">
	<div class="container">

		<!-- Hero Slider with Video -->
		<?php if (!empty($featuredProducts)): ?>
		<div class="hero">
			<div class="hero-slides" id="hero-slides">
				<!-- Video Slide -->
				<div class="hero-slide">
					<video class="hero-video" autoplay muted loop>
						<source src="assets/v1.mp4" type="video/mp4">
						Your browser does not support the video tag.
					</video>
					<div class="hero-content">
						<div class="hero-text">
							<h2>Featured Collection</h2>
							<p>
								Discover our premium tailoring services
							</p>
							<a href="product.php" class="hero-button">Explore Now</a>
						</div>
					</div>
				</div>
				<?php foreach ($featuredProducts as $index => $product): ?>
				<?php if ($index > 0): // Skip the first slot as it's now occupied by video ?>
				<div class="hero-slide">
					<img src="images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
					<div class="hero-content">
						<div class="hero-text">
							<h2><?php echo htmlspecialchars($product['name']); ?></h2>
							<p>
								₦<?php echo number_format($product['price'], 2); ?>
							</p>
							<a href="product.php?id=<?php echo $product['id']; ?>" class="hero-button">View Details</a>
						</div>
					</div>
				</div>
				<?php endif; ?>
				<?php endforeach; ?>
			</div>
			<button class="hero-nav prev" onclick="prevSlide()">
				<i class="fas fa-chevron-left"></i>
			</button>
			<button class="hero-nav next" onclick="nextSlide()">
				<i class="fas fa-chevron-right"></i>
			</button>
		</div>
		<?php endif; ?>

		<!-- Welcome Banner -->
		<section class="welcome-banner">
			<div class="welcome-content">
				<h1>Welcome to Tailor Booking System</h1>
				<p>
					Find the perfect tailoring service for all your fashion needs. Browse our collection of custom designs, book appointments, and track your orders all in one place.
				</p>
				<div class="welcome-buttons">
					<a href="#products" class="welcome-btn primary">Browse Products</a>
					<?php if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']): ?>
					<a href="login.php" class="welcome-btn secondary">Sign In</a>
					<?php endif; ?>
				</div>
			</div>
		</section>

		<!-- Products Section -->
		<section class="products-section" id="products">
			<h2 class="section-title">All Products</h2>
			<div class="product-grid">
				<?php foreach ($allProducts as $product): ?>
				<div class="product-card">
					<a href="product.php?id=<?php echo $product['id']; ?>">
						<img src="images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
						<div class="product-card-content">
							<h3><?php echo htmlspecialchars($product['name']); ?></h3>
							<p class="price">
								₦<?php echo number_format($product['price'], 2); ?>
							</p>
							<p class="category">
								<?php echo htmlspecialchars($product['category']); ?>
							</p>
						</div>
					</a>
					<button class="add-to-cart" onclick="addToCart(<?php echo $product['id']; ?>)">
						<i class="fas fa-shopping-cart"></i> Add to Cart
					</button>
				</div>
				<?php endforeach; ?>
			</div>
		</section>

		<!-- Pagination -->
		<div class="pagination">
			<?php if ($page > 1): ?>
			<a href="?page=<?php echo $page - 1; ?>" class="pagination-btn">
				<i class="fas fa-chevron-left"></i>
			</a>
			<?php endif; ?>

			<?php
			// Show page numbers
			for ($i = 1; $i <= $totalPages; $i++) {
				echo "<a href='?page=$i' class='pagination-btn " . ($i == $page ? 'active' : '') . "'>$i</a>";
			}
			?>

			<?php if ($page < $totalPages): ?>
			<a href="?page=<?php echo $page + 1; ?>" class="pagination-btn">
				<i class="fas fa-chevron-right"></i>
			</a>
			<?php endif; ?>
		</div>

		<!-- Features Section -->
		<section class="features">
			<div class="feature-card">
				<div class="feature-icon">
					<i class="fas fa-tshirt"></i>
				</div>
				<h3>Quality Products</h3>
				<p>
					Expertly crafted garments made with premium materials for all your tailoring needs.
				</p>
			</div>
			<div class="feature-card">
				<div class="feature-icon">
					<i class="fas fa-cut"></i>
				</div>
				<h3>Custom Tailoring</h3>
				<p>
					Personalized fitting services to ensure your clothes fit perfectly every time.
				</p>
			</div>
			<div class="feature-card">
				<div class="feature-icon">
					<i class="fas fa-shipping-fast"></i>
				</div>
				<h3>Fast Delivery</h3>
				<p>
					Quick turnaround times with convenient delivery options for your convenience.
				</p>
			</div>
		</section>

	</div>
</main>

<?php include 'includes/footer.php'; ?>

<script>
let currentSlide = 0;
let slideInterval;
const slides = document.querySelector('#hero-slides');
const slideCount = <?php echo count($featuredProducts) > 0 ? count($featuredProducts) : 1; ?>; // Ensure at least 1 for video

function nextSlide() {
    currentSlide = (currentSlide + 1) % slideCount;
    updateSlide();
}

function prevSlide() {
    currentSlide = (currentSlide - 1 + slideCount) % slideCount;
    updateSlide();
}

function updateSlide() {
    slides.style.transform = `translateX(-${currentSlide * 100}%)`;
    
    // Pause/play videos when they're active/inactive
    const videos = slides.querySelectorAll('video');
    videos.forEach((video, index) => {
        if (index === currentSlide) {
            video.play();
        } else {
            video.pause();
        }
    });
}

function startCarousel() {
    clearInterval(slideInterval);
    slideInterval = setInterval(nextSlide, 8000); // Longer interval for video viewing
}

// Add to cart functionality
function addToCart(productId) {
    // Prevent navigation
    event.preventDefault();
    event.stopPropagation();
    
    // Here you would typically use AJAX to add the product to cart
    fetch('cart_process.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=add&product_id=${productId}&quantity=1`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update cart count
            updateCartCount(data.cart_count);
            
            // Show notification
            alert('Product added to cart!');
            
            // Check if we need to redirect (for login)
            if (data.redirect && data.redirect_url) {
                window.location.href = data.redirect_url;
            }
        } else {
            alert(data.message || 'Failed to add product to cart');
            
            // Check if we need to redirect (for login)
            if (data.redirect && data.redirect_url) {
                window.location.href = data.redirect_url;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}

// Update cart count in the header
function updateCartCount(count) {
    const cartCountBadges = document.querySelectorAll('#cart-count, #desktop-cart-count');
    
    cartCountBadges.forEach(badge => {
        if (count > 0) {
            badge.textContent = count;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    });
    
    // Also update in localStorage for persistence
    localStorage.setItem('cartCount', count);
}

// Initialize the carousel
if (slideCount > 0) {
    startCarousel();
    
    // Make sure video plays on page load
    const firstVideo = slides.querySelector('video');
    if (firstVideo) {
        firstVideo.play().catch(e => {
            console.log('Auto-play was prevented:', e);
            // You might want to add a play button here for mobile devices
        });
    }
}

// Initialize cart count on page load
document.addEventListener('DOMContentLoaded', function() {
    // Here you would typically fetch the cart count from server
    // For now, we'll use localStorage as an example
    const cartCount = localStorage.getItem('cartCount') || 0;
    if (cartCount > 0) {
        updateCartCount(cartCount);
    }
});
</script>
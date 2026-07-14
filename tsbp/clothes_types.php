<?php
// Initialize the session
session_start();

// Include database connection
require_once 'includes/config.php';

// Check if category is set
if (!isset($_GET['category'])) {
    header("Location: index.php");
    exit;
}

$category = $_GET['category']; 

// Get sort parameter
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'price-asc';

// Fetch products from the category
$products = [];
$sql = "SELECT * FROM products WHERE category = ?";

// Add sorting
if ($sort == 'price-asc') {
    $sql .= " ORDER BY price ASC";
} else if ($sort == 'price-desc') {
    $sql .= " ORDER BY price DESC";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $category);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

$stmt->close();
$conn->close();
?>

<?php include 'includes/header.php'; ?>

<style>
    /* Category Page Styling */
    .container {
        max-width: var(--container-width);
        margin: 0 auto;
        padding: 2rem 1rem;
    }

    .category-header {
        margin-bottom: 2rem;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        position: relative;
    }

    .category-header h1 {
        color: var(--primary-dark);
        font-size: 2rem;
        margin-bottom: 1rem;
        position: relative;
        padding-bottom: 0.75rem;
        font-weight: 700;
    }

    .category-header h1::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 60px;
        height: 4px;
        background-color: var(--secondary);
        border-radius: 2px;
    }

    .breadcrumb {
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
        font-size: 0.9rem;
    }

    .breadcrumb a {
        color: var(--primary);
        text-decoration: none;
        transition: var(--transition);
    }

    .breadcrumb a:hover {
        color: var(--secondary);
    }

    .breadcrumb .separator {
        margin: 0 0.5rem;
        color: var(--gray-800);
    }

    .sort-options {
        display: flex;
        align-items: center;
        margin-top: 1rem;
        background-color: white;
        border-radius: 8px;
        padding: 0.5rem;
        box-shadow: var(--shadow-sm);
        width: 100%;
    }

    .sort-options label {
        font-weight: 500;
        margin-right: 1rem;
        color: var(--gray-800);
    }

    #sort-select {
        padding: 0.5rem 1rem;
        border: 1px solid var(--gray-100);
        border-radius: 4px;
        background-color: white;
        color: var(--text-dark);
        cursor: pointer;
        flex-grow: 1;
        font-family: inherit;
        transition: var(--transition);
    }

    #sort-select:focus {
        outline: none;
        border-color: var(--primary-light);
        box-shadow: 0 0 0 2px rgba(94, 53, 177, 0.2);
    }

    /* Product Grid */
    .product-grid {
        display: grid;
        grid-template-columns: repeat(1, 1fr);
        gap: 1.5rem;
    }

    /* Product Card */
    .product-card {
        background-color: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        transition: var(--transition);
        position: relative;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-md);
    }

    .product-card a {
        text-decoration: none;
        color: inherit;
        display: block;
    }

    .product-card img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-bottom: 1px solid var(--gray-100);
    }

    .product-card-content {
        padding: 1.25rem;
    }

    .product-card h3 {
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
        color: var(--text-dark);
        font-weight: 600;
    }

    .product-card .price {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 0.5rem;
    }

    .product-card .category {
        font-size: 0.85rem;
        color: var(--gray-800);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 0.3rem 0.6rem;
        background-color: var(--gray-50);
        border-radius: 4px;
        display: inline-block;
    }

    /* Add to cart button */
    .add-to-cart {
        display: block;
        width: 100%;
        padding: 0.75rem;
        margin-top: 1rem;
        background-color: var(--secondary);
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 600;
        text-align: center;
        transition: var(--transition);
    }

    .add-to-cart:hover {
        background-color: var(--secondary-dark);
    }

    /* No products message */
    .no-products {
        background-color: white;
        padding: 2rem;
        text-align: center;
        border-radius: 8px;
        box-shadow: var(--shadow-sm);
    }

    .no-products p {
        font-size: 1.1rem;
        color: var(--gray-800);
    }

    .no-products .fa-search {
        font-size: 3rem;
        color: var(--gray-800);
        margin-bottom: 1rem;
    }

    /* Responsive adjustments */
    @media (min-width: 768px) {
        .container {
            padding: 3rem 2rem;
        }

        .product-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .category-header {
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
        }

        .category-header h1 {
            margin-bottom: 0;
        }

        .sort-options {
            margin-top: 0;
            width: auto;
        }
    }

    @media (min-width: 992px) {
        .product-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (min-width: 1200px) {
        .product-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }
</style>

<main id="app" class="container">
    <div class="breadcrumb">
        <a href="index.php">Home</a>
        <span class="separator"><i class="fas fa-chevron-right"></i></span>
        <span><?php echo htmlspecialchars($category); ?></span>
    </div>

    <div class="category-header">
        <h1><?php echo htmlspecialchars($category); ?></h1>
        <div class="sort-options">
            <label for="sort-select"><i class="fas fa-sort"></i> Sort by:</label>
            <select id="sort-select" onchange="sortProducts(this.value)">
                <option value="price-asc" <?php echo $sort == 'price-asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                <option value="price-desc" <?php echo $sort == 'price-desc' ? 'selected' : ''; ?>>Price: High to Low</option>
            </select>
        </div>
    </div>

    <?php if (empty($products)): ?>
    <div class="no-products">
        <i class="fas fa-search"></i>
        <p>No products found in this category.</p>
        <a href="index.php" class="add-to-cart">Continue Shopping</a>
    </div>
    <?php else : ?>
    <div class="product-grid">
        <?php foreach ($products as $product): ?>
        <div class="product-card">
            <a href="product.php?id=<?php echo $product['id']; ?>">
                <img src="images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <div class="product-card-content">
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p class="price">₦<?php echo number_format($product['price'], 2); ?></p>
                    <p class="category">
                        <i class="fas fa-tag"></i> <?php echo htmlspecialchars($product['category']); ?>
                    </p>
                    <button class="add-to-cart">
                        <i class="fas fa-shopping-cart"></i> Add to Cart
                    </button>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>

<script>
    function sortProducts(value) {
        window.location.href = `clothes_types.php?category=<?php echo urlencode($category); ?>&sort=${value}`;
    }

    // Add to cart functionality (for demo purposes)
    document.addEventListener('DOMContentLoaded', function() {
        const addToCartButtons = document.querySelectorAll('.add-to-cart');
        
        addToCartButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Demo count update
                const currentCount = parseInt(localStorage.getItem('cartCount') || '0');
                localStorage.setItem('cartCount', currentCount + 1);
                
                // Update cart count badges
                const cartCountBadges = document.querySelectorAll('#cart-count, #desktop-cart-count');
                cartCountBadges.forEach(badge => {
                    badge.textContent = currentCount + 1;
                    badge.classList.remove('hidden');
                });
                
                // Show feedback
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-check"></i> Added to Cart';
                this.style.backgroundColor = 'var(--success)';
                
                // Reset after 2 seconds
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.style.backgroundColor = '';
                }, 2000);
            });
        });
    });
</script>
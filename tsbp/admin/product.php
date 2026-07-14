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

// Process form submission for adding new product
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	// Check which form was submitted
	if (isset($_POST['action'])) {
		if ($_POST['action'] == 'add') {
			// Add new product
			$name = mysqli_real_escape_string($conn, $_POST['name']);
			$price = mysqli_real_escape_string($conn, $_POST['price']);
			$description = mysqli_real_escape_string($conn, $_POST['description']);
			$category = mysqli_real_escape_string($conn, $_POST['category']);
			$image = $_FILES['image']['name'];
			$target = "../images/" . basename($image);

			$query = "INSERT INTO products (name, price, description, category, image)
                    VALUES ('$name', '$price', '$description', '$category', '$image')";

			if ($conn->query($query) === TRUE) {
				$product_id = $conn->insert_id;
				move_uploaded_file($_FILES['image']['tmp_name'], $target);
				
				// Insert colors
				if (isset($_POST['color_names']) && is_array($_POST['color_names'])) {
					for ($i = 0; $i < count($_POST['color_names']); $i++) {
						if (!empty($_POST['color_names'][$i])) {
							$color_name = mysqli_real_escape_string($conn, $_POST['color_names'][$i]);
							$color_code = mysqli_real_escape_string($conn, $_POST['color_codes'][$i]);
							$quantity = (int)$_POST['color_quantities'][$i];
							
							$color_query = "INSERT INTO product_colors (product_id, color_name, color_code, quantity) 
											VALUES ($product_id, '$color_name', '$color_code', $quantity)";
							$conn->query($color_query);
						}
					}
				}
				
				$success_message = "Product added successfully!";
			} else {
				$error_message = "Error: " . $query . "<br>" . $conn->error;
			}
		} elseif ($_POST['action'] == 'edit') {
			// Edit existing product
			$id = mysqli_real_escape_string($conn, $_POST['id']);
			$name = mysqli_real_escape_string($conn, $_POST['name']);
			$price = mysqli_real_escape_string($conn, $_POST['price']);
			$description = mysqli_real_escape_string($conn, $_POST['description']);
			$category = mysqli_real_escape_string($conn, $_POST['category']);

			if (!empty($_FILES['image']['name'])) {
				// New image uploaded
				$image = $_FILES['image']['name'];
				$target = "../images/" . basename($image);
				move_uploaded_file($_FILES['image']['tmp_name'], $target);

				$query = "UPDATE products SET name='$name', price='$price', description='$description',
                          category='$category', image='$image' WHERE id=$id";
			} else {
				// No new image
				$query = "UPDATE products SET name='$name', price='$price', description='$description',
                          category='$category' WHERE id=$id";
			}

			if ($conn->query($query) === TRUE) {
				// Delete existing colors
				$conn->query("DELETE FROM product_colors WHERE product_id = $id");
				
				// Insert updated colors
				if (isset($_POST['color_names']) && is_array($_POST['color_names'])) {
					for ($i = 0; $i < count($_POST['color_names']); $i++) {
						if (!empty($_POST['color_names'][$i])) {
							$color_name = mysqli_real_escape_string($conn, $_POST['color_names'][$i]);
							$color_code = mysqli_real_escape_string($conn, $_POST['color_codes'][$i]);
							$quantity = (int)$_POST['color_quantities'][$i];
							
							$color_query = "INSERT INTO product_colors (product_id, color_name, color_code, quantity) 
											VALUES ($id, '$color_name', '$color_code', $quantity)";
							$conn->query($color_query);
						}
					}
				}
				
				$_SESSION['success'] = "Product updated successfully!";
			} else {
				$_SESSION['error'] = "Error updating product: " . $conn->error;
			}
		}
	}
}

// Process delete action
if (isset($_GET['delete_id'])) {
	$id = mysqli_real_escape_string($conn, $_GET['delete_id']);

	// First check if the product is referenced in order_items
	$check_query = "SELECT COUNT(*) as count FROM order_items WHERE product_id = $id";
	$check_result = $conn->query($check_query);
	$row = $check_result->fetch_assoc();

	if ($row['count'] > 0) {
		// Product is referenced in orders - cannot delete
		$error_message = "Cannot delete this product because it is referenced in one or more orders. Consider marking it as inactive instead.";
	} else {
		// Safe to delete - product is not referenced

		// Get image filename to delete the file
		$query = "SELECT image FROM products WHERE id = $id";
		$result = $conn->query($query);
		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc();
			$image_path = "../images/" . $row['image'];
			// Delete the image file if it exists
			if (file_exists($image_path)) {
				unlink($image_path);
			}
		}

		// Delete the product record (colors will auto-delete due to foreign key)
		$query = "DELETE FROM products WHERE id = $id";
		if ($conn->query($query) === TRUE) {
			$_SESSION['success'] = "Product deleted successfully!";
		} else {
			$_SESSION['error'] = "Error deleting product: " . $conn->error;
		}
	}
}

// Set default filters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Count total products
$countQuery = "SELECT COUNT(*) as total FROM products";
if (!empty($search)) {
	$countQuery .= " WHERE name LIKE '%$search%' OR description LIKE '%$search%'";
}
if (!empty($category)) {
	$countQuery .= (!empty($search) ? " AND" : " WHERE") . " category = '$category'";
}
$countResult = $conn->query($countQuery);
$totalProducts = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalProducts / $limit);

// Get products with pagination
$query = "SELECT * FROM products";
if (!empty($search)) {
	$query .= " WHERE name LIKE '%$search%' OR description LIKE '%$search%'";
}
if (!empty($category)) {
	$query .= (!empty($search) ? " AND" : " WHERE") . " category = '$category'";
}
$query .= " LIMIT $offset, $limit";
$result = $conn->query($query);
?>
	<?php include('includes/header.php'); ?>
	<?php include('includes/sidebar.php'); ?>

	<main class="main-content">
		<div class="container">
			<div class="page-header">
				<h1>Manage Products</h1>
				<p>
					Add, edit, and manage your product inventory
				</p>
				<div class="header-actions">
					<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
						<i class="fas fa-plus"></i> Add New Product
					</button>
				</div>
			</div>

			<?php if (isset($success_message)): ?>
			<div class="alert alert-success alert-dismissible fade show" role="alert">
				<?php echo $success_message; ?>
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
			<?php endif; ?>

			<?php if (isset($error_message)): ?>
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
				<?php echo $error_message; ?>
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
			<?php endif; ?>

			<div class="card">
				<div class="card-body">
					<div class="filters">
						<form action="" method="GET" class="filter-form">
							<div class="form-group">
								<select name="category" class="form-control">
									<option value="">All Categories</option>
									<option value="Shaddas" <?php echo ($category == 'Shaddas') ? 'selected' : ''; ?>>1. Shaddas</option>
									<option value="Yadis" <?php echo ($category == 'Yadis') ? 'selected' : ''; ?>>2. Yadis</option>
									<option value="Kaftanis" <?php echo ($category == 'Kaftanis') ? 'selected' : ''; ?>>3. Kaftanis</option>
								</select>
							</div>

							<div class="form-group search-group">
								<input type="text" name="search" class="form-control" placeholder="Search by name or description" value="<?php echo htmlspecialchars($search); ?>">
								<button type="submit" class="btn btn-primary">
									<i class="fas fa-search"></i>
								</button>
							</div>
						</form>
					</div>

					<div class="table-responsive">
						<table class="table table-striped">
							<thead>
								<tr>
									<th>Image</th>
									<th>Name</th>
									<th>Price</th>
									<th>Category</th>
									<th>Colors & Stock</th>
									<th>Description</th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody>
								<?php if ($result->num_rows == 0): ?>
								<tr>
									<td colspan="7" class="text-center">No products found</td>
								</tr>
								<?php else : ?>
								<?php while ($row = $result->fetch_assoc()): ?>
								<?php
									// Get colors for this product
									$colors_query = "SELECT * FROM product_colors WHERE product_id = " . $row['id'];
									$colors_result = $conn->query($colors_query);
									$colors = [];
									while ($color = $colors_result->fetch_assoc()) {
										$colors[] = $color;
									}
								?>
								<tr>
									<td>
										<img src="../images/<?php echo htmlspecialchars($row['image']); ?>"
										alt="<?php echo htmlspecialchars($row['name']); ?>"
										width="60" height="60" class="product-thumbnail">
									</td>
									<td><?php echo htmlspecialchars($row['name']); ?></td>
									<td>₦<?php echo number_format($row['price']); ?></td>
									<td><?php echo htmlspecialchars($row['category']); ?></td>
									<td>
										<?php if (count($colors) > 0): ?>
											<div style="display: flex; flex-wrap: wrap; gap: 5px;">
												<?php foreach ($colors as $color): ?>
													<span class="badge" style="background-color: <?php echo htmlspecialchars($color['color_code']); ?>; color: #fff; padding: 5px 10px; border-radius: 12px; font-size: 11px;">
														<?php echo htmlspecialchars($color['color_name']) . ' (' . $color['quantity'] . ')'; ?>
													</span>
												<?php endforeach; ?>
											</div>
										<?php else: ?>
											<span class="text-muted">No colors</span>
										<?php endif; ?>
									</td>
									<td><?php echo htmlspecialchars(substr($row['description'], 0, 50) . (strlen($row['description']) > 50 ? '...' : '')); ?></td>
									<td class="actions">
										<button class="btn btn-sm btn-warning edit-product-btn"
											data-bs-toggle="modal"
											data-bs-target="#editModal"
											data-id="<?php echo $row['id']; ?>"
											data-name="<?php echo htmlspecialchars($row['name']); ?>"
											data-price="<?php echo $row['price']; ?>"
											data-description="<?php echo htmlspecialchars($row['description']); ?>"
											data-category="<?php echo htmlspecialchars($row['category']); ?>"
											data-image="<?php echo htmlspecialchars($row['image']); ?>"
											data-colors='<?php echo json_encode($colors); ?>'
											title="Edit">
											<i class="fas fa-edit"></i>
										</button>

										<a href="?delete_id=<?php echo $row['id']; ?>"
											class="btn btn-sm btn-danger"
											onclick="return confirm('Are you sure you want to delete this product?');"
											title="Delete">
											<i class="fas fa-trash"></i>
										</a>
									</td>
								</tr>
								<?php endwhile; ?>
								<?php endif; ?>
							</tbody>
						</table>
					</div>

					<?php if ($totalPages > 1): ?>
					<div class="pagination">
						<?php if ($page > 1): ?>
						<a href="?page=<?php echo ($page - 1); ?>&category=<?php echo $category; ?>&search=<?php echo urlencode($search); ?>" class="page-link"><</a>
						<?php endif; ?>

						<?php for ($i = 1; $i <= $totalPages; $i++): ?>
						<a href="?page=<?php echo $i; ?>&category=<?php echo $category; ?>&search=<?php echo urlencode($search); ?>" class="page-link <?php if ($i == $page) echo 'active'; ?>">
							<?php echo $i; ?>
						</a>
						<?php endfor; ?>

						<?php if ($page < $totalPages): ?>
						<a href="?page=<?php echo ($page + 1); ?>&category=<?php echo $category; ?>&search=<?php echo urlencode($search); ?>" class="page-link">></a>
						<?php endif; ?>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</main>

	<!-- Add Product Modal -->
	<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<form id="addProductForm" method="post" enctype="multipart/form-data">
					<input type="hidden" name="action" value="add">
					<div class="modal-body">
						<div class="mb-3">
							<label for="name" class="form-label">Product Name</label>
							<input type="text" class="form-control" id="name" name="name" required>
						</div>
						<div class="mb-3">
							<label for="price" class="form-label">Price (₦)</label>
							<input type="number" step="0.01" class="form-control" id="price" name="price" required>
						</div>
						<div class="mb-3">
							<label for="category" class="form-label">Category</label>
							<select class="form-control" id="category" name="category" required>
								<option value="">Select Category</option>
								<option value="Shaddas">1. Shaddas</option>
								<option value="Yadis">2. Yadis</option>
								<option value="Kaftanis">3. Kaftanis</option>
							</select>
						</div>
						<div class="mb-3">
							<label for="description" class="form-label">Description</label>
							<textarea class="form-control" id="description" name="description" rows="3" required></textarea>
						</div>
						<div class="mb-3">
							<label for="image" class="form-label">Product Image</label>
							<input type="file" class="form-control" id="image" name="image" required>
						</div>
						
						<!-- Colors Section -->
						<div class="mb-3">
							<label class="form-label">Colors & Stock</label>
							<div id="addColorContainer">
								<div class="color-row mb-2">
									<div class="row">
										<div class="col-md-4">
											<input type="text" class="form-control" name="color_names[]" placeholder="Color Name" required>
										</div>
										<div class="col-md-3">
											<input type="color" class="form-control" name="color_codes[]" value="#000000" required>
										</div>
										<div class="col-md-3">
											<input type="number" class="form-control" name="color_quantities[]" placeholder="Quantity" min="0" required>
										</div>
										<div class="col-md-2">
											<button type="button" class="btn btn-danger btn-sm remove-color-btn" disabled>Remove</button>
										</div>
									</div>
								</div>
							</div>
							<button type="button" class="btn btn-secondary btn-sm" id="addColorBtn">Add Another Color</button>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
						<button type="submit" class="btn btn-primary">Add Product</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<!-- Edit Product Modal -->
	<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="editModalLabel">Edit Product</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<form id="editProductForm" method="post" enctype="multipart/form-data">
					<input type="hidden" name="action" value="edit">
					<div class="modal-body">
						<input type="hidden" id="productId" name="id">
						<div class="mb-3">
							<label for="editName" class="form-label">Product Name</label>
							<input type="text" class="form-control" id="editName" name="name" required>
						</div>
						<div class="mb-3">
							<label for="editPrice" class="form-label">Price (₦)</label>
							<input type="number" step="0.01" class="form-control" id="editPrice" name="price" required>
						</div>
						<div class="mb-3">
							<label for="editCategory" class="form-label">Category</label>
							<select class="form-control" id="editCategory" name="category" required>
								<option value="">Select Category</option>
								<option value="Shaddas">1. Shaddas</option>
								<option value="Yadis">2. Yadis</option>
								<option value="Kaftanis">3. Kaftanis</option>
							</select>
						</div>
						<div class="mb-3">
							<label for="editDescription" class="form-label">Description</label>
							<textarea class="form-control" id="editDescription" name="description" rows="3" required></textarea>
						</div>
						<div class="mb-3">
							<label for="editImage" class="form-label">Product Image</label>
							<input type="file" class="form-control" id="editImage" name="image">
							<small class="form-text text-muted">Leave empty to keep the current image</small>
							<div class="mt-2">
								<label class="form-label">Current Image:</label>
								<img id="currentImage" src="" alt="Current product image" width="100" class="d-block">
							</div>
						</div>
						
						<!-- Colors Section -->
						<div class="mb-3">
							<label class="form-label">Colors & Stock</label>
							<div id="editColorContainer">
								<!-- Colors will be populated dynamically -->
							</div>
							<button type="button" class="btn btn-secondary btn-sm" id="editAddColorBtn">Add Another Color</button>
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


	<script>
		document.addEventListener('DOMContentLoaded', function() {
			// Add Color Functionality
			let addColorCount = 1;
			document.getElementById('addColorBtn').addEventListener('click', function() {
				addColorCount++;
				const colorRow = `
					<div class="color-row mb-2">
						<div class="row">
							<div class="col-md-4">
								<input type="text" class="form-control" name="color_names[]" placeholder="Color Name" required>
							</div>
							<div class="col-md-3">
								<input type="color" class="form-control" name="color_codes[]" value="#000000" required>
							</div>
							<div class="col-md-3">
								<input type="number" class="form-control" name="color_quantities[]" placeholder="Quantity" min="0" required>
							</div>
							<div class="col-md-2">
								<button type="button" class="btn btn-danger btn-sm remove-color-btn">Remove</button>
							</div>
						</div>
					</div>
				`;
				document.getElementById('addColorContainer').insertAdjacentHTML('beforeend', colorRow);
				updateRemoveButtons('addColorContainer');
			});

			// Remove Color Functionality
			document.addEventListener('click', function(e) {
				if (e.target.classList.contains('remove-color-btn')) {
					e.target.closest('.color-row').remove();
					const container = e.target.closest('[id$="ColorContainer"]');
					updateRemoveButtons(container.id);
				}
			});

			function updateRemoveButtons(containerId) {
				const container = document.getElementById(containerId);
				const removeButtons = container.querySelectorAll('.remove-color-btn');
				removeButtons.forEach((btn, index) => {
					btn.disabled = (removeButtons.length === 1);
				});
			}

			// Edit Color Functionality
			document.getElementById('editAddColorBtn').addEventListener('click', function() {
				const colorRow = `
					<div class="color-row mb-2">
						<div class="row">
							<div class="col-md-4">
								<input type="text" class="form-control" name="color_names[]" placeholder="Color Name" required>
							</div>
							<div class="col-md-3">
								<input type="color" class="form-control" name="color_codes[]" value="#000000" required>
							</div>
							<div class="col-md-3">
								<input type="number" class="form-control" name="color_quantities[]" placeholder="Quantity" min="0" required>
							</div>
							<div class="col-md-2">
								<button type="button" class="btn btn-danger btn-sm remove-color-btn">Remove</button>
							</div>
						</div>
					</div>
				`;
				document.getElementById('editColorContainer').insertAdjacentHTML('beforeend', colorRow);
				updateRemoveButtons('editColorContainer');
			});

			// Edit product modal functionality
			var editButtons = document.querySelectorAll('.edit-product-btn');
			editButtons.forEach(function(button) {
				button.addEventListener('click', function() {
					var id = this.getAttribute('data-id');
					var name = this.getAttribute('data-name');
					var price = this.getAttribute('data-price');
					var description = this.getAttribute('data-description');
					var category = this.getAttribute('data-category');
					var image = this.getAttribute('data-image');
					var colors = JSON.parse(this.getAttribute('data-colors'));

					document.getElementById('productId').value = id;
					document.getElementById('editName').value = name;
					document.getElementById('editPrice').value = price;
					document.getElementById('editDescription').value = description;
					
					// Set category
					const categorySelect = document.getElementById('editCategory');
					for (let i = 0; i < categorySelect.options.length; i++) {
						if (categorySelect.options[i].value === category) {
							categorySelect.selectedIndex = i;
							break;
						}
					}
					
					document.getElementById('currentImage').src = '../images/' + image;
					document.getElementById('currentImage').alt = name;
					document.getElementById('editModalLabel').textContent = 'Edit Product: ' + name;

					// Populate colors
					const editColorContainer = document.getElementById('editColorContainer');
					editColorContainer.innerHTML = '';
					
					if (colors.length === 0) {
						colors = [{color_name: '', color_code: '#000000', quantity: 0}];
					}
					
					colors.forEach(function(color) {
						const colorRow = `
							<div class="color-row mb-2">
								<div class="row">
									<div class="col-md-4">
										<input type="text" class="form-control" name="color_names[]" placeholder="Color Name" value="${color.color_name}" required>
									</div>
									<div class="col-md-3">
										<input type="color" class="form-control" name="color_codes[]" value="${color.color_code}" required>
									</div>
									<div class="col-md-3">
										<input type="number" class="form-control" name="color_quantities[]" placeholder="Quantity" min="0" value="${color.quantity}" required>
									</div>
									<div class="col-md-2">
										<button type="button" class="btn btn-danger btn-sm remove-color-btn">Remove</button>
									</div>
								</div>
							</div>
						`;
						editColorContainer.insertAdjacentHTML('beforeend', colorRow);
					});
					
					updateRemoveButtons('editColorContainer');
				});
			});
		});
	</script>

	<?php include 'includes/footer.php'; ?>
<?php
require_once 'includes/db_connect.php';

// Protect this page and ensure the user is a seller
if (!isset($_SESSION['loggedin']) || $_SESSION['user_role'] !== 'Seller') {
    header('Location: login.php');
    exit;
}

$seller_id = $_SESSION['user_id'];

// Get all products for this seller
$sql = "SELECT * FROM products WHERE seller_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
$products = [];

while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
$stmt->close();

// Calculate some stats
$total_products = count($products);
$total_stock = 0;
$low_stock_count = 0;
$out_of_stock = 0;

foreach ($products as $product) {
    $total_stock += $product['stock'];
    if ($product['stock'] <= 5 && $product['stock'] > 0) {
        $low_stock_count++;
    }
    if ($product['stock'] <= 0) {
        $out_of_stock++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - AgroKartBD</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/products.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar Navigation -->
        <nav class="sidebar">
            <div class="logo">
                <img src="images/AGrO.png" alt="Logo">
                <span>AgroKartBD</span>
            </div>
            <ul class="nav-menu">
                <li><a href="dashboard.php"><span class="icon"><i class="fas fa-chart-bar"></i></span>Dashboard</a></li>
                <li class="active"><a href="#"><span class="icon"><i class="fas fa-box"></i></span>Products</a></li>
                <li><a href="orders.php"><span class="icon"><i class="fas fa-shopping-cart"></i></span>Orders</a></li>
                <li><a href="customers.php"><span class="icon"><i class="fas fa-users"></i></span>Customers</a></li>
                <li><a href="php/logout.php"><span class="icon"><i class="fas fa-sign-out-alt"></i></span>Logout</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="top-header">
                <h1 class="page-title"><i class="fas fa-box"></i> Product Management</h1>
                <button class="add-product"><i class="fas fa-plus"></i> Add New Product</button>
                <div class="header-right">
                    <div class="user-profile">
                        <img src="images/profiles/user_<?php echo $_SESSION['user_id']; ?>_<?php echo time(); ?>.jpg" alt="Profile" onerror="this.src='images/AGrO.png'">
                    </div>
                </div>
            </header>

            <?php
            if (isset($_SESSION['error'])) {
                echo '<p class="error-message">' . $_SESSION['error'] . '</p>';
                unset($_SESSION['error']);
            }
            if (isset($_SESSION['message'])) {
                echo '<p class="success-message">' . $_SESSION['message'] . '</p>';
                unset($_SESSION['message']);
            }
            ?>

            <!-- Product Statistics -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="fas fa-box"></i></div><span>Total Products</span>
                    </div>
                    <div class="stat-info">
                        <h2><?php echo $total_products; ?></h2>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="fas fa-warehouse"></i></div><span>Total Stock</span>
                    </div>
                    <div class="stat-info">
                        <h2><?php echo $total_stock; ?> items</h2>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div><span>Low Stock</span>
                    </div>
                    <div class="stat-info">
                        <h2><?php echo $low_stock_count; ?> products</h2>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="fas fa-times-circle"></i></div><span>Out of Stock</span>
                    </div>
                    <div class="stat-info">
                        <h2><?php echo $out_of_stock; ?> products</h2>
                    </div>
                </div>
            </div>

            <!-- Products List -->
            <div class="product-list-container">
                <div class="list-header">
                    <h3>Our Products</h3>
                    <div class="filter-container">
                        <div class="search-container">
                            <input type="text" id="productSearch" placeholder="Search products..." class="search-input">
                            <i class="fas fa-search search-icon"></i>
                        </div>
                        <select id="categoryFilter" class="filter-select">
                            <option value="all">All Categories</option>
                            <option value="Vegetable">Vegetables</option>
                            <option value="Fruit">Fruits</option>
                            <option value="Spice">Spices</option>
                        </select>
                        <select id="stockFilter" class="filter-select">
                            <option value="all">All Stock</option>
                            <option value="in-stock">In Stock</option>
                            <option value="low-stock">Low Stock (≤5)</option>
                            <option value="out-of-stock">Out of Stock</option>
                        </select>
                    </div>
                </div>

                <?php if (count($products) > 0): ?>
                    <div class="product-grid">
                        <?php foreach ($products as $product): ?>
                            <div class="product-card"
                                data-category="<?php echo htmlspecialchars($product['category']); ?>"
                                data-stock="<?php echo $product['stock'] <= 0 ? 'out-of-stock' : ($product['stock'] <= 5 ? 'low-stock' : 'in-stock'); ?>">
                                <div class="product-actions-floating">
                                    <button class="edit-btn" onclick="editProduct(<?php echo $product['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="delete-btn" onclick="confirmDelete(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                                <div class="product-image">
                                    <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    <?php if ($product['stock'] <= 0): ?>
                                        <div class="out-of-stock-badge">Out of Stock</div>
                                    <?php elseif ($product['stock'] <= 5): ?>
                                        <div class="low-stock-badge">Low Stock</div>
                                    <?php endif; ?>
                                </div>
                                <div class="product-info">
                                    <h4 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h4>
                                    <div class="product-category"><i class="fas fa-tag"></i> <?php echo htmlspecialchars($product['category']); ?></div>
                                    <div class="product-price">
                                        <?php
                                        // Check if this product has a hot deal
                                        $hot_deal_sql = "SELECT discount_price, discount_percentage FROM hot_deals WHERE product_id = ? AND is_active = 1";
                                        $hot_deal_stmt = $conn->prepare($hot_deal_sql);
                                        $hot_deal_stmt->bind_param("i", $product['id']);
                                        $hot_deal_stmt->execute();
                                        $hot_deal_result = $hot_deal_stmt->get_result();
                                        $hot_deal = $hot_deal_result->fetch_assoc();
                                        $hot_deal_stmt->close();

                                        if ($hot_deal): ?>
                                            <span class="deal-price">৳<?php echo number_format($hot_deal['discount_price'], 2); ?></span>
                                            <span class="original-price">৳<?php echo number_format($product['price'], 2); ?></span>
                                            <span class="hot-deal-badge">HOT DEAL!</span>
                                            <?php
                                            // Display quantity and unit for hot deals
                                            if (isset($product['display_unit'])) {
                                                echo " <span class=\"unit-text\">for {$product['display_unit']}</span>";
                                            } else {
                                                $quantity = isset($product['quantity']) ? $product['quantity'] : 1;
                                                $unitDisplay = $product['unit'];
                                                echo " <span class=\"unit-text\">for {$quantity} {$unitDisplay}</span>";
                                            }
                                            ?>
                                        <?php else: ?>
                                            ৳<?php echo number_format($product['price'], 2); ?>
                                            <?php
                                            // Display quantity and unit for regular prices
                                            if (isset($product['display_unit'])) {
                                                echo " <span class=\"unit-text\">for {$product['display_unit']}</span>";
                                            } else {
                                                $quantity = isset($product['quantity']) ? $product['quantity'] : 1;
                                                $unitDisplay = $product['unit'];
                                                echo " <span class=\"unit-text\">for {$quantity} {$unitDisplay}</span>";
                                            }
                                            ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-stock"><i class="fas fa-cubes"></i> Stock: <?php echo $product['stock']; ?></div>
                                    <div class="product-date"><i class="fas fa-calendar-alt"></i> Added: <?php echo date('M d, Y', strtotime($product['created_at'])); ?></div>

                                    <div class="product-actions">
                                        <button class="btn btn-primary btn-sm" onclick="editProduct(<?php echo $product['id']; ?>)">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn <?php echo $hot_deal ? 'btn-warning' : 'btn-success'; ?> btn-sm" onclick="<?php echo $hot_deal ? 'removeHotDeal' : 'addHotDeal'; ?>(<?php echo $product['id']; ?>)">
                                            <i class="fas <?php echo $hot_deal ? 'fa-times' : 'fa-fire'; ?>"></i>
                                            <?php echo $hot_deal ? 'Remove Deal' : 'Add Hot Deal'; ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-products">
                        <div class="no-data-icon"><i class="fas fa-box-open"></i></div>
                        <h3>No Products Found</h3>
                        <p>You haven't added any products yet. Click the "Add New Product" button to get started.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Add New Product Modal -->
            <div id="addProductModal" class="modal">
                <div class="modal-content">
                    <span class="close-modal">&times;</span>
                    <h2>Add New Product</h2>
                    <form class="add-product-form" action="php/add_product_process.php" method="POST" enctype="multipart/form-data">
                        <label>Product Name</label>
                        <input type="text" name="product_name" placeholder="Enter product name" required />
                        <label>Category</label>
                        <select name="category" required>
                            <option value="">Select category</option>
                            <option value="Vegetable">Vegetable</option>
                            <option value="Fruit">Fruit</option>
                            <option value="Spice">Spice</option>
                        </select>
                        <label>Price (৳)</label>
                        <div class="price-unit-group">
                            <input type="number" name="price" placeholder="Enter price" min="0" step="0.01" required />
                        </div>

                        <label>Package Details</label>
                        <div class="package-details-group">
                            <input type="number" name="quantity" placeholder="Quantity" min="0.1" step="0.1" value="1" required />
                            <select name="unit" required>
                                <option value="kg">kg</option>
                                <option value="gm">gm</option>
                                <option value="pc">pc</option>
                                <option value="pack">pack</option>
                                <option value="dozen">dozen</option>
                                <option value="liter">liter</option>
                            </select>
                        </div>
                        <label>Stock Quantity</label>
                        <input type="number" name="stock" placeholder="Enter stock quantity" min="0" required />
                        <label>Product Image</label>
                        <input type="file" name="product_image" accept="image/*" required />
                        <label>Description</label>
                        <textarea name="description" placeholder="Enter product description" rows="3" required></textarea>
                        <button type="submit" class="submit-btn">Add Product</button>
                    </form>
                </div>
            </div>

            <!-- Edit Product Modal -->
            <div id="editProductModal" class="modal">
                <div class="modal-content">
                    <span class="close-modal">&times;</span>
                    <h2>Edit Product</h2>
                    <form class="edit-product-form" action="php/edit_product_process.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" id="edit_product_id" name="product_id" value="" />
                        <label>Product Name</label>
                        <input type="text" id="edit_product_name" name="product_name" placeholder="Enter product name" required />
                        <label>Category</label>
                        <select id="edit_category" name="category" required>
                            <option value="">Select category</option>
                            <option value="Vegetable">Vegetable</option>
                            <option value="Fruit">Fruit</option>
                            <option value="Spice">Spice</option>
                        </select>
                        <label>Price (৳)</label>
                        <div class="price-unit-group">
                            <input type="number" id="edit_price" name="price" placeholder="Enter price" min="0" step="0.01" required />
                        </div>

                        <label>Package Details</label>
                        <div class="package-details-group">
                            <input type="number" id="edit_quantity" name="quantity" placeholder="Quantity" min="0.1" step="0.1" value="1" required />
                            <select id="edit_unit" name="unit" required>
                                <option value="kg">kg</option>
                                <option value="gm">gm</option>
                                <option value="pc">pc</option>
                                <option value="pack">pack</option>
                                <option value="dozen">dozen</option>
                                <option value="liter">liter</option>
                            </select>
                        </div>
                        <label>Stock Quantity</label>
                        <input type="number" id="edit_stock" name="stock" placeholder="Enter stock quantity" min="0" required />
                        <label>Product Image</label>
                        <div class="current-image-container">
                            <img id="current_image" src="" alt="Current product image" />
                            <p class="image-note">Current image</p>
                        </div>
                        <input type="file" name="product_image" accept="image/*" />
                        <p class="image-note">Leave empty to keep the current image</p>
                        <label>Description</label>
                        <textarea id="edit_description" name="description" placeholder="Enter product description" rows="3" required></textarea>
                        <button type="submit" class="submit-btn">Update Product</button>
                    </form>
                </div>
            </div>

            <!-- Delete Confirmation Modal -->
            <div id="deleteModal" class="modal">
                <div class="modal-content">
                    <span class="close-modal">&times;</span>
                    <div class="modal-header">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h2>Confirm Deletion</h2>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete the product <strong id="productName"></strong>?</p>
                        <p class="warning-text">This action cannot be undone. This will permanently delete the product from your inventory and remove it from all buyer views.</p>
                    </div>
                    <div class="modal-footer">
                        <form action="php/delete_product.php" method="POST">
                            <input type="hidden" id="productId" name="product_id" value="">
                            <button type="button" class="cancel-btn" onclick="closeModal('deleteModal')">Cancel</button>
                            <button type="submit" class="delete-confirm-btn">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get modal elements
            const addProductModal = document.getElementById('addProductModal');
            const editProductModal = document.getElementById('editProductModal');
            const deleteModal = document.getElementById('deleteModal');
            const addProductBtn = document.querySelector('.add-product');
            const closeButtons = document.querySelectorAll('.close-modal');

            // Product cards animation
            const productCards = document.querySelectorAll('.product-card');
            productCards.forEach((card, index) => {
                setTimeout(() => {
                    card.classList.add('animated');
                }, 100 * index);
            });

            // Add Product button event
            addProductBtn.addEventListener('click', function() {
                addProductModal.style.display = 'flex';
                document.body.classList.add('modal-open');
            });

            // Close button events
            closeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    closeAllModals();
                });
            });

            // Close modal when clicking outside
            window.addEventListener('click', function(event) {
                if (event.target === addProductModal) {
                    closeAllModals();
                } else if (event.target === editProductModal) {
                    closeAllModals();
                } else if (event.target === deleteModal) {
                    closeAllModals();
                }
            });

            // Search functionality
            const searchInput = document.getElementById('productSearch');
            const categoryFilter = document.getElementById('categoryFilter');
            const stockFilter = document.getElementById('stockFilter');

            function filterProducts() {
                const searchTerm = searchInput.value.toLowerCase();
                const categoryValue = categoryFilter.value;
                const stockValue = stockFilter.value;

                productCards.forEach(card => {
                    const name = card.querySelector('.product-name').textContent.toLowerCase();
                    const category = card.dataset.category;
                    const stockStatus = card.dataset.stock;

                    const matchesSearch = name.includes(searchTerm);
                    const matchesCategory = categoryValue === 'all' || category === categoryValue;
                    const matchesStock = stockValue === 'all' || stockStatus === stockValue;

                    if (matchesSearch && matchesCategory && matchesStock) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }

            searchInput.addEventListener('input', filterProducts);
            categoryFilter.addEventListener('change', filterProducts);
            stockFilter.addEventListener('change', filterProducts);
        });

        // Delete confirmation function
        function confirmDelete(id, name) {
            const modal = document.getElementById('deleteModal');
            document.getElementById('productId').value = id;
            document.getElementById('productName').textContent = name;
            modal.style.display = 'flex';
            document.body.classList.add('modal-open');
        }

        // Edit product function
        function editProduct(id) {
            // AJAX request to get product details
            fetch(`php/get_product.php?id=${id}`)
                .then(response => response.json())
                .then(product => {
                    document.getElementById('edit_product_id').value = product.id;
                    document.getElementById('edit_product_name').value = product.name;
                    document.getElementById('edit_category').value = product.category;
                    document.getElementById('edit_price').value = product.price;
                    document.getElementById('edit_unit').value = product.unit;
                    // Set quantity if it exists, otherwise default to 1
                    document.getElementById('edit_quantity').value = product.quantity || 1;
                    document.getElementById('edit_stock').value = product.stock;
                    document.getElementById('edit_description').value = product.description;
                    document.getElementById('current_image').src = product.image_path;

                    const modal = document.getElementById('editProductModal');
                    modal.style.display = 'flex';
                    document.body.classList.add('modal-open');
                })
                .catch(error => {
                    console.error('Error fetching product details:', error);
                    alert('Error loading product details. Please try again.');
                });
        }

        // Close all modals
        function closeAllModals() {
            const modals = document.querySelectorAll('.modal');
            document.body.classList.remove('modal-open');
            modals.forEach(modal => {
                modal.style.display = 'none';
            });
        }

        // Close specific modal
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            document.body.classList.remove('modal-open');
            modal.style.display = 'none';
        }

        // Hot Deal functions
        function addHotDeal(productId) {
            // Get product details first to calculate discount
            fetch(`php/get_product.php?id=${productId}`)
                .then(response => response.json())
                .then(product => {
                    document.getElementById('hotDealProductId').value = productId;
                    document.getElementById('originalPrice').value = product.price;

                    const modal = document.getElementById('hotDealModal');
                    modal.style.display = 'flex';
                    document.body.classList.add('modal-open');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load product details');
                });
        }

        function removeHotDeal(productId) {
            if (confirm('Are you sure you want to remove this hot deal?')) {
                fetch('php/manage_hot_deals.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: `action=remove&product_id=${productId}`
                    })
                    .then(response => {
                        // Check if response is JSON
                        const contentType = response.headers.get("content-type");
                        if (contentType && contentType.indexOf("application/json") !== -1) {
                            return response.json();
                        } else {
                            // If not JSON, the page is probably redirecting
                            location.reload();
                            return {
                                success: true
                            };
                        }
                    })
                    .then(data => {
                        if (data.success) {
                            alert('Hot deal removed successfully!');
                            location.reload();
                        } else {
                            alert('Error: ' + (data.message || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to remove hot deal. Please try again.');
                    });
            }
        }

        // Calculate discount price automatically
        function calculateDiscountPrice() {
            const originalPrice = parseFloat(document.getElementById('originalPrice').value);
            const discountPercentage = parseFloat(document.getElementById('discountPercentage').value);

            if (originalPrice && discountPercentage) {
                const discountPrice = originalPrice - (originalPrice * discountPercentage / 100);
                document.getElementById('discountPrice').value = discountPrice.toFixed(2);
            }
        }
    </script>

    <!-- Hot Deal Modal -->
    <div id="hotDealModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2><i class="fas fa-fire"></i> Add Hot Deal</h2>
            <form id="hotDealForm" action="php/manage_hot_deals.php" method="POST">
                <input type="hidden" name="action" value="add">
                <input type="hidden" id="hotDealProductId" name="product_id">
                <input type="hidden" id="originalPrice" name="original_price">

                <div class="form-row">
                    <div class="form-group">
                        <label>Discount Percentage (%)</label>
                        <input type="number" id="discountPercentage" name="discount_percentage"
                            placeholder="e.g., 20" min="1" max="90"
                            onchange="calculateDiscountPrice()" required>
                    </div>
                    <div class="form-group">
                        <label>Discount Price (৳)</label>
                        <input type="number" id="discountPrice" name="discount_price"
                            placeholder="Auto-calculated" step="0.01" readonly>
                    </div>
                </div>

                <div class="form-group">
                    <label>Deal Valid Until</label>
                    <input type="datetime-local" name="valid_until" required>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('hotDealModal')">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-fire"></i> Create Hot Deal
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
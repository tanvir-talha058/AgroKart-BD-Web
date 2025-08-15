<?php
// FILE: product_comparison.php
include 'includes/header.php';

// Get comparison data
$comparison_data = null;
if (isset($_SESSION['comparison']) && !empty($_SESSION['comparison'])) {
    // Get comparison data via internal function call
    ob_start();
    $_GET['action'] = 'get_comparison_data';
    include 'php/comparison_manager.php';
    $json_output = ob_get_clean();
    $comparison_data = json_decode($json_output, true);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Comparison - AgroKartBD</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/comparison-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="comparison-container">
        <div class="comparison-header">
            <div class="header-content">
                <div class="comparison-icon">
                    <i class="fas fa-balance-scale"></i>
                </div>
                <div class="comparison-title-content">
                    <h1 class="comparison-title">Product Comparison</h1>
                    <p class="comparison-subtitle">Compare products side by side to make the best choice</p>
                </div>
            </div>
            <div class="comparison-actions">
                <button class="action-btn clear-btn" onclick="clearComparison()">
                    <i class="fas fa-trash"></i>
                    Clear All
                </button>
                <a href="index.php" class="action-btn back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Continue Shopping
                </a>
            </div>
        </div>

        <!-- Notification Container -->
        <div id="notification-container"></div>

        <?php if ($comparison_data && $comparison_data['success'] && !empty($comparison_data['products'])): ?>
            <!-- Comparison Insights -->
            <?php if (!empty($comparison_data['insights'])): ?>
                <div class="insights-section">
                    <h3><i class="fas fa-lightbulb"></i> Comparison Insights</h3>
                    <div class="insights-grid">
                        <?php foreach ($comparison_data['insights'] as $insight): ?>
                            <div class="insight-card <?php echo $insight['type']; ?>">
                                <div class="insight-icon">
                                    <?php
                                    $icons = [
                                        'price' => 'fa-dollar-sign',
                                        'savings' => 'fa-piggy-bank',
                                        'rating' => 'fa-star',
                                        'availability' => 'fa-box'
                                    ];
                                    echo '<i class="fas ' . ($icons[$insight['type']] ?? 'fa-info') . '"></i>';
                                    ?>
                                </div>
                                <div class="insight-content">
                                    <h4><?php echo htmlspecialchars($insight['title']); ?></h4>
                                    <p><?php echo htmlspecialchars($insight['message']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Comparison Table -->
            <div class="comparison-table-container">
                <div class="comparison-table">
                    <!-- Product Images Row -->
                    <div class="comparison-row header-row">
                        <div class="comparison-cell feature-cell">
                            <strong>Products</strong>
                        </div>
                        <?php foreach ($comparison_data['products'] as $product): ?>
                            <div class="comparison-cell product-cell">
                                <div class="product-image-container">
                                    <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                         class="product-image">
                                    <button class="remove-btn" onclick="removeFromComparison(<?php echo $product['id']; ?>)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Price Row -->
                    <div class="comparison-row">
                        <div class="comparison-cell feature-cell">
                            <i class="fas fa-tag"></i>
                            <strong>Price</strong>
                        </div>
                        <?php foreach ($comparison_data['products'] as $product): ?>
                            <div class="comparison-cell">
                                <div class="price-info">
                                    <span class="price">৳<?php echo number_format($product['price'], 2); ?></span>
                                    <span class="unit">per <?php echo htmlspecialchars($product['unit']); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Category Row -->
                    <div class="comparison-row">
                        <div class="comparison-cell feature-cell">
                            <i class="fas fa-folder"></i>
                            <strong>Category</strong>
                        </div>
                        <?php foreach ($comparison_data['products'] as $product): ?>
                            <div class="comparison-cell">
                                <span class="category-badge"><?php echo htmlspecialchars($product['category']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Availability Row -->
                    <div class="comparison-row">
                        <div class="comparison-cell feature-cell">
                            <i class="fas fa-box"></i>
                            <strong>Availability</strong>
                        </div>
                        <?php foreach ($comparison_data['products'] as $product): ?>
                            <div class="comparison-cell">
                                <span class="availability-badge <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                                    <i class="fas <?php echo $product['stock'] > 0 ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                                    <?php echo $product['availability']; ?>
                                    <?php if ($product['stock'] > 0): ?>
                                        (<?php echo $product['stock']; ?> available)
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Rating Row -->
                    <div class="comparison-row">
                        <div class="comparison-cell feature-cell">
                            <i class="fas fa-star"></i>
                            <strong>Rating</strong>
                        </div>
                        <?php foreach ($comparison_data['products'] as $product): ?>
                            <div class="comparison-cell">
                                <div class="rating-info">
                                    <?php if ($product['avg_rating'] > 0): ?>
                                        <div class="stars">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= $product['avg_rating'] ? 'filled' : 'empty'; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="rating-text">
                                            <?php echo $product['avg_rating']; ?>/5 
                                            (<?php echo $product['review_count']; ?> reviews)
                                        </span>
                                    <?php else: ?>
                                        <span class="no-rating">No ratings yet</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Description Row -->
                    <div class="comparison-row">
                        <div class="comparison-cell feature-cell">
                            <i class="fas fa-info-circle"></i>
                            <strong>Description</strong>
                        </div>
                        <?php foreach ($comparison_data['products'] as $product): ?>
                            <div class="comparison-cell">
                                <div class="description-text">
                                    <?php echo nl2br(htmlspecialchars(substr($product['description'], 0, 150))); ?>
                                    <?php if (strlen($product['description']) > 150): ?>
                                        <span class="more-text">...</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Actions Row -->
                    <div class="comparison-row action-row">
                        <div class="comparison-cell feature-cell">
                            <strong>Actions</strong>
                        </div>
                        <?php foreach ($comparison_data['products'] as $product): ?>
                            <div class="comparison-cell">
                                <div class="product-actions">
                                    <?php if ($product['stock'] > 0): ?>
                                        <button class="action-btn add-cart-btn" onclick="addToCart(<?php echo $product['id']; ?>)">
                                            <i class="fas fa-shopping-cart"></i>
                                            Add to Cart
                                        </button>
                                    <?php endif; ?>
                                    
                                    <button class="action-btn wishlist-btn" onclick="addToWishlist(<?php echo $product['id']; ?>)">
                                        <i class="far fa-heart"></i>
                                        Wishlist
                                    </button>
                                    
                                    <a href="product_details.php?id=<?php echo $product['id']; ?>" class="action-btn view-btn">
                                        <i class="fas fa-eye"></i>
                                        View Details
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- Empty Comparison State -->
            <div class="empty-comparison">
                <div class="empty-icon">
                    <i class="fas fa-balance-scale"></i>
                </div>
                <h2>No products to compare</h2>
                <p>Add products to comparison from the product pages to see detailed side-by-side comparison.</p>
                <div class="empty-actions">
                    <a href="index.php" class="action-btn shop-btn">
                        <i class="fas fa-shopping-bag"></i>
                        Start Shopping
                    </a>
                    <button class="action-btn guide-btn" onclick="showComparisonGuide()">
                        <i class="fas fa-question-circle"></i>
                        How to Compare
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <!-- Comparison Guide Modal -->
        <div id="guide-modal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3><i class="fas fa-info-circle"></i> How to Use Product Comparison</h3>
                    <button class="close-btn" onclick="closeComparisonGuide()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="guide-steps">
                        <div class="guide-step">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h4>Browse Products</h4>
                                <p>Visit product pages and look for the "Compare" button</p>
                            </div>
                        </div>
                        <div class="guide-step">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h4>Add to Comparison</h4>
                                <p>Click the "Compare" button to add products (max 4)</p>
                            </div>
                        </div>
                        <div class="guide-step">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h4>Compare Features</h4>
                                <p>View side-by-side comparison of prices, ratings, and features</p>
                            </div>
                        </div>
                        <div class="guide-step">
                            <div class="step-number">4</div>
                            <div class="step-content">
                                <h4>Make Decision</h4>
                                <p>Use insights and comparison data to choose the best product</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/product-comparison.js"></script>
</body>
</html>

<?php
include 'includes/footer.php';
?>

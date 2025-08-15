<?php
// FILE: product_details.php

// Check if product ID is provided
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}
$product_id = $_GET['id'];

// Include header after initial check
include 'includes/header.php';

// Track recently viewed product
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Remove old entry if exists
    $deleteStmt = $conn->prepare("DELETE FROM recently_viewed WHERE user_id = ? AND product_id = ?");
    $deleteStmt->bind_param("ii", $user_id, $product_id);
    $deleteStmt->execute();
    
    // Add new entry
    $insertStmt = $conn->prepare("INSERT INTO recently_viewed (user_id, product_id) VALUES (?, ?)");
    $insertStmt->bind_param("ii", $user_id, $product_id);
    $insertStmt->execute();
    
    // Keep only last 20 viewed products
    $cleanupStmt = $conn->prepare("
        DELETE FROM recently_viewed 
        WHERE user_id = ? 
        AND id NOT IN (
            SELECT id FROM (
                SELECT id FROM recently_viewed 
                WHERE user_id = ? 
                ORDER BY viewed_at DESC 
                LIMIT 20
            ) tmp
        )
    ");
    $cleanupStmt->bind_param("ii", $user_id, $user_id);
    $cleanupStmt->execute();
}

// Fetch Product Details
$stmt_prod = $conn->prepare("SELECT p.*, u.name as seller_name FROM products p JOIN users u ON p.seller_id = u.id WHERE p.id = ?");
$stmt_prod->bind_param("i", $product_id);
$stmt_prod->execute();
$product_result = $stmt_prod->get_result();
if ($product_result->num_rows === 0) {
    header('Location: index.php');
    exit;
}
$product = $product_result->fetch_assoc();
$stmt_prod->close();

// Fetch Reviews
$stmt_rev = $conn->prepare("SELECT r.*, u.name as reviewer_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.created_at DESC");
$stmt_rev->bind_param("i", $product_id);
$stmt_rev->execute();
$reviews_result = $stmt_rev->get_result();

// Calculate average rating
$avg_rating = 0;
$total_reviews = 0;
if ($reviews_result->num_rows > 0) {
    $reviews_result->data_seek(0);
    $total_rating = 0;
    while ($review = $reviews_result->fetch_assoc()) {
        $total_rating += $review['rating'];
        $total_reviews++;
    }
    $avg_rating = $total_rating / $total_reviews;
    $reviews_result->data_seek(0);
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="product-details-page">
    <!-- Breadcrumb Navigation -->
    <div class="breadcrumb-container">
        <nav class="breadcrumb">
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
            <span class="breadcrumb-separator">></span>
            <span class="current-page"><?php echo htmlspecialchars($product['name']); ?></span>
        </nav>
    </div>

    <!-- Product Details Section -->
    <div class="product-details-container">
        <div class="product-image-section">
            <div class="product-image-wrapper">
                <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="main-product-image">
                <div class="image-overlay">
                    <i class="fas fa-search-plus zoom-icon"></i>
                </div>
            </div>
        </div>

        <div class="product-info-section">
            <div class="product-header">
                <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                <div class="product-rating">
                    <?php if ($total_reviews > 0): ?>
                        <div class="stars">
                            <?php
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= floor($avg_rating)) {
                                    echo '<i class="fas fa-star"></i>';
                                } elseif ($i <= ceil($avg_rating)) {
                                    echo '<i class="fas fa-star-half-alt"></i>';
                                } else {
                                    echo '<i class="far fa-star"></i>';
                                }
                            }
                            ?>
                        </div>
                        <span class="rating-text">(<?php echo number_format($avg_rating, 1); ?> out of 5 - <?php echo $total_reviews; ?> reviews)</span>
                    <?php else: ?>
                        <span class="no-rating">No reviews yet</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="seller-info">
                <i class="fas fa-store"></i>
                <span>Sold by: <strong><?php echo htmlspecialchars($product['seller_name']); ?></strong></span>
            </div>

            <div class="price-section">
                <span class="current-price">৳<?php echo number_format($product['price'], 2); ?> <span class="per-unit">per <?php echo htmlspecialchars($product['unit']); ?></span></span>
            </div>

            <div class="stock-section">
                <div class="stock-indicator <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                    <i class="fas <?php echo $product['stock'] > 0 ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                    <span><?php echo $product['stock'] > 0 ? 'In Stock (' . $product['stock'] . ' available)' : 'Out of Stock'; ?></span>
                </div>
            </div>

            <div class="product-description">
                <h3><i class="fas fa-info-circle"></i> Product Description</h3>
                <div class="description-content">
                    <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                </div>
            </div>

            <?php if ($product['stock'] > 0): ?>
                <div class="purchase-section">
                    <form action="php/cart_manager.php" method="POST" class="add-to-cart-form">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="ajax" value="1">

                        <div class="quantity-selector">
                            <label for="quantity"><i class="fas fa-sort-numeric-up"></i> Quantity:</label>
                            <div class="quantity-controls">
                                <button type="button" class="qty-btn" onclick="decreaseQuantity()">-</button>
                                <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" readonly>
                                <button type="button" class="qty-btn" onclick="increaseQuantity()">+</button>
                            </div>
                        </div>

                        <div class="action-buttons">
                            <button type="submit" class="add-to-cart-btn">
                                <i class="fas fa-shopping-cart"></i>
                                <span>Add to Cart</span>
                            </button>
                            <?php if (isset($_SESSION['loggedin'])): ?>
                                <button type="button" class="wishlist-btn wishlist-toggle-btn" data-product-id="<?php echo $product_id; ?>">
                                    <i class="far fa-heart"></i>
                                </button>
                                <button type="button" class="compare-btn" onclick="addToComparison(<?php echo $product_id; ?>)">
                                    <i class="fas fa-balance-scale"></i>
                                </button>
                            <?php else: ?>
                                <a href="login.php" class="wishlist-btn" title="Login to add to wishlist">
                                    <i class="far fa-heart"></i>
                                </a>
                                <a href="login.php" class="compare-btn" title="Login to compare products">
                                    <i class="fas fa-balance-scale"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Reviews Section -->
    <div class="reviews-section">
        <div class="reviews-header">
            <h2><i class="fas fa-comments"></i> Customer Reviews</h2>
            <?php if ($total_reviews > 0): ?>
                <div class="reviews-summary">
                    <div class="average-rating">
                        <span class="rating-number"><?php echo number_format($avg_rating, 1); ?></span>
                        <div class="rating-stars">
                            <?php
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= floor($avg_rating)) {
                                    echo '<i class="fas fa-star"></i>';
                                } elseif ($i <= ceil($avg_rating)) {
                                    echo '<i class="fas fa-star-half-alt"></i>';
                                } else {
                                    echo '<i class="far fa-star"></i>';
                                }
                            }
                            ?>
                        </div>
                        <span class="total-reviews"><?php echo $total_reviews; ?> reviews</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if (isset($_SESSION['loggedin'])): ?>
            <div class="review-form-container">
                <div class="review-form">
                    <h3><i class="fas fa-edit"></i> Write a Review</h3>
                    <?php
                    if (isset($_SESSION['error'])) {
                        echo '<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i>' . $_SESSION['error'] . '</div>';
                        unset($_SESSION['error']);
                    }
                    if (isset($_SESSION['message'])) {
                        echo '<div class="alert alert-success"><i class="fas fa-check-circle"></i>' . $_SESSION['message'] . '</div>';
                        unset($_SESSION['message']);
                    }
                    ?>
                    <form action="php/review_process.php" method="POST">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">

                        <div class="form-group">
                            <label>Your Rating:</label>
                            <div class="rating-input">
                                <input type="radio" id="star5" name="rating" value="5" /><label for="star5" title="5 stars"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star4" name="rating" value="4" /><label for="star4" title="4 stars"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star3" name="rating" value="3" /><label for="star3" title="3 stars"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star2" name="rating" value="2" /><label for="star2" title="2 stars"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star1" name="rating" value="1" /><label for="star1" title="1 star"><i class="fas fa-star"></i></label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="comment">Your Review:</label>
                            <textarea name="comment" id="comment" placeholder="Share your thoughts about this product..." rows="4" required></textarea>
                        </div>

                        <button type="submit" class="submit-review-btn">
                            <i class="fas fa-paper-plane"></i>
                            Submit Review
                        </button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="login-prompt">
                <i class="fas fa-sign-in-alt"></i>
                <p>Please <a href="login.php">login</a> to write a review.</p>
            </div>
        <?php endif; ?>

        <div class="reviews-list">
            <?php if ($reviews_result->num_rows > 0): ?>
                <?php while ($review = $reviews_result->fetch_assoc()): ?>
                    <div class="review-item">
                        <div class="review-header">
                            <div class="reviewer-info">
                                <div class="reviewer-avatar">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                                <div class="reviewer-details">
                                    <span class="reviewer-name"><?php echo htmlspecialchars($review['reviewer_name']); ?></span>
                                    <span class="review-date"><?php echo date('F j, Y', strtotime($review['created_at'])); ?></span>
                                </div>
                            </div>
                            <div class="review-rating">
                                <?php
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $review['rating']) {
                                        echo '<i class="fas fa-star"></i>';
                                    } else {
                                        echo '<i class="far fa-star"></i>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <div class="review-content">
                            <p><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-reviews">
                    <i class="fas fa-comments"></i>
                    <h3>No reviews yet</h3>
                    <p>Be the first to review this product!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Notification Container -->
<div id="notification-container"></div>

<style>
    /* Product Details Page Styles */
    .product-details-page {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
        padding: 20px 0;
    }

    .breadcrumb-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .breadcrumb {
        background: rgba(255, 255, 255, 0.9);
        padding: 12px 20px;
        border-radius: 25px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        backdrop-filter: blur(10px);
    }

    .breadcrumb a {
        color: #4CAF50;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.3s ease;
    }

    .breadcrumb a:hover {
        color: #2E7D32;
    }

    .breadcrumb-separator {
        margin: 0 10px;
        color: #666;
    }

    .current-page {
        color: #333;
        font-weight: 600;
    }

    .product-details-container {
        max-width: 1200px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        padding: 30px;
        background: rgba(255, 255, 255, 0.95);
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        backdrop-filter: blur(10px);
        margin-bottom: 30px;
    }

    .product-image-section {
        position: relative;
    }

    .product-image-wrapper {
        position: relative;
        overflow: hidden;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        transition: transform 0.3s ease;
    }

    .product-image-wrapper:hover {
        transform: scale(1.02);
    }

    .main-product-image {
        width: 100%;
        height: 500px;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .image-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .product-image-wrapper:hover .image-overlay {
        opacity: 1;
    }

    .zoom-icon {
        color: white;
        font-size: 2rem;
        cursor: pointer;
    }

    .product-info-section {
        padding: 20px;
    }

    .product-header {
        margin-bottom: 20px;
    }

    .product-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #2C3E50;
        margin: 0 0 15px 0;
        line-height: 1.2;
    }

    .product-rating {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 15px;
    }

    .stars {
        color: #FFD700;
        font-size: 1.2rem;
    }

    .rating-text {
        color: #666;
        font-size: 0.9rem;
    }

    .no-rating {
        color: #999;
        font-style: italic;
    }

    .seller-info {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 20px;
        padding: 12px;
        background: #f8f9fa;
        border-radius: 10px;
        border-left: 4px solid #4CAF50;
    }

    .seller-info i {
        color: #4CAF50;
    }

    .price-section {
        margin-bottom: 20px;
    }

    .current-price {
        font-size: 2.5rem;
        font-weight: 700;
        color: #4CAF50;
        text-shadow: 0 2px 4px rgba(76, 175, 80, 0.3);
    }

    .stock-section {
        margin-bottom: 25px;
    }

    .stock-indicator {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 16px;
        border-radius: 25px;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .stock-indicator.in-stock {
        background: linear-gradient(135deg, #d4edda, #c3e6cb);
        color: #155724;
        border: 2px solid #b8dacc;
    }

    .stock-indicator.out-of-stock {
        background: linear-gradient(135deg, #f8d7da, #f1b0b7);
        color: #721c24;
        border: 2px solid #f1b0b7;
    }

    .product-description {
        margin-bottom: 30px;
        max-width: 100%;
    }

    .product-description h3 {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #2C3E50;
        margin-bottom: 15px;
        font-size: 1.3rem;
    }

    .product-description p {
        line-height: 1.6;
        color: #555;
        font-size: 1.1rem;
        overflow-wrap: break-word;
        word-wrap: break-word;
        hyphens: auto;
        max-width: 100%;
    }

    .description-content {
        max-width: 100%;
        overflow-wrap: break-word;
        word-wrap: break-word;
        word-break: break-word;
        hyphens: auto;
    }

    .purchase-section {
        background: #f8f9fa;
        padding: 25px;
        border-radius: 15px;
        border: 2px solid #e9ecef;
    }

    .quantity-selector {
        margin-bottom: 20px;
    }

    .quantity-selector label {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px;
        font-weight: 600;
        color: #2C3E50;
    }

    .quantity-controls {
        display: flex;
        align-items: center;
        gap: 0;
        width: fit-content;
        border: 2px solid #4CAF50;
        border-radius: 25px;
        overflow: hidden;
        background: white;
        /* Ensure background is white */
    }

    .qty-btn {
        background: #4CAF50;
        color: white;
        border: none;
        padding: 12px 16px;
        cursor: pointer;
        font-size: 1.2rem;
        font-weight: bold;
        transition: background 0.3s ease;
        min-width: 40px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .quantity-controls input {
        border: none;
        padding: 12px 10px;
        text-align: center;
        font-size: 1.1rem;
        font-weight: 600;
        width: 60px;
        background: white;
        color: #333;
        /* Ensure text color has good contrast */
        margin: 0;
        /* Remove any default margins */
    }

    /* Add this to ensure the input is visible and has consistent styling */
    #quantity {
        -moz-appearance: textfield;
        appearance: textfield;
        /* Standard property for compatibility */
        /* Remove spinner for Firefox */
    }

    #quantity::-webkit-outer-spin-button,
    #quantity::-webkit-inner-spin-button {
        -webkit-appearance: none;
        appearance: none;
        margin: 0;
    }

    .action-buttons {
        display: flex;
        gap: 15px;
    }

    .add-to-cart-btn {
        flex: 1;
        background: linear-gradient(135deg, #4CAF50, #45a049);
        color: white;
        border: none;
        padding: 15px 25px;
        border-radius: 25px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(76, 175, 80, 0.4);
    }

    .add-to-cart-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(76, 175, 80, 0.6);
    }

    .add-to-cart-btn:active {
        transform: translateY(0);
    }

    .add-to-cart-btn.loading {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .wishlist-btn {
        background: white;
        color: #e91e63;
        border: 2px solid #e91e63;
        padding: 15px;
        border-radius: 50%;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
    }

    .wishlist-btn:hover {
        background: #e91e63;
        color: white;
        transform: scale(1.1);
    }

    .wishlist-btn.active {
        background: #e91e63;
        color: white;
    }

    .compare-btn {
        background: white;
        color: #2196F3;
        border: 2px solid #2196F3;
        padding: 15px;
        border-radius: 50%;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
    }

    .compare-btn:hover {
        background: #2196F3;
        color: white;
        transform: scale(1.1);
    }

    /* Reviews Section */
    .reviews-section {
        max-width: 1200px;
        margin: 0 auto;
        background: rgba(255, 255, 255, 0.95);
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        backdrop-filter: blur(10px);
    }

    .reviews-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #f0f0f0;
    }

    .reviews-header h2 {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #2C3E50;
        margin: 0;
        font-size: 2rem;
    }

    .reviews-summary {
        text-align: center;
    }

    .average-rating {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 5px;
    }

    .rating-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: #4CAF50;
    }

    .rating-stars {
        color: #FFD700;
        font-size: 1.5rem;
    }

    .total-reviews {
        color: #666;
        font-size: 0.9rem;
    }

    .review-form-container {
        margin-bottom: 40px;
    }

    .review-form {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        padding: 25px;
        border-radius: 15px;
        border: 2px solid #dee2e6;
    }

    .review-form h3 {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #2C3E50;
        margin-bottom: 20px;
    }

    .alert {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #2C3E50;
    }

    .rating-input {
        display: flex;
        flex-direction: row-reverse;
        justify-content: flex-end;
        gap: 5px;
    }

    .rating-input input {
        display: none;
    }

    .rating-input label {
        cursor: pointer;
        font-size: 2rem;
        color: #ddd;
        transition: color 0.2s ease;
    }

    .rating-input label:hover,
    .rating-input label:hover~label,
    .rating-input input:checked~label {
        color: #FFD700;
    }

    .form-group textarea {
        width: 100%;
        padding: 15px;
        border: 2px solid #dee2e6;
        border-radius: 10px;
        font-family: inherit;
        font-size: 1rem;
        resize: vertical;
        transition: border-color 0.3s ease;
    }

    .form-group textarea:focus {
        outline: none;
        border-color: #4CAF50;
        box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
    }

    .submit-review-btn {
        background: linear-gradient(135deg, #4CAF50, #45a049);
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 25px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(76, 175, 80, 0.4);
    }

    .submit-review-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(76, 175, 80, 0.6);
    }

    .login-prompt {
        text-align: center;
        padding: 30px;
        background: #f8f9fa;
        border-radius: 15px;
        margin-bottom: 30px;
    }

    .login-prompt i {
        font-size: 3rem;
        color: #4CAF50;
        margin-bottom: 15px;
    }

    .login-prompt a {
        color: #4CAF50;
        text-decoration: none;
        font-weight: 600;
    }

    .login-prompt a:hover {
        text-decoration: underline;
    }

    .reviews-list {
        /* Replace space-y with proper spacing */
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .review-item {
        background: white;
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        border: 1px solid #f0f0f0;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .review-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    }

    .review-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .reviewer-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .reviewer-avatar {
        font-size: 2.5rem;
        color: #4CAF50;
    }

    .reviewer-details {
        display: flex;
        flex-direction: column;
    }

    .reviewer-name {
        font-weight: 600;
        color: #2C3E50;
        font-size: 1.1rem;
    }

    .review-date {
        color: #666;
        font-size: 0.9rem;
    }

    .review-rating {
        color: #FFD700;
        font-size: 1.2rem;
    }

    .review-content p {
        line-height: 1.6;
        color: #555;
        margin: 0;
        font-size: 1rem;
    }

    .no-reviews {
        text-align: center;
        padding: 60px 20px;
        color: #666;
    }

    .no-reviews i {
        font-size: 4rem;
        color: #ddd;
        margin-bottom: 20px;
    }

    .no-reviews h3 {
        margin: 0 0 10px 0;
        color: #999;
    }

    /* Notification Styles */
    #notification-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
    }

    .notification {
        background: white;
        padding: 15px 20px;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 300px;
        animation: slideIn 0.3s ease;
    }

    .notification.success {
        border-left: 4px solid #4CAF50;
    }

    .notification.error {
        border-left: 4px solid #f44336;
    }

    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .product-details-container {
            grid-template-columns: 1fr;
            gap: 20px;
            padding: 20px;
        }

        .product-title {
            font-size: 2rem;
        }

        .current-price {
            font-size: 2rem;
        }

        .reviews-header {
            flex-direction: column;
            gap: 20px;
            text-align: center;
        }

        .action-buttons {
            flex-direction: column;
        }

        .wishlist-btn {
            align-self: center;
        }
    }
</style>

<script>
    function increaseQuantity() {
        const input = document.getElementById('quantity');
        const max = parseInt(input.getAttribute('max'));
        const current = parseInt(input.value);
        if (current < max) {
            input.value = current + 1;
        }
    }

    function decreaseQuantity() {
        const input = document.getElementById('quantity');
        const current = parseInt(input.value);
        if (current > 1) {
            input.value = current - 1;
        }
    }

    // Enhanced Add to Cart with AJAX
    document.addEventListener('DOMContentLoaded', function() {
        const addToCartForm = document.querySelector('.add-to-cart-form');
        if (addToCartForm) {
            addToCartForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const button = this.querySelector('.add-to-cart-btn');
                const originalText = button.innerHTML;

                // Show loading state
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
                button.classList.add('loading');
                button.disabled = true;

                // Prepare form data
                const formData = new FormData(this);

                fetch('php/cart_manager.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('Product added to cart successfully!', 'success');
                            // Update cart counter if exists
                            const cartIcon = document.querySelector('.cart-icon');
                            if (cartIcon && data.cart_count) {
                                cartIcon.setAttribute('data-count', data.cart_count);
                            }
                        } else {
                            showNotification(data.message || 'Failed to add product to cart', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('An error occurred. Please try again.', 'error');
                    })
                    .finally(() => {
                        // Restore button state
                        button.innerHTML = originalText;
                        button.classList.remove('loading');
                        button.disabled = false;
                    });
            });
        }
    });

    function showNotification(message, type) {
        const container = document.getElementById('notification-container');
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;

        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        notification.innerHTML = `
        <i class="fas ${icon}"></i>
        <span>${message}</span>
    `;

        container.appendChild(notification);

        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 5000);
    }

    // Add to comparison function
    function addToComparison(productId) {
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('action', 'add');
        
        fetch('php/comparison_manager.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                updateComparisonCount(data.comparison_count);
            } else {
                showNotification(data.message || 'Failed to add to comparison', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred. Please try again.', 'error');
        });
    }

    // Update comparison count in header
    function updateComparisonCount(count) {
        const comparisonBadge = document.getElementById('comparison-count');
        if (comparisonBadge) {
            comparisonBadge.textContent = count;
            comparisonBadge.style.display = count > 0 ? 'block' : 'none';
        }
    }

    // Check wishlist status on page load
    document.addEventListener('DOMContentLoaded', function() {
        const wishlistBtn = document.querySelector('.wishlist-toggle-btn');
        if (wishlistBtn) {
            const productId = wishlistBtn.getAttribute('data-product-id');
            checkWishlistStatus(productId);
        }
        
        // Initialize wishlist functionality
        if (wishlistBtn) {
            wishlistBtn.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                toggleWishlist(productId, this);
            });
        }
    });

    // Check wishlist status
    function checkWishlistStatus(productId) {
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('action', 'check_status');
        
        fetch('php/wishlist_manager.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.is_in_wishlist) {
                const wishlistBtn = document.querySelector('.wishlist-toggle-btn');
                if (wishlistBtn) {
                    const icon = wishlistBtn.querySelector('i');
                    icon.className = 'fas fa-heart';
                    wishlistBtn.classList.add('active');
                }
            }
        })
        .catch(error => {
            console.error('Error checking wishlist status:', error);
        });
    }

    // Toggle wishlist
    function toggleWishlist(productId, button) {
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('action', 'toggle');
        
        fetch('php/wishlist_manager.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const icon = button.querySelector('i');
                if (data.is_in_wishlist) {
                    icon.className = 'fas fa-heart';
                    button.classList.add('active');
                    showNotification('Added to wishlist!', 'success');
                } else {
                    icon.className = 'far fa-heart';
                    button.classList.remove('active');
                    showNotification('Removed from wishlist!', 'success');
                }
                
                // Update wishlist count
                updateWishlistCount(data.wishlist_count);
            } else {
                showNotification(data.message || 'Please log in to manage wishlist', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred. Please try again.', 'error');
        });
    }

    // Update wishlist count in header
    function updateWishlistCount(count) {
        const wishlistBadge = document.getElementById('wishlist-count');
        if (wishlistBadge) {
            wishlistBadge.textContent = count;
            wishlistBadge.style.display = count > 0 ? 'block' : 'none';
        }
    }

    // Add slideOut animation
    const style = document.createElement('style');
    style.textContent = `
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
    document.head.appendChild(style);
</script>

<?php
$stmt_rev->close();
include 'includes/footer.php';
?>
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

// Fetch Product Details
$stmt_prod = $conn->prepare("SELECT p.*, u.name as seller_name FROM products p JOIN users u ON p.seller_id = u.id WHERE p.id = ?");
$stmt_prod->bind_param("i", $product_id);
$stmt_prod->execute();
$product_result = $stmt_prod->get_result();
if ($product_result->num_rows === 0) { header('Location: index.php'); exit; }
$product = $product_result->fetch_assoc();
$stmt_prod->close();

// Fetch Reviews
$stmt_rev = $conn->prepare("SELECT r.*, u.name as reviewer_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.created_at DESC");
$stmt_rev->bind_param("i", $product_id);
$stmt_rev->execute();
$reviews_result = $stmt_rev->get_result();
?>
<div class="product-details-container">
    <div class="product-image-section">
        <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
    </div>
    <div class="product-info-section">
        <h1><?php echo htmlspecialchars($product['name']); ?></h1>
        <p class="seller">Sold by: <?php echo htmlspecialchars($product['seller_name']); ?></p>
        <p class="price-details">৳<?php echo htmlspecialchars($product['price']); ?></p>
        <p class="stock <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
            <?php echo $product['stock'] > 0 ? 'In Stock (' . $product['stock'] . ' available)' : 'Out of Stock'; ?>
        </p>
        <p class="description"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
        
        <?php if ($product['stock'] > 0): ?>
        <form action="php/cart_manager.php" method="POST" class="add-to-cart-form">
            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
            <input type="hidden" name="action" value="add">
            <label for="quantity">Quantity:</label>
            <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
            <button type="submit" class="btn-primary">Add to Cart</button>
        </form>
        <?php endif; ?>
    </div>
</div>

<div class="reviews-section">
    <h2>Customer Reviews</h2>
    <?php if (isset($_SESSION['loggedin'])): ?>
    <div class="review-form">
        <h3>Write a Review</h3>
        <?php
        if (isset($_SESSION['error'])) { echo '<p class="error-message">' . $_SESSION['error'] . '</p>'; unset($_SESSION['error']); }
        if (isset($_SESSION['message'])) { echo '<p class="success-message">' . $_SESSION['message'] . '</p>'; unset($_SESSION['message']); }
        ?>
        <form action="php/review_process.php" method="POST">
            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
            <div class="rating">
                <input type="radio" id="star5" name="rating" value="5" /><label for="star5" title="5 stars">★</label>
                <input type="radio" id="star4" name="rating" value="4" /><label for="star4" title="4 stars">★</label>
                <input type="radio" id="star3" name="rating" value="3" /><label for="star3" title="3 stars">★</label>
                <input type="radio" id="star2" name="rating" value="2" /><label for="star2" title="2 stars">★</label>
                <input type="radio" id="star1" name="rating" value="1" /><label for="star1" title="1 star">★</label>
            </div>
            <textarea name="comment" placeholder="Share your thoughts..." rows="4" required></textarea>
            <button type="submit" class="btn-primary">Submit Review</button>
        </form>
    </div>
    <?php endif; ?>

    <div class="reviews-list">
        <?php if ($reviews_result->num_rows > 0): ?>
            <?php while($review = $reviews_result->fetch_assoc()): ?>
            <div class="review-item">
                <div class="review-header">
                    <span class="reviewer-name"><?php echo htmlspecialchars($review['reviewer_name']); ?></span>
                    <span class="review-rating"><?php echo str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']); ?></span>
                </div>
                <p class="review-comment"><?php echo htmlspecialchars($review['comment']); ?></p>
                <p class="review-date"><?php echo date('F j, Y', strtotime($review['created_at'])); ?></p>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No reviews yet. Be the first to review this product!</p>
        <?php endif; ?>
    </div>
</div>
<?php 
$stmt_rev->close();
include 'includes/footer.php'; 
?>
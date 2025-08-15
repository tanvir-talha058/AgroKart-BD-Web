<?php
// FILE: wishlist.php
include 'includes/header.php';

// Redirect if not logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get wishlist items
$stmt = $conn->prepare("
    SELECT w.*, p.name, p.price, p.unit, p.image_path, p.stock, p.category, p.id as product_id
    FROM wishlist w
    JOIN products p ON w.product_id = p.id
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$wishlist_items = $stmt->get_result();
$total_items = $wishlist_items->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - AgroKartBD</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/wishlist-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="wishlist-container">
        <div class="wishlist-header">
            <div class="header-content">
                <div class="wishlist-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="wishlist-title-content">
                    <h1 class="wishlist-title">My Wishlist</h1>
                    <p class="wishlist-subtitle">Your favorite products saved for later</p>
                </div>
            </div>
            <div class="wishlist-stats">
                <div class="stat-item">
                    <i class="fas fa-heart"></i>
                    <span><?php echo $total_items; ?> Items</span>
                </div>
            </div>
        </div>

        <!-- Notification Container -->
        <div id="notification-container"></div>

        <?php if ($total_items > 0): ?>
            <div class="wishlist-actions">
                <button class="action-btn clear-all-btn" onclick="clearAllWishlist()">
                    <i class="fas fa-trash"></i>
                    Clear All
                </button>
                <button class="action-btn add-all-cart-btn" onclick="addAllToCart()">
                    <i class="fas fa-shopping-cart"></i>
                    Add All to Cart
                </button>
            </div>

            <div class="wishlist-grid">
                <?php while ($item = $wishlist_items->fetch_assoc()): ?>
                    <div class="wishlist-card" data-product-id="<?php echo $item['product_id']; ?>">
                        <div class="product-image-container">
                            <img src="<?php echo htmlspecialchars($item['image_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                 class="product-image">
                            <div class="product-overlay">
                                <a href="product_details.php?id=<?php echo $item['product_id']; ?>" class="view-btn">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                            <?php if ($item['stock'] <= 0): ?>
                                <div class="stock-badge out-of-stock">
                                    <i class="fas fa-times-circle"></i>
                                    Out of Stock
                                </div>
                            <?php elseif ($item['stock'] <= 5): ?>
                                <div class="stock-badge limited-stock">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Limited Stock
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="product-info">
                            <h3 class="product-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                            <div class="product-details">
                                <div class="price-info">
                                    <span class="price">৳<?php echo number_format($item['price'], 2); ?></span>
                                    <span class="unit">per <?php echo htmlspecialchars($item['unit']); ?></span>
                                </div>
                                <div class="category-badge">
                                    <?php echo htmlspecialchars($item['category']); ?>
                                </div>
                            </div>
                            <div class="added-date">
                                Added on <?php echo date('M j, Y', strtotime($item['created_at'])); ?>
                            </div>
                        </div>

                        <div class="card-actions">
                            <?php if ($item['stock'] > 0): ?>
                                <button class="action-btn add-to-cart-btn" onclick="addToCart(<?php echo $item['product_id']; ?>)">
                                    <i class="fas fa-shopping-cart"></i>
                                    Add to Cart
                                </button>
                            <?php else: ?>
                                <button class="action-btn notify-btn" onclick="requestNotification(<?php echo $item['product_id']; ?>)">
                                    <i class="fas fa-bell"></i>
                                    Notify When Available
                                </button>
                            <?php endif; ?>
                            
                            <button class="action-btn remove-btn" onclick="removeFromWishlist(<?php echo $item['product_id']; ?>)">
                                <i class="fas fa-trash"></i>
                                Remove
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

        <?php else: ?>
            <div class="empty-wishlist">
                <div class="empty-icon">
                    <i class="fas fa-heart-broken"></i>
                </div>
                <h2>Your wishlist is empty</h2>
                <p>Start browsing and add products you love to your wishlist!</p>
                <a href="index.php" class="shop-now-btn">
                    <i class="fas fa-shopping-bag"></i>
                    Start Shopping
                </a>
            </div>
        <?php endif; ?>

        <!-- Recommendations Section -->
        <div class="recommendations-section">
            <h3><i class="fas fa-magic"></i> You might also like</h3>
            <div class="recommendations-grid" id="recommendations-grid">
                <!-- Recommendations will be loaded via JavaScript -->
            </div>
        </div>
    </div>

    <script src="js/wishlist.js"></script>
</body>
</html>

<?php
include 'includes/footer.php';
$conn->close();
?>

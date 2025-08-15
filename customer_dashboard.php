<?php
// FILE: customer_dashboard.php
include 'includes/header.php';

// Redirect if not logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get customer statistics
$stats_query = "
    SELECT 
        COUNT(DISTINCT o.id) as total_orders,
        COALESCE(SUM(o.total_amount), 0) as total_spent,
        COUNT(DISTINCT w.id) as wishlist_count,
        COUNT(DISTINCT r.id) as reviews_count
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id AND o.status = 'Delivered'
    LEFT JOIN wishlist w ON u.id = w.user_id
    LEFT JOIN reviews r ON u.id = r.user_id
    WHERE u.id = ?
    GROUP BY u.id
";

$stmt = $conn->prepare($stats_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Get recent orders
$recent_orders_query = "
    SELECT o.*, COUNT(oi.id) as item_count
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
    LIMIT 5
";

$stmt = $conn->prepare($recent_orders_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_orders = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - AgroKartBD</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/customer-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    <div class="dashboard-container">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="header-content">
                <div class="user-welcome">
                    <div class="user-avatar">
                        <img src="<?php echo isset($_SESSION['profile_image_path']) ? htmlspecialchars($_SESSION['profile_image_path']) : 'images/default-profile.png'; ?>" alt="Profile">
                    </div>
                    <div class="welcome-text">
                        <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
                        <p>Here's your shopping overview and quick access to your favorite features</p>
                    </div>
                </div>
                <div class="quick-actions">
                    <a href="index.php" class="quick-action">
                        <i class="fas fa-shopping-bag"></i>
                        <span>Continue Shopping</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-section">
            <div class="stats-grid">
                <div class="stat-card orders">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['total_orders'] ?? 0; ?></div>
                        <div class="stat-label">Orders Completed</div>
                    </div>
                    <a href="my_orders.php" class="stat-link">View Orders</a>
                </div>

                <div class="stat-card spending">
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">৳<?php echo number_format($stats['total_spent'] ?? 0, 2); ?></div>
                        <div class="stat-label">Total Spent</div>
                    </div>
                    <a href="my_orders.php" class="stat-link">View Orders</a>
                </div>

                <div class="stat-card wishlist">
                    <div class="stat-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['wishlist_count'] ?? 0; ?></div>
                        <div class="stat-label">Saved Items</div>
                    </div>
                    <a href="my_orders.php" class="stat-link">View Orders</a>
                </div>

                <div class="stat-card reviews">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['reviews_count'] ?? 0; ?></div>
                        <div class="stat-label">Reviews Written</div>
                    </div>
                    <a href="#" class="stat-link">Write More</a>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions-section">
            <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
            <div class="actions-grid">
                <div class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-redo-alt"></i>
                    </div>
                    <div class="action-content">
                        <h4>Reorder</h4>
                        <p>Reorder your frequently bought items</p>
                        <a href="my_orders.php" class="action-btn">
                            <i class="fas fa-shopping-cart"></i>
                            View Orders
                        </a>
                    </div>
                </div>

                <div class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                    <div class="action-content">
                        <h4>Compare Products</h4>
                        <p>Compare multiple products side by side</p>
                        <a href="product_comparison.php" class="action-btn">
                            <i class="fas fa-eye"></i>
                            View Comparison
                        </a>
                    </div>
                </div>

                <div class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="action-content">
                        <h4>Product Alerts</h4>
                        <p>Get notified about stock and price drops</p>
                        <button class="action-btn" onclick="manageNotifications()">
                            <i class="fas fa-cog"></i>
                            Manage Alerts
                        </button>
                    </div>
                </div>

                <div class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-crown"></i>
                    </div>
                    <div class="action-content">
                        <h4>My Profile</h4>
                        <p>View and edit your profile</p>
                        <a href="profile.php" class="action-btn">
                            <i class="fas fa-user"></i>
                            View Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="recent-orders-section">
            <div class="section-header">
                <h3><i class="fas fa-clock"></i> Recent Orders</h3>
                <a href="my_orders.php" class="view-all-link">View All Orders</a>
            </div>

            <?php if ($recent_orders->num_rows > 0): ?>
                <div class="orders-list">
                    <?php while ($order = $recent_orders->fetch_assoc()): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div class="order-id">
                                    <i class="fas fa-receipt"></i>
                                    <span>Order #<?php echo $order['id']; ?></span>
                                </div>
                                <div class="order-status <?php echo strtolower($order['status']); ?>">
                                    <?php echo $order['status']; ?>
                                </div>
                            </div>
                            <div class="order-details">
                                <div class="order-info">
                                    <div class="order-items">
                                        <i class="fas fa-box"></i>
                                        <span><?php echo $order['item_count']; ?> items</span>
                                    </div>
                                    <div class="order-amount">
                                        <i class="fas fa-money-bill"></i>
                                        <span>৳<?php echo number_format($order['total_amount'], 2); ?></span>
                                    </div>
                                    <div class="order-date">
                                        <i class="fas fa-calendar"></i>
                                        <span><?php echo date('M j, Y', strtotime($order['created_at'])); ?></span>
                                    </div>
                                </div>
                                <div class="order-actions">
                                    <a href="my_orders.php?order_id=<?php echo $order['id']; ?>" class="order-btn view-btn">
                                        <i class="fas fa-eye"></i>
                                        View Details
                                    </a>
                                    <?php if ($order['status'] === 'Delivered'): ?>
                                        <button class="order-btn reorder-btn" onclick="reorderItems(<?php echo $order['id']; ?>)">
                                            <i class="fas fa-redo"></i>
                                            Reorder
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-orders">
                    <div class="empty-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h4>No orders yet</h4>
                    <p>Start shopping to see your orders here!</p>
                    <a href="index.php" class="shop-now-btn">
                        <i class="fas fa-shopping-bag"></i>
                        Start Shopping
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Personalized Recommendations -->
        <div class="recommendations-section">
            <h3><i class="fas fa-magic"></i> Recommended for You</h3>
            <div class="recommendations-grid" id="recommendations-grid">
                <!-- Recommendations will be loaded via JavaScript -->
            </div>
        </div>

        <!-- Recently Viewed -->
        <div class="recently-viewed-section">
            <h3><i class="fas fa-history"></i> Recently Viewed</h3>
            <div class="recently-viewed-grid" id="recently-viewed-grid">
                <!-- Recently viewed items will be loaded via JavaScript -->
            </div>
        </div>
    </div>

    <!-- Quick Reorder Modal -->
    <div id="reorder-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-redo-alt"></i> Quick Reorder</h3>
                <button class="close-btn" onclick="closeReorderModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="reorder-modal-body">
                <!-- Reorder content will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Notifications Modal -->
    <div id="notifications-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-bell"></i> Manage Notifications</h3>
                <button class="close-btn" onclick="closeNotificationsModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="notifications-modal-body">
                <!-- Notifications content will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Notification Container -->
    <div id="notification-container"></div>

    <script src="js/customer-dashboard.js"></script>
</body>

</html>

<?php
include 'includes/footer.php';
$conn->close();
?>
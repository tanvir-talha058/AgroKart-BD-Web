<?php
// FILE: my_orders.php

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

// Include header after checking for redirect
include 'includes/header.php';
$buyer_id = $_SESSION['user_id'];
?>

<!-- Enhanced My Orders Section -->
<section class="orders-section">
    <div class="orders-background">
        <div class="orders-pattern"></div>
    </div>

    <div class="orders-container">
        <div class="orders-header">
            <div class="orders-title-wrapper">
                <div class="orders-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="orders-title-content">
                    <h1 class="orders-title">My Orders</h1>
                    <p class="orders-subtitle">Track and manage your order history</p>
                </div>
            </div>
            <div class="orders-stats">
                <?php
                // Get order statistics
                $stats_sql = "SELECT 
                    COUNT(*) as total_orders,
                    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_orders,
                    SUM(CASE WHEN status = 'Delivered' THEN 1 ELSE 0 END) as delivered_orders
                    FROM orders WHERE buyer_id = ?";
                $stats_stmt = $conn->prepare($stats_sql);
                $stats_stmt->bind_param("i", $buyer_id);
                $stats_stmt->execute();
                $stats_result = $stats_stmt->get_result();
                $stats = $stats_result->fetch_assoc();
                $stats_stmt->close();
                ?>
                <div class="stat-item">
                    <i class="fas fa-box"></i>
                    <span><?php echo $stats['total_orders']; ?> Total</span>
                </div>
                <div class="stat-item pending">
                    <i class="fas fa-clock"></i>
                    <span><?php echo $stats['pending_orders']; ?> Pending</span>
                </div>
                <div class="stat-item delivered">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $stats['delivered_orders']; ?> Delivered</span>
                </div>
            </div>
        </div>

        <div class="orders-content">
            <?php
            $sql = "SELECT o.*, 
                    COUNT(oi.id) as item_count,
                    GROUP_CONCAT(p.name SEPARATOR ', ') as product_names
                    FROM orders o 
                    LEFT JOIN order_items oi ON o.id = oi.order_id
                    LEFT JOIN products p ON oi.product_id = p.id
                    WHERE o.buyer_id = ? 
                    GROUP BY o.id 
                    ORDER BY o.created_at DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $buyer_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                echo '<div class="orders-grid">';
                while ($order = $result->fetch_assoc()) {
                    $status_class = strtolower(str_replace(' ', '-', $order['status']));
                    $status_icon = '';
                    switch($order['status']) {
                        case 'Pending':
                            $status_icon = 'fas fa-clock';
                            break;
                        case 'Processing':
                            $status_icon = 'fas fa-cog fa-spin';
                            break;
                        case 'Shipped':
                            $status_icon = 'fas fa-truck';
                            break;
                        case 'Delivered':
                            $status_icon = 'fas fa-check-circle';
                            break;
                        case 'Cancelled':
                            $status_icon = 'fas fa-times-circle';
                            break;
                        default:
                            $status_icon = 'fas fa-info-circle';
                    }
                    
                    echo '<div class="order-card">';
                    echo '<div class="order-card-header">';
                    echo '<div class="order-id-section">';
                    echo '<span class="order-id">#' . str_pad($order['id'], 6, '0', STR_PAD_LEFT) . '</span>';
                    echo '<span class="order-date">' . date('M d, Y', strtotime($order['created_at'])) . '</span>';
                    echo '</div>';
                    echo '<div class="order-status status-' . $status_class . '">';
                    echo '<i class="' . $status_icon . '"></i>';
                    echo '<span>' . $order['status'] . '</span>';
                    echo '</div>';
                    echo '</div>';
                    
                    echo '<div class="order-card-body">';
                    echo '<div class="order-details">';
                    echo '<div class="order-info">';
                    echo '<div class="info-item">';
                    echo '<i class="fas fa-shopping-cart"></i>';
                    echo '<span>' . $order['item_count'] . ' item(s)</span>';
                    echo '</div>';
                    echo '<div class="info-item">';
                    echo '<i class="fas fa-map-marker-alt"></i>';
                    echo '<span>' . htmlspecialchars($order['delivery_location']) . '</span>';
                    echo '</div>';
                    echo '</div>';
                    
                    if ($order['product_names']) {
                        $products = explode(', ', $order['product_names']);
                        $display_products = array_slice($products, 0, 3);
                        echo '<div class="order-products">';
                        echo '<p class="products-label">Products:</p>';
                        echo '<p class="products-list">' . implode(', ', $display_products);
                        if (count($products) > 3) {
                            echo ' <span class="more-products">+' . (count($products) - 3) . ' more</span>';
                        }
                        echo '</p>';
                        echo '</div>';
                    }
                    echo '</div>';
                    
                    echo '<div class="order-amount">';
                    echo '<span class="amount-label">Total Amount</span>';
                    echo '<span class="amount-value">৳' . number_format($order['total_amount'], 2) . '</span>';
                    echo '</div>';
                    echo '</div>';
                    
                    echo '<div class="order-card-footer">';
                    echo '<div class="order-actions">';
                    if ($order['status'] == 'Pending') {
                        echo '<button class="action-btn cancel-btn" onclick="cancelOrder(' . $order['id'] . ')">';
                        echo '<i class="fas fa-times"></i> Cancel Order';
                        echo '</button>';
                    }
                    echo '<button class="action-btn view-btn" onclick="viewOrderDetails(' . $order['id'] . ')">';
                    echo '<i class="fas fa-eye"></i> View Details';
                    echo '</button>';
                    if ($order['status'] == 'Delivered') {
                        echo '<button class="action-btn reorder-btn" onclick="reorderItems(' . $order['id'] . ')">';
                        echo '<i class="fas fa-redo"></i> Reorder';
                        echo '</button>';
                    }
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
                echo '</div>';
            } else {
                echo '<div class="empty-orders-section">';
                echo '<div class="empty-orders-content">';
                echo '<div class="empty-orders-icon">';
                echo '<i class="fas fa-shopping-bag"></i>';
                echo '</div>';
                echo '<h2>No Orders Yet</h2>';
                echo '<p>You haven\'t placed any orders yet. Start shopping to see your orders here!</p>';
                echo '<div class="empty-orders-actions">';
                echo '<a href="index.php" class="shop-now-btn">';
                echo '<i class="fas fa-store"></i>';
                echo '<span>Start Shopping</span>';
                echo '</a>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
            $stmt->close();
            ?>
        </div>
    </div>
</section>

<style>
    /* Enhanced Orders Page Styles */
    .orders-section {
        position: relative;
        min-height: 100vh;
        background: linear-gradient(135deg, #f8fff9 0%, #e8f5e8 50%, #d4edda 100%);
        overflow: hidden;
        padding: 40px 0;
    }

    .orders-background {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
    }

    .orders-pattern {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image:
            radial-gradient(circle at 20% 80%, rgba(76, 175, 80, 0.1) 0%, transparent 50%),
            radial-gradient(circle at 80% 20%, rgba(139, 195, 74, 0.1) 0%, transparent 50%),
            radial-gradient(circle at 40% 40%, rgba(76, 175, 80, 0.05) 0%, transparent 50%);
    }

    .orders-container {
        position: relative;
        z-index: 2;
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
    }

    /* Orders Header */
    .orders-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 40px;
        background: white;
        padding: 30px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .orders-title-wrapper {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .orders-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #4CAF50, #8BC34A);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        box-shadow: 0 10px 30px rgba(76, 175, 80, 0.3);
    }

    .orders-title {
        font-size: 2.5rem;
        font-weight: 800;
        color: #2c3e50;
        margin: 0;
    }

    .orders-subtitle {
        color: #666;
        font-size: 1.1rem;
        margin: 5px 0 0 0;
    }

    .orders-stats {
        display: flex;
        gap: 20px;
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 8px;
        background: rgba(76, 175, 80, 0.1);
        padding: 10px 20px;
        border-radius: 25px;
        color: #4CAF50;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .stat-item.pending {
        background: rgba(255, 193, 7, 0.1);
        color: #ffc107;
    }

    .stat-item.delivered {
        background: rgba(40, 167, 69, 0.1);
        color: #28a745;
    }

    /* Orders Grid */
    .orders-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
        gap: 25px;
    }

    .order-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        border: 1px solid #f0f0f0;
    }

    .order-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }

    .order-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 25px;
        background: #f8fff9;
        border-bottom: 1px solid #e8f5e8;
    }

    .order-id-section {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .order-id {
        font-size: 1.2rem;
        font-weight: 700;
        color: #2c3e50;
    }

    .order-date {
        font-size: 0.9rem;
        color: #666;
    }

    .order-status {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-pending {
        background: rgba(255, 193, 7, 0.1);
        color: #ffc107;
        border: 1px solid rgba(255, 193, 7, 0.3);
    }

    .status-processing {
        background: rgba(0, 123, 255, 0.1);
        color: #007bff;
        border: 1px solid rgba(0, 123, 255, 0.3);
    }

    .status-shipped {
        background: rgba(255, 87, 34, 0.1);
        color: #ff5722;
        border: 1px solid rgba(255, 87, 34, 0.3);
    }

    .status-delivered {
        background: rgba(40, 167, 69, 0.1);
        color: #28a745;
        border: 1px solid rgba(40, 167, 69, 0.3);
    }

    .status-cancelled {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545;
        border: 1px solid rgba(220, 53, 69, 0.3);
    }

    .order-card-body {
        padding: 25px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
    }

    .order-details {
        flex: 1;
    }

    .order-info {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-bottom: 15px;
    }

    .info-item {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #666;
        font-size: 0.9rem;
    }

    .info-item i {
        color: #4CAF50;
        width: 16px;
    }

    .order-products {
        margin-top: 15px;
    }

    .products-label {
        font-size: 0.9rem;
        color: #666;
        margin: 0 0 5px 0;
        font-weight: 600;
    }

    .products-list {
        color: #2c3e50;
        margin: 0;
        line-height: 1.4;
    }

    .more-products {
        color: #4CAF50;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .order-amount {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 5px;
    }

    .amount-label {
        font-size: 0.9rem;
        color: #666;
    }

    .amount-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #4CAF50;
    }

    .order-card-footer {
        padding: 20px 25px;
        background: #f8fff9;
        border-top: 1px solid #e8f5e8;
    }

    .order-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .action-btn {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border: none;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .view-btn {
        background: linear-gradient(135deg, #4CAF50, #8BC34A);
        color: white;
    }

    .view-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
    }

    .cancel-btn {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545;
        border: 1px solid rgba(220, 53, 69, 0.3);
    }

    .cancel-btn:hover {
        background: #dc3545;
        color: white;
    }

    .reorder-btn {
        background: rgba(0, 123, 255, 0.1);
        color: #007bff;
        border: 1px solid rgba(0, 123, 255, 0.3);
    }

    .reorder-btn:hover {
        background: #007bff;
        color: white;
    }

    /* Empty Orders Section */
    .empty-orders-section {
        background: white;
        border-radius: 20px;
        padding: 80px 40px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .empty-orders-content {
        max-width: 500px;
        margin: 0 auto;
    }

    .empty-orders-icon {
        width: 120px;
        height: 120px;
        background: linear-gradient(135deg, #4CAF50, #8BC34A);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 30px;
        color: white;
        font-size: 3rem;
        box-shadow: 0 10px 30px rgba(76, 175, 80, 0.3);
    }

    .empty-orders-section h2 {
        font-size: 2rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 15px;
    }

    .empty-orders-section p {
        color: #666;
        font-size: 1.1rem;
        line-height: 1.6;
        margin-bottom: 40px;
    }

    .shop-now-btn {
        background: linear-gradient(135deg, #4CAF50, #8BC34A);
        color: white;
        padding: 18px 40px;
        border-radius: 30px;
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
    }

    .shop-now-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
        color: white;
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .orders-grid {
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        }
    }

    @media (max-width: 768px) {
        .orders-header {
            flex-direction: column;
            gap: 20px;
            text-align: center;
        }

        .orders-stats {
            flex-wrap: wrap;
            justify-content: center;
        }

        .orders-grid {
            grid-template-columns: 1fr;
        }

        .order-card-body {
            flex-direction: column;
            gap: 15px;
        }

        .order-amount {
            align-items: flex-start;
        }

        .order-actions {
            justify-content: center;
        }

        .empty-orders-section {
            padding: 60px 20px;
        }

        .empty-orders-icon {
            width: 100px;
            height: 100px;
            font-size: 2.5rem;
        }
    }

    @media (max-width: 480px) {
        .orders-container {
            padding: 0 15px;
        }

        .orders-title {
            font-size: 2rem;
        }

        .order-card-header,
        .order-card-body,
        .order-card-footer {
            padding: 15px 20px;
        }

        .stat-item {
            padding: 8px 12px;
            font-size: 0.8rem;
        }
    }
</style>

<script>
    function viewOrderDetails(orderId) {
        // You can implement a modal or redirect to order details page
        alert('View details for order #' + orderId + ' - This can be implemented with a modal or separate page');
    }

    function cancelOrder(orderId) {
        if (confirm('Are you sure you want to cancel this order?')) {
            // Implement order cancellation logic
            alert('Order #' + orderId + ' cancellation requested - Implement backend logic');
        }
    }

    function reorderItems(orderId) {
        // Implement reorder functionality
        alert('Reordering items from order #' + orderId + ' - Implement add to cart logic');
    }
</script>

<?php include 'includes/footer.php'; ?>
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
                    switch ($order['status']) {
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

    /* Order Details Modal Styles */
    .order-details-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
        padding: 20px;
    }

    .order-details-modal.show {
        opacity: 1;
        pointer-events: all;
    }

    .order-details-content {
        background: white;
        border-radius: 15px;
        width: 95%;
        max-width: 900px;
        max-height: 85vh;
        display: flex;
        flex-direction: column;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        animation: slideIn 0.4s ease;
        overflow: hidden;
        position: relative;
    }

    .order-details-header {
        background: linear-gradient(135deg, #4CAF50, #8BC34A);
        padding: 20px 25px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: sticky;
        top: 0;
        z-index: 5;
    }

    .order-details-header h3 {
        margin: 0;
        font-size: 1.5rem;
        color: white;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .order-details-header h3 i {
        font-size: 1.2rem;
    }

    .close-modal {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .close-modal:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: rotate(90deg);
    }

    .order-details-body {
        padding: 25px;
        overflow-y: auto;
        max-height: calc(85vh - 80px);
        /* Header height is approximately 80px */
    }

    .loading-spinner {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #007bff;
        font-size: 1rem;
        margin: 20px 0;
    }

    .error-message {
        color: #dc3545;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 20px 0;
    }

    .order-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 30px;
    }

    @media (max-width: 768px) {
        .order-info-grid {
            grid-template-columns: 1fr;
        }
    }

    .order-info-section {
        background: #f9f9f9;
        padding: 20px;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        border: 1px solid rgba(76, 175, 80, 0.1);
    }

    .order-info-section h4 {
        margin: 0 0 15px 0;
        font-size: 1.3rem;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 10px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e0e0e0;
    }

    .order-info-section h4 i {
        color: #4CAF50;
        font-size: 1.3rem;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px dashed #e9ecef;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .label {
        color: #6c757d;
        font-weight: 500;
    }

    .value {
        font-weight: 600;
        color: #2c3e50;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .order-items-section {
        margin-top: 25px;
        background: #f9f9f9;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    }

    .order-items-section h4 {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 0;
        margin-bottom: 15px;
        color: #2c3e50;
        font-size: 1.3rem;
    }

    .order-items-section h4 i {
        color: #4CAF50;
    }

    .order-items-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 15px;
        max-height: 400px;
        overflow-y: auto;
        padding-right: 5px;
    }

    .order-items-list::-webkit-scrollbar {
        width: 6px;
    }

    .order-items-list::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .order-items-list::-webkit-scrollbar-thumb {
        background: #4CAF50;
        border-radius: 10px;
    }

    .detail-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: 1px solid rgba(76, 175, 80, 0.1);
    }

    .detail-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
    }

    .item-image {
        width: 70px;
        height: 70px;
        overflow: hidden;
        border-radius: 10px;
        border: 1px solid #e0e0e0;
        flex-shrink: 0;
    }

    .item-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .item-info {
        flex: 1;
        min-width: 0;
        /* Prevents flex item from overflowing */
    }

    .item-info h4 {
        margin: 0 0 8px 0;
        font-size: 1.1rem;
        color: #2c3e50;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .item-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 5px;
        font-size: 0.95rem;
    }

    .price {
        color: #4CAF50;
        font-weight: 600;
    }

    .quantity {
        color: #666;
        background: #f5f5f5;
        padding: 3px 8px;
        border-radius: 15px;
        font-size: 0.85rem;
    }

    .subtotal {
        font-weight: 700;
        color: #2c3e50;
    }

    .order-summary {
        margin-top: 30px;
        padding: 20px;
        background: linear-gradient(135deg, #f9f9f9, #f3f3f3);
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        border: 1px solid rgba(76, 175, 80, 0.1);
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px dashed #e0e0e0;
        font-size: 1.05rem;
    }

    .summary-row:last-child {
        border-bottom: none;
        margin-top: 5px;
        padding-top: 15px;
        font-weight: 700;
        color: #2c3e50;
        font-size: 1.2rem;
    }

    .summary-row:last-child span:last-child {
        color: #4CAF50;
        font-size: 1.3rem;
    }

    /* Animations */
    @keyframes slideIn {
        from {
            transform: translateY(-10px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* Notification Styles */
    .notification-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .notification {
        background: white;
        border-radius: 10px;
        padding: 15px 20px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        transform: translateX(120%);
        transition: transform 0.3s ease;
        max-width: 300px;
    }

    .notification.show {
        transform: translateX(0);
    }

    .notification.success {
        border-left: 4px solid #4CAF50;
    }

    .notification.error {
        border-left: 4px solid #dc3545;
    }

    .notification-content {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .notification-content i {
        font-size: 1.2rem;
    }

    .notification.success i {
        color: #4CAF50;
    }

    .notification.error i {
        color: #dc3545;
    }
</style>

<script>
    function viewOrderDetails(orderId) {
        // Create and open a modal with order details
        const modal = document.createElement('div');
        modal.className = 'order-details-modal';
        modal.innerHTML = `
            <div class="order-details-content">
                <div class="order-details-header">
                    <h3><i class="fas fa-clipboard-list"></i> Order #${String(orderId).padStart(6, '0')} Details</h3>
                    <button class="close-modal"><i class="fas fa-times"></i></button>
                </div>
                <div class="order-details-body">
                    <div class="loading-spinner">
                        <i class="fas fa-spinner fa-spin"></i>
                        <span>Loading order details...</span>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        // Add event listener to close button
        modal.querySelector('.close-modal').addEventListener('click', function() {
            closeModal(modal);
        });

        // Close modal when clicking outside the content
        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeModal(modal);
            }
        });

        // Also close on escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && modal.classList.contains('show')) {
                closeModal(modal);
            }
        });

        // Show modal with animation
        setTimeout(() => modal.classList.add('show'), 10);

        // Fetch order details using AJAX
        fetch(`php/get_order_details.php?order_id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update modal content with order details
                    const detailsBody = modal.querySelector('.order-details-body');
                    detailsBody.innerHTML = createOrderDetailsHTML(data.order, data.items);
                } else {
                    modal.querySelector('.order-details-body').innerHTML = `
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <p>${data.message}</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error fetching order details:', error);
                modal.querySelector('.order-details-body').innerHTML = `
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <p>Failed to load order details. Please try again.</p>
                    </div>
                `;
            });
    }

    // Helper function to create order details HTML
    function createOrderDetailsHTML(order, items) {
        // Format date
        const orderDate = new Date(order.created_at);
        const formattedDate = orderDate.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        // Get status icon based on order status
        let statusIcon = 'fas fa-info-circle';
        switch (order.status.toLowerCase()) {
            case 'pending':
                statusIcon = 'fas fa-clock';
                break;
            case 'processing':
                statusIcon = 'fas fa-cog fa-spin';
                break;
            case 'shipped':
                statusIcon = 'fas fa-truck';
                break;
            case 'delivered':
                statusIcon = 'fas fa-check-circle';
                break;
            case 'cancelled':
                statusIcon = 'fas fa-times-circle';
                break;
        }

        // Create items list HTML
        const itemsHTML = items.map(item => `
            <div class="detail-item">
                <div class="item-image">
                    <img src="${item.image}" alt="${item.name}">
                </div>
                <div class="item-info">
                    <h4>${item.name}</h4>
                    <div class="item-meta">
                        <span class="price">৳${parseFloat(item.price).toFixed(2)}</span>
                        <span class="quantity">× ${item.quantity}</span>
                        <span class="subtotal">৳${(parseFloat(item.price) * parseInt(item.quantity)).toFixed(2)}</span>
                    </div>
                </div>
            </div>
        `).join('');

        return `
            <div class="order-info-grid">
                <div class="order-info-section">
                    <h4><i class="fas fa-info-circle"></i> Order Information</h4>
                    <div class="info-row">
                        <span class="label">Order Date:</span>
                        <span class="value">${formattedDate}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Status:</span>
                        <span class="value status-badge status-${order.status.toLowerCase()}"><i class="${statusIcon}"></i> ${order.status}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Payment Method:</span>
                        <span class="value"><i class="fas fa-credit-card"></i> ${order.payment_method}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Payment Status:</span>
                        <span class="value">${order.payment_status === 'Paid' ? 
                          '<span style="color: #4CAF50;"><i class="fas fa-check-circle"></i> Paid</span>' : 
                          '<span style="color: #ffc107;"><i class="fas fa-clock"></i> Pending</span>'}</span>
                    </div>
                </div>
                
                <div class="order-info-section">
                    <h4><i class="fas fa-map-marker-alt"></i> Shipping Information</h4>
                    <div class="info-row">
                        <span class="label">Name:</span>
                        <span class="value">${order.full_name || 'Not available'}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Phone:</span>
                        <span class="value">${order.phone || 'Not available'}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Address:</span>
                        <span class="value">${order.delivery_location}</span>
                    </div>
                </div>
            </div>
            
            <div class="order-items-section">
                <h4><i class="fas fa-shopping-cart"></i> Order Items (${items.length} items)</h4>
                <div class="order-items-list">
                    ${itemsHTML}
                </div>
            </div>
            
            <div class="order-summary">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>৳${parseFloat(order.total_amount).toFixed(2)}</span>
                </div>
                <div class="summary-row">
                    <span>Shipping:</span>
                    <span>${parseFloat(order.shipping_cost) > 0 ? '৳' + parseFloat(order.shipping_cost).toFixed(2) : '<span class="free-delivery">Free</span>'}</span>
                </div>
                <div class="summary-row total">
                    <span>Total Amount:</span>
                    <span>৳${parseFloat(order.total_amount).toFixed(2)}</span>
                </div>
            </div>
        `;
    }

    function cancelOrder(orderId) {
        if (confirm('Are you sure you want to cancel this order?')) {
            // Show loading indicator on the button
            const button = document.querySelector(`.cancel-btn[onclick="cancelOrder(${orderId})"]`);
            const originalContent = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cancelling...';
            button.disabled = true;

            // Submit cancel request via AJAX
            fetch('php/update_order_status_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `order_id=${orderId}&action=cancel`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        showNotification(data.message, 'success');

                        // Update order status in UI
                        const orderCard = button.closest('.order-card');
                        const statusElement = orderCard.querySelector('.order-status');
                        statusElement.className = 'order-status status-cancelled';
                        statusElement.innerHTML = '<i class="fas fa-times-circle"></i><span>Cancelled</span>';

                        // Remove cancel button
                        button.remove();
                    } else {
                        showNotification(data.message || 'Failed to cancel order.', 'error');
                        button.innerHTML = originalContent;
                        button.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred. Please try again.', 'error');
                    button.innerHTML = originalContent;
                    button.disabled = false;
                });
        }
    }

    // Helper function to close modal with animation
    function closeModal(modal) {
        modal.classList.remove('show');
        setTimeout(() => modal.remove(), 300);
    }

    function reorderItems(orderId) {
        // Show loading indicator
        const button = document.querySelector(`.reorder-btn[onclick="reorderItems(${orderId})"]`);
        const originalContent = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding to cart...';
        button.disabled = true;

        // Submit reorder request via AJAX
        fetch('php/reorder_items.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `order_id=${orderId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');

                    // Update cart count in header
                    updateCartCount(data.cart_count);

                    // Show success UI on button
                    button.innerHTML = '<i class="fas fa-check"></i> Added to cart';

                    // Redirect to cart page after short delay
                    setTimeout(() => {
                        window.location.href = 'cart.php';
                    }, 1500);
                } else {
                    showNotification(data.message || 'Failed to add items to cart.', 'error');
                    button.innerHTML = originalContent;
                    button.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
                button.innerHTML = originalContent;
                button.disabled = false;
            });
    }

    // Add this function to my_orders.php if it's not already present
    function showNotification(message, type) {
        // Check if notification container exists, if not create it
        let container = document.querySelector('.notification-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'notification-container';
            document.body.appendChild(container);
        }

        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                <span>${message}</span>
            </div>
        `;

        // Add to container
        container.appendChild(notification);

        // Show notification
        setTimeout(() => notification.classList.add('show'), 10);

        // Hide notification after 5 seconds
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    }

    // Add cart count update function if not already present
    function updateCartCount(count) {
        // Target all possible cart counter elements
        const cartIcons = document.querySelectorAll('[data-count]');
        const cartBadges = document.querySelectorAll('.badge');
        const cartCountTexts = document.querySelectorAll('.cart-count');

        // Update data-count attributes
        cartIcons.forEach(icon => {
            icon.setAttribute('data-count', count);
        });

        // Update any badge elements
        cartBadges.forEach(badge => {
            badge.textContent = count;
        });

        // Update text content of any cart count elements
        cartCountTexts.forEach(text => {
            if (text) text.textContent = count;
        });
    }
</script>

<?php include 'includes/footer.php'; ?>
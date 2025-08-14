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

<link rel="stylesheet" href="css/my-orders-style.css">

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
            .then(r => r.json())
            .then(data => {
                console.log('Order details response:', data);
                const bodyEl = modal.querySelector('.order-details-body');
                if (data && data.success && data.order) {
                    const items = Array.isArray(data.order.items) ? data.order.items : [];
                    bodyEl.innerHTML = createOrderDetailsHTML(data.order, items);
                } else {
                    bodyEl.innerHTML = `
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <p>${(data && data.message) ? data.message : 'Failed to load order details. Please try again.'}</p>
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
    function createOrderDetailsHTML(order, items = []) {
        // Fallback to order.items if items param is missing
        if (!Array.isArray(items) || items.length === 0) {
            items = Array.isArray(order?.items) ? order.items : [];
        }
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
        const itemsHTML = items.map(item => {
            const imgSrc = item.image || item.image_path || 'images/AGrO.png';
            return `
            <div class="detail-item">
                <div class="item-image">
            <img src="${imgSrc}" alt="${item.name}" onerror="this.src='images/AGrO.png'">
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
    `;
        }).join('');

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
            
            ${order.notes ? `
            <div class="order-notes-section">
                <h4><i class="fas fa-sticky-note"></i> Order Notes</h4>
                <div class="order-notes">
                    <p>${order.notes}</p>
                </div>
            </div>
            ` : ''}
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
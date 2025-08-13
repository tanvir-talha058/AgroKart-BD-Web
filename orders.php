<?php
require_once 'includes/db_connect.php';

// Protect this page and ensure the user is a seller
if (!isset($_SESSION['loggedin']) || $_SESSION['user_role'] !== 'Seller') {
    header('Location: login.php');
    exit;
}

$seller_id = $_SESSION['user_id'];

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$date_filter = isset($_GET['date']) ? $_GET['date'] : 'all';

// Build WHERE clause for filters
$where_conditions = [];
$params = [$seller_id];
$param_types = "i";

if ($status_filter !== 'all') {
    $where_conditions[] = "o.status = ?";
    $params[] = $status_filter;
    $param_types .= "s";
}

if ($date_filter !== 'all') {
    switch ($date_filter) {
        case 'today':
            $where_conditions[] = "DATE(o.created_at) = CURDATE()";
            break;
        case 'week':
            $where_conditions[] = "o.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            break;
        case 'month':
            $where_conditions[] = "o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            break;
    }
}

$where_clause = count($where_conditions) > 0 ? " AND " . implode(" AND ", $where_conditions) : "";

// Get all order items with product details in one query
$order_items_sql = "SELECT 
                    oi.id as item_id,
                    o.id AS order_id, 
                    o.status,
                    o.created_at,
                    o.notes,
                    u.name AS buyer_name,
                    u.email AS buyer_email,
                    u.phone AS buyer_phone,
                    CONCAT(u.city, ', ', u.district, ', ', u.division) AS location,
                    p.name AS product_name,
                    p.image_path,
                    p.unit,
                    oi.quantity,
                    oi.price,
                    (oi.quantity * oi.price) AS item_total
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                JOIN users u ON o.buyer_id = u.id
                JOIN products p ON oi.product_id = p.id
                WHERE p.seller_id = ? $where_clause
                ORDER BY o.created_at DESC";

$stmt = $conn->prepare($order_items_sql);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$order_items = [];
$total_sales = 0;
$status_counts = [
    'Pending' => 0,
    'Processing' => 0,
    'Shipped' => 0,
    'Delivered' => 0,
    'Cancelled' => 0
];

$unique_orders = [];

while ($row = $result->fetch_assoc()) {
    $order_items[] = $row;

    // Count unique orders by status
    if (!isset($unique_orders[$row['order_id']])) {
        $unique_orders[$row['order_id']] = $row['status'];
        $status_counts[$row['status']]++;
    }

    // Add to total sales if delivered
    if ($row['status'] === 'Delivered') {
        $total_sales += $row['item_total'];
    }
}
$stmt->close();

// We already have the stats from our order items query
$stats = [
    'pending_count' => $status_counts['Pending'],
    'processing_count' => $status_counts['Processing'],
    'shipped_count' => $status_counts['Shipped'],
    'delivered_count' => $status_counts['Delivered'],
    'cancelled_count' => $status_counts['Cancelled'],
    'total_revenue' => $total_sales
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management - AgroKartBD</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/orders.css">
    <link rel="stylesheet" href="css/order-card-fixes.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
                <li><a href="products.php"><span class="icon"><i class="fas fa-box"></i></span>Products</a></li>
                <li class="active"><a href="#"><span class="icon"><i class="fas fa-shopping-cart"></i></span>Orders</a></li>
                <li><a href="customers.php"><span class="icon"><i class="fas fa-users"></i></span>Customers</a></li>
                <li><a href="php/logout.php"><span class="icon"><i class="fas fa-sign-out-alt"></i></span>Logout</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="top-header">
                <h1 class="page-title"><i class="fas fa-shopping-cart"></i> Order Products</h1>
                <div class="header-right">
                    <div class="search-filter">
                        <form method="GET" class="filters-form">
                            <select name="status" onchange="this.form.submit()">
                                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                                <option value="Pending" <?php echo $status_filter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="Processing" <?php echo $status_filter === 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="Shipped" <?php echo $status_filter === 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="Delivered" <?php echo $status_filter === 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="Cancelled" <?php echo $status_filter === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                            <select name="date" onchange="this.form.submit()">
                                <option value="all" <?php echo $date_filter === 'all' ? 'selected' : ''; ?>>All Time</option>
                                <option value="today" <?php echo $date_filter === 'today' ? 'selected' : ''; ?>>Today</option>
                                <option value="week" <?php echo $date_filter === 'week' ? 'selected' : ''; ?>>This Week</option>
                                <option value="month" <?php echo $date_filter === 'month' ? 'selected' : ''; ?>>This Month</option>
                            </select>
                        </form>
                    </div>
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

            <!-- Order Statistics -->
            <div class="stats-container">
                <div class="stat-card pending">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="fas fa-clock"></i></div><span>Pending</span>
                    </div>
                    <div class="stat-info">
                        <h2><?php echo $stats['pending_count']; ?></h2>
                    </div>
                </div>
                <div class="stat-card processing">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="fas fa-cog"></i></div><span>Processing</span>
                    </div>
                    <div class="stat-info">
                        <h2><?php echo $stats['processing_count']; ?></h2>
                    </div>
                </div>
                <div class="stat-card shipped">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="fas fa-shipping-fast"></i></div><span>Shipped</span>
                    </div>
                    <div class="stat-info">
                        <h2><?php echo $stats['shipped_count']; ?></h2>
                    </div>
                </div>
                <div class="stat-card delivered">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="fas fa-check-circle"></i></div><span>Delivered</span>
                    </div>
                    <div class="stat-info">
                        <h2><?php echo $stats['delivered_count']; ?></h2>
                    </div>
                </div>
                <div class="stat-card revenue">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div><span>Revenue</span>
                    </div>
                    <div class="stat-info">
                        <h2>৳<?php echo number_format($stats['total_revenue'], 2); ?></h2>
                    </div>
                </div>
            </div>

            <!-- Orders Product List -->
            <div class="orders-product-container">
                <div class="list-header">
                    <h3>Ordered Products <span class="order-count">(<?php echo count($order_items); ?> items)</span></h3>
                </div>

                <?php if (count($order_items) > 0): ?>
                    <div class="product-orders-grid">
                        <?php foreach ($order_items as $item): ?>
                            <div class="product-order-card status-<?php echo strtolower($item['status']); ?>">
                                <div class="product-image">
                                    <img src="<?php echo !empty($item['image_path']) ? htmlspecialchars($item['image_path']) : 'images/AGrO.png'; ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" onerror="this.src='images/AGrO.png'">
                                    <div class="order-status-badge <?php echo strtolower($item['status']); ?>">
                                        <?php echo $item['status']; ?>
                                    </div>
                                </div>

                                <div class="product-details">
                                    <div class="order-header-grid">
                                        <div class="order-code-section">
                                            <div class="order-code"><?php echo strtoupper(substr($item['product_name'], 0, 2)); ?></div>
                                        </div>
                                        <div class="order-info-section">
                                            <div class="order-id">Order #<?php echo $item['order_id']; ?></div>
                                            <div class="order-date">
                                                <i class="fas fa-calendar"></i>
                                                <span><?php echo date('M d, Y', strtotime($item['created_at'])); ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <h3 class="product-name"><?php echo htmlspecialchars($item['product_name']); ?></h3>

                                    <div class="order-details-grid">
                                        <table class="order-details-table">
                                            <tr>
                                                <td class="order-detail-label">Quantity:</td>
                                                <td class="order-detail-value"><?php echo $item['quantity']; ?> <?php echo htmlspecialchars($item['unit']); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="order-detail-label">Unit Price:</td>
                                                <td class="order-detail-value">৳<?php echo number_format($item['price'], 2); ?></td>
                                            </tr>
                                            <tr class="total-row">
                                                <td class="order-detail-label">Total:</td>
                                                <td class="order-detail-value total-value">৳<?php echo number_format($item['item_total'], 2); ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="buyer-info">
                                    <div class="buyer-header">
                                        <i class="fas fa-user-circle"></i>
                                        <h4>Buyer Information</h4>
                                    </div>
                                    <div class="buyer-details">
                                        <p><i class="fas fa-user"></i> <?php echo htmlspecialchars($item['buyer_name']); ?></p>
                                        <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($item['buyer_phone']); ?></p>
                                        <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($item['location']); ?></p>
                                    </div>

                                    <?php if (!empty($item['notes'])): ?>
                                        <div class="order-notes-section">
                                            <div class="notes-header">
                                                <i class="fas fa-sticky-note"></i>
                                                <h4>Special Notes</h4>
                                            </div>
                                            <div class="notes-content">
                                                <p><?php echo htmlspecialchars($item['notes']); ?></p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="order-actions">
                                    <form action="php/update_order_status.php" method="POST" class="status-update-form">
                                        <input type="hidden" name="order_id" value="<?php echo $item['order_id']; ?>">
                                        <input type="hidden" name="redirect_to" value="orders">
                                        <div class="status-dropdown-wrapper">
                                            <select name="status" class="status-select status-select-<?php echo strtolower($item['status']); ?>">
                                                <?php
                                                $statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
                                                foreach ($statuses as $status) {
                                                    $selected = ($item['status'] == $status) ? 'selected' : '';
                                                    echo '<option value="' . $status . '" ' . $selected . '>' . $status . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <button type="submit" class="update-btn">UPDATE STATUS</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-orders">
                        <div class="no-data-icon"><i class="fas fa-shopping-cart"></i></div>
                        <h3>No Orders Found</h3>
                        <p>No orders match the selected filters. Orders will appear here when customers purchase your products.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Product cards animation with slight delay for a nice effect
            const orderCards = document.querySelectorAll('.product-order-card');
            orderCards.forEach((card, index) => {
                setTimeout(() => {
                    card.classList.add('animated');
                }, 100 * index);
            });

            // Status select styling - enhance with improved color management
            const statusSelects = document.querySelectorAll('.status-select');
            statusSelects.forEach(select => {
                // Initialize the status colors based on current value
                const currentStatus = select.value.toLowerCase();
                select.classList.add('status-select-' + currentStatus);

                // Set initial border color to match status
                updateSelectBorderColor(select);

                select.addEventListener('change', function() {
                    const selectedStatus = this.value.toLowerCase();

                    // Remove old status classes
                    const classes = this.className.split(' ');
                    classes.forEach(className => {
                        if (className.startsWith('status-select-')) {
                            this.classList.remove(className);
                        }
                    });

                    // Add new status class
                    this.classList.add('status-select-' + selectedStatus);

                    // Update border color to match the new status
                    updateSelectBorderColor(this);
                });
            });

            // Function to update select border color based on status
            function updateSelectBorderColor(selectElement) {
                const status = selectElement.value.toLowerCase();
                let borderColor = '#ddd';

                switch (status) {
                    case 'pending':
                        borderColor = '#f5bc42';
                        break;
                    case 'processing':
                        borderColor = '#3498db';
                        break;
                    case 'shipped':
                        borderColor = '#9b59b6';
                        break;
                    case 'delivered':
                        borderColor = '#2ecc71';
                        break;
                    case 'cancelled':
                        borderColor = '#e74c3c';
                        break;
                }

                selectElement.style.borderColor = borderColor;
                selectElement.style.color = borderColor;
            }

            // Prevent form from submitting automatically - require explicit button click
            const statusForms = document.querySelectorAll('.status-update-form');
            statusForms.forEach(form => {
                const selectElement = form.querySelector('.status-select');

                selectElement.addEventListener('change', function(e) {
                    // Only show visual change, don't submit
                    e.preventDefault();
                });
            });
        });
    </script>
</body>

</html>
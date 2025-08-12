<?php
require_once 'includes/db_connect.php';

// Protect this page and ensure the user is a seller
if (!isset($_SESSION['loggedin']) || $_SESSION['user_role'] !== 'Seller') {
    header('Location: login.php');
    exit;
}

$seller_id = $_SESSION['user_id'];

// Get pagination parameters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

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

// Get total count for pagination
$count_sql = "SELECT COUNT(DISTINCT o.id) as total 
              FROM orders o
              JOIN order_items oi ON o.id = oi.order_id
              JOIN products p ON oi.product_id = p.id
              WHERE p.seller_id = ?" . $where_clause;

$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param($param_types, ...$params);
$count_stmt->execute();
$total_orders = $count_stmt->get_result()->fetch_assoc()['total'];
$count_stmt->close();

$total_pages = ceil($total_orders / $limit);

// Get orders with details - first get order IDs with pagination, then get details
// This approach fixes the pagination issue by separating the list retrieval from aggregation
// We'll use a simpler query to avoid any GROUP BY or complex joins that could affect pagination
$order_ids_sql = "SELECT DISTINCT o.id
                  FROM orders o 
                  JOIN order_items oi ON o.id = oi.order_id
                  JOIN products p ON oi.product_id = p.id
                  WHERE p.seller_id = ?" . $where_clause . "
                  GROUP BY o.id
                  ORDER BY MAX(o.created_at) DESC
                  LIMIT ? OFFSET ?";

// Create a new params array for the first query
$id_params = $params;
$id_params[] = $limit;
$id_params[] = $offset;
$id_param_types = $param_types . "ii";

$id_stmt = $conn->prepare($order_ids_sql);
$id_stmt->bind_param($id_param_types, ...$id_params);
$id_stmt->execute();
$id_result = $id_stmt->get_result();

$order_ids = [];
while ($row = $id_result->fetch_assoc()) {
    $order_ids[] = $row['id'];
}
$id_stmt->close();

$orders = [];

// If we have order IDs, get their full details
if (!empty($order_ids)) {
    // Convert the array of IDs to a comma-separated string for IN clause
    $id_list = implode(',', $order_ids);

    $orders_sql = "SELECT o.id AS order_id, o.status, o.notes, o.created_at,
                          u.name AS buyer_name, u.email AS buyer_email, u.phone AS buyer_phone,
                          CONCAT(u.city, ', ', u.district, ', ', u.division) AS location,
                          SUM(oi.quantity * oi.price) AS total_amount,
                          COUNT(oi.id) AS total_items,
                          (SELECT p2.image_path FROM order_items oi2 JOIN products p2 ON oi2.product_id = p2.id 
                           WHERE oi2.order_id = o.id AND p2.seller_id = ? LIMIT 1) AS product_image
                   FROM orders o
                   JOIN users u ON o.buyer_id = u.id
                   JOIN order_items oi ON o.id = oi.order_id
                   JOIN products p ON oi.product_id = p.id
                   WHERE o.id IN ($id_list) AND p.seller_id = ?
                   GROUP BY o.id, o.status, o.notes, o.created_at, u.name, u.email, u.phone,
                            u.city, u.district, u.division
                   ORDER BY FIELD(o.id, $id_list)";

    $stmt = $conn->prepare($orders_sql);
    $stmt->bind_param("ii", $seller_id, $seller_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    $stmt->close();
}

// Calculate stats
$stats_sql = "SELECT 
                COUNT(DISTINCT CASE WHEN o.status = 'Pending' THEN o.id END) as pending_count,
                COUNT(DISTINCT CASE WHEN o.status = 'Processing' THEN o.id END) as processing_count,
                COUNT(DISTINCT CASE WHEN o.status = 'Shipped' THEN o.id END) as shipped_count,
                COUNT(DISTINCT CASE WHEN o.status = 'Delivered' THEN o.id END) as delivered_count,
                COUNT(DISTINCT CASE WHEN o.status = 'Cancelled' THEN o.id END) as cancelled_count,
                SUM(CASE WHEN o.status = 'Delivered' THEN oi.quantity * oi.price ELSE 0 END) as total_revenue
              FROM orders o
              JOIN order_items oi ON o.id = oi.order_id
              JOIN products p ON oi.product_id = p.id
              WHERE p.seller_id = ?";

$stats_stmt = $conn->prepare($stats_sql);
$stats_stmt->bind_param("i", $seller_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();
$stats_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management - AgroKartBD</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/orders.css">
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
                <h1 class="page-title"><i class="fas fa-shopping-cart"></i> Orders Management</h1>
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

            <!-- Order Statistics -->
            <div class="stats-container">
                <div class="stat-card pending">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="fas fa-clock"></i></div><span>Pending Orders</span>
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
                        <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div><span>Total Revenue</span>
                    </div>
                    <div class="stat-info">
                        <h2>৳<?php echo number_format($stats['total_revenue'], 2); ?></h2>
                    </div>
                </div>
            </div>

            <!-- Orders List -->
            <div class="orders-list-container">
                <div class="list-header">
                    <h3>All Orders (<?php echo $total_orders; ?>)</h3>
                    <div class="filter-container">
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
                            <input type="hidden" name="page" value="1">
                        </form>
                    </div>
                </div>

                <?php if (count($orders) > 0): ?>
                    <div class="orders-list">
                        <?php foreach ($orders as $order): ?>
                            <div class="order-card">
                                <div class="order-header">
                                    <div class="order-info">
                                        <h4>Order #<?php echo $order['order_id']; ?></h4>
                                        <span class="order-date"><i class="fas fa-calendar"></i> <?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></span>
                                    </div>
                                    <div class="order-status-container">
                                        <form action="php/update_order_status.php" method="POST" class="status-form">
                                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                            <input type="hidden" name="redirect_to" value="orders">
                                            <select name="status" class="status-select <?php echo strtolower($order['status']); ?>" onchange="this.form.submit()">
                                                <?php
                                                $statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
                                                foreach ($statuses as $status) {
                                                    $selected = ($order['status'] == $status) ? 'selected' : '';
                                                    echo '<option value="' . $status . '" ' . $selected . '>' . $status . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </form>
                                    </div>
                                </div>

                                <div class="order-body">
                                    <div class="order-product-image">
                                        <?php
                                        // Handle image path
                                        $imagePath = !empty($order['product_image']) ? $order['product_image'] : 'images/AGrO.png';
                                        ?>
                                        <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Product Image" onerror="this.src='images/AGrO.png'">
                                    </div>
                                    <div class="customer-info">
                                        <div class="customer-details">
                                            <h5><i class="fas fa-user"></i> <?php echo htmlspecialchars($order['buyer_name']); ?></h5>
                                            <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($order['buyer_email']); ?></p>
                                            <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($order['buyer_phone']); ?></p>
                                            <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($order['location']); ?></p>
                                        </div>
                                    </div>

                                    <div class="order-summary">
                                        <div class="summary-item">
                                            <span class="label">Total Items:</span>
                                            <span class="value"><?php echo $order['total_items']; ?></span>
                                        </div>
                                        <div class="summary-item">
                                            <span class="label">Total Amount:</span>
                                            <span class="value">৳<?php echo number_format($order['total_amount'], 2); ?></span>
                                        </div>
                                    </div>
                                </div>

                                <?php if (!empty($order['notes'])): ?>
                                    <div class="order-notes">
                                        <i class="fas fa-sticky-note"></i>
                                        <span><?php echo htmlspecialchars($order['notes']); ?></span>
                                    </div>
                                <?php endif; ?>

                                <div class="order-actions">
                                    <button class="btn-view" onclick="viewOrderDetails(<?php echo $order['order_id']; ?>)">
                                        <i class="fas fa-eye"></i> View Details
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>&status=<?php echo $status_filter; ?>&date=<?php echo $date_filter; ?>" class="page-btn">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            <?php endif; ?>

                            <?php
                            $start = max(1, $page - 2);
                            $end = min($total_pages, $page + 2);

                            for ($i = $start; $i <= $end; $i++):
                            ?>
                                <a href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&date=<?php echo $date_filter; ?>"
                                    class="page-btn <?php echo $i === $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page + 1; ?>&status=<?php echo $status_filter; ?>&date=<?php echo $date_filter; ?>" class="page-btn">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

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

    <!-- Order Details Modal -->
    <div id="orderDetailsModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Order Details</h2>
            <div id="orderDetailsContent">
                <!-- Order details will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Order cards animation
            const orderCards = document.querySelectorAll('.order-card');
            orderCards.forEach((card, index) => {
                setTimeout(() => {
                    card.classList.add('animated');
                }, 100 * index);
            });

            // Modal functionality
            const modal = document.getElementById('orderDetailsModal');
            const closeBtn = document.querySelector('.close-modal');

            closeBtn.addEventListener('click', function() {
                modal.style.display = 'none';
                document.body.classList.remove('modal-open');
            });

            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                    document.body.classList.remove('modal-open');
                }
            });
        });

        // View order details function
        function viewOrderDetails(orderId) {
            const modal = document.getElementById('orderDetailsModal');
            const content = document.getElementById('orderDetailsContent');

            // Show loading
            content.innerHTML = '<div class="loading">Loading order details...</div>';
            modal.style.display = 'flex';
            document.body.classList.add('modal-open');

            // Fetch order details via AJAX
            fetch(`php/get_order_details.php?order_id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayOrderDetails(data.order);
                    } else {
                        content.innerHTML = '<div class="error">Error loading order details: ' + data.message + '</div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    content.innerHTML = '<div class="error">Error loading order details. Please try again.</div>';
                });
        }

        function displayOrderDetails(order) {
            const content = document.getElementById('orderDetailsContent');
            let itemsHtml = '';

            order.items.forEach(item => {
                itemsHtml += `
                    <div class="order-item">
                        <img src="${item.image_path}" alt="${item.name}" class="item-image" onerror="this.src='images/AGrO.png'">
                        <div class="item-details">
                            <h5>${item.name}</h5>
                            <p>Quantity: ${item.quantity} ${item.unit}</p>
                            <p>Price: ৳${parseFloat(item.price).toFixed(2)} per ${item.unit}</p>
                            <p class="item-total">Total: ৳${(item.quantity * item.price).toFixed(2)}</p>
                        </div>
                    </div>
                `;
            });

            content.innerHTML = `
                <div class="order-details-content">
                    <div class="order-summary-section">
                        <h3>Order Summary</h3>
                        <p><strong>Order ID:</strong> #${order.order_id}</p>
                        <p><strong>Status:</strong> <span class="status-badge ${order.status.toLowerCase()}">${order.status}</span></p>
                        <p><strong>Order Date:</strong> ${new Date(order.created_at).toLocaleDateString()}</p>
                        <p><strong>Total Amount:</strong> ৳${parseFloat(order.total_amount).toFixed(2)}</p>
                        ${order.notes ? `<p><strong>Notes:</strong> ${order.notes}</p>` : ''}
                    </div>
                    
                    <div class="customer-section">
                        <h3>Customer Information</h3>
                        <p><strong>Name:</strong> ${order.buyer_name}</p>
                        <p><strong>Email:</strong> ${order.buyer_email}</p>
                        <p><strong>Phone:</strong> ${order.buyer_phone}</p>
                        <p><strong>Address:</strong> ${order.location}</p>
                    </div>
                    
                    <div class="items-section">
                        <h3>Order Items</h3>
                        <div class="order-items-list">
                            ${itemsHtml}
                        </div>
                    </div>
                </div>
            `;
        }
    </script>
</body>

</html>
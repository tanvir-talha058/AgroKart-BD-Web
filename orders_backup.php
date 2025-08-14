<?php
require_once 'includes/db_connect.php';

// Protect this page and ensure the user is a seller
if (!isset($_SESSION['loggedin']) || $_SESSION['user_role'] !== 'Seller') {
    header('Location: login.php');
    exit;
}

$seller_id = $_SESSION['user_id'];

// Get all orders for this seller
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// First, get all products belonging to this seller
$product_sql = "SELECT id FROM products WHERE seller_id = ?";
$product_stmt = $conn->prepare($product_sql);
$product_stmt->bind_param("i", $seller_id);
$product_stmt->execute();
$product_result = $product_stmt->get_result();

$product_ids = [];
while ($product_row = $product_result->fetch_assoc()) {
    $product_ids[] = $product_row['id'];
}
$product_stmt->close();

// Debug info
$product_count = count($product_ids);
$product_id_list = implode(', ', $product_ids);

// If this seller has products, find orders containing those products
if (!empty($product_ids)) {
    $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
    $sql = "SELECT DISTINCT o.id AS order_id, o.status, o.total_amount, o.notes, o.created_at,
            u.name AS buyer_name, u.email AS buyer_email, u.phone AS buyer_phone,
            u.division, u.district, u.city, u.profile_image_path
            FROM orders o
            JOIN users u ON o.buyer_id = u.id
            JOIN order_items oi ON o.id = oi.order_id
            WHERE oi.product_id IN ($placeholders)
            ORDER BY o.created_at DESC";

    // Save SQL query for debugging
    $debug_sql = $sql . " (Product IDs: " . $product_id_list . ")";

    try {
        $stmt = $conn->prepare($sql);

        // Dynamically bind all product ID parameters
        $types = str_repeat('i', count($product_ids));
        $stmt->bind_param($types, ...$product_ids);

        $stmt->execute();
        $result = $stmt->get_result();
        $orders = [];

        // Check for errors
        if ($stmt->error) {
            echo '<div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 20px; border-radius: 5px;">
                <p><strong>SQL Error:</strong> ' . $stmt->error . '</p>
                </div>';
        }
    } catch (Exception $e) {
        echo '<div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 20px; border-radius: 5px;">
            <p><strong>Exception:</strong> ' . $e->getMessage() . '</p>
            </div>';
    }
} else {
    // If seller has no products, empty result set
    $orders = [];
    $debug_sql = "No products found for this seller";
    $result = false;
}

// Count the number of rows for debugging
$row_count = $result ? $result->num_rows : 0;

if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Now let's get payment information and total items for each order
        $order_id = $row['order_id'];

        // Get payment method
        $payment_sql = "SELECT method FROM payments WHERE order_id = ?";
        $payment_stmt = $conn->prepare($payment_sql);
        $payment_stmt->bind_param("i", $order_id);
        $payment_stmt->execute();
        $payment_result = $payment_stmt->get_result();

        if ($payment_row = $payment_result->fetch_assoc()) {
            $row['payment_method'] = $payment_row['method'];
        } else {
            $row['payment_method'] = 'Cash on Delivery';
        }
        $payment_stmt->close();

        // Count total items
        $items_sql = "SELECT COUNT(*) as total FROM order_items WHERE order_id = ?";
        $items_stmt = $conn->prepare($items_sql);
        $items_stmt->bind_param("i", $order_id);
        $items_stmt->execute();
        $items_result = $items_stmt->get_result();

        if ($items_row = $items_result->fetch_assoc()) {
            $row['total_items'] = $items_row['total'];
        } else {
            $row['total_items'] = 0;
        }
        $items_stmt->close();

        // Add complete order info to array
        $orders[] = $row;
    }

    if ($stmt) {
        $stmt->close();
    }
}

// Calculate some stats
$total_orders = count($orders);
$pending_orders = 0;
$shipped_orders = 0;
$delivered_orders = 0;
$total_revenue = 0;

foreach ($orders as $order) {
    if ($order['status'] == 'Pending' || $order['status'] == 'Processing') {
        $pending_orders++;
    } else if ($order['status'] == 'Shipped') {
        $shipped_orders++;
    } else if ($order['status'] == 'Delivered') {
        $delivered_orders++;
        $total_revenue += $order['total_amount'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - AgroKartBD</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/orders.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Add any additional CSS files here -->
    <link rel="stylesheet" href="css/orders-backup-style.css">
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
                <li><a href="orders.php" class="active"><span class="icon"><i class="fas fa-shopping-cart"></i></span>Orders</a></li>
                <li><a href="customers.php"><span class="icon"><i class="fas fa-users"></i></span>Customers</a></li>
                <li><a href="php/logout.php"><span class="icon"><i class="fas fa-sign-out-alt"></i></span>Logout</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="top-header">
                <h1 class="page-title"><i class="fas fa-shopping-cart"></i> Order Management</h1>
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
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div><span>Total Orders</span>
                    </div>
                    <div class="stat-info">
                        <h2><?php echo $total_orders; ?></h2>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="fas fa-clock"></i></div><span>Pending Orders</span>
                    </div>
                    <div class="stat-info">
                        <h2><?php echo $pending_orders; ?></h2>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="fas fa-shipping-fast"></i></div><span>Shipped Orders</span>
                    </div>
                    <div class="stat-info">
                        <h2><?php echo $shipped_orders; ?></h2>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div><span>Total Revenue</span>
                    </div>
                    <div class="stat-info">
                        <h2>৳<?php echo number_format($total_revenue, 2); ?></h2>
                    </div>
                </div>
            </div>

            <!-- Orders List -->
            <div class="orders-container">
                <div class="list-header">
                    <h3>All Orders</h3>
                    <div class="filter-container">
                        <div class="search-container">
                            <input type="text" id="orderSearch" placeholder="Search orders..." class="search-input">
                            <i class="fas fa-search search-icon"></i>
                        </div>
                        <select id="statusFilter" class="filter-select">
                            <option value="all">All Status</option>
                            <option value="Pending">Pending</option>
                            <option value="Processing">Processing</option>
                            <option value="Shipped">Shipped</option>
                            <option value="Delivered">Delivered</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                        <select id="dateFilter" class="filter-select">
                            <option value="all">All Time</option>
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                        </select>
                    </div>
                </div>

                <?php if (count($orders) > 0): ?>
                    <div class="orders-list" id="ordersContainer" style="display: flex; flex-direction: column; gap: 20px;">
                        <?php foreach ($orders as $order): ?>
                            <div class="order-card" data-status="<?php echo htmlspecialchars($order['status']); ?>" style="opacity: 1; transform: translateY(0); display: block; margin-bottom: 20px; background-color: #fff; border-radius: 10px; box-shadow: 0 3px 8px rgba(0, 0, 0, 0.05); border: 1px solid #eee;">
                                <div class="order-header" style="display: flex; justify-content: space-between; align-items: center; padding: 15px 20px; background-color: #f8f8f8; border-bottom: 1px solid #eee;">
                                    <div class="order-id" style="font-weight: 600; color: #333;">Order #<?php echo $order['order_id']; ?></div>
                                    <div class="order-date" style="font-size: 0.9rem; color: #666;"><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></div>
                                    <div class="order-status status-<?php echo strtolower($order['status']); ?>" style="display: flex; align-items: center; font-size: 0.9rem; font-weight: 500; gap: 6px;">
                                        <span class="status-dot" style="width: 10px; height: 10px; border-radius: 50%; background-color: 
                                        <?php
                                        switch (strtolower($order['status'])) {
                                            case 'pending':
                                                echo '#FFC107;';
                                                break;
                                            case 'processing':
                                                echo '#2196F3;';
                                                break;
                                            case 'shipped':
                                                echo '#9C27B0;';
                                                break;
                                            case 'delivered':
                                                echo '#4CAF50;';
                                                break;
                                            case 'cancelled':
                                                echo '#F44336;';
                                                break;
                                            default:
                                                echo '#ccc;';
                                                break;
                                        }
                                        ?>
                                        "></span>
                                        <?php echo $order['status']; ?>
                                    </div>
                                </div>

                                <div class="order-body" style="padding: 20px; display: flex; flex-wrap: wrap; gap: 20px;">
                                    <div class="customer-info" style="flex: 1; min-width: 250px; display: flex; gap: 15px;">
                                        <div class="customer-profile" style="width: 60px; height: 60px; border-radius: 50%; overflow: hidden; background-color: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                                            <?php if (!empty($order['profile_image_path'])): ?>
                                                <img src="<?php echo htmlspecialchars($order['profile_image_path']); ?>" alt="<?php echo htmlspecialchars($order['buyer_name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="avatar-placeholder" style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background-color: #e0e0e0; color: #757575;">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="customer-details" style="display: flex; flex-direction: column; gap: 5px;">
                                            <h4 style="margin: 0 0 5px 0; color: #333;"><?php echo htmlspecialchars($order['buyer_name']); ?></h4>
                                            <div style="font-size: 0.9rem; color: #666;"><i class="fas fa-envelope" style="color: #4CAF50; width: 16px;"></i> <?php echo htmlspecialchars($order['buyer_email']); ?></div>
                                            <div style="font-size: 0.9rem; color: #666;"><i class="fas fa-phone" style="color: #4CAF50; width: 16px;"></i> <?php echo htmlspecialchars($order['buyer_phone']); ?></div>
                                            <div style="font-size: 0.9rem; color: #666;"><i class="fas fa-map-marker-alt" style="color: #4CAF50; width: 16px;"></i>
                                                <?php
                                                $location = [];
                                                if (!empty($order['city'])) $location[] = $order['city'];
                                                if (!empty($order['district'])) $location[] = $order['district'];
                                                if (!empty($order['division'])) $location[] = $order['division'];
                                                echo !empty($location) ? htmlspecialchars(implode(', ', $location)) : 'Location not provided';
                                                ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="order-details" style="flex: 2; min-width: 300px;">
                                        <div class="order-meta" style="display: flex; gap: 20px; margin-bottom: 15px; flex-wrap: wrap;">
                                            <div class="meta-item" style="display: flex; align-items: center; gap: 8px; background-color: #f5f5f5; padding: 8px 15px; border-radius: 20px; font-size: 0.9rem;">
                                                <i class="fas fa-box" style="color: #4CAF50;"></i>
                                                <span><?php echo isset($order['total_items']) ? $order['total_items'] : '1'; ?> Items</span>
                                            </div>
                                            <div class="meta-item" style="display: flex; align-items: center; gap: 8px; background-color: #f5f5f5; padding: 8px 15px; border-radius: 20px; font-size: 0.9rem;">
                                                <i class="fas fa-money-bill-alt" style="color: #4CAF50;"></i>
                                                <span>৳<?php echo number_format($order['total_amount'], 2); ?></span>
                                            </div>
                                            <div class="meta-item" style="display: flex; align-items: center; gap: 8px; background-color: #f5f5f5; padding: 8px 15px; border-radius: 20px; font-size: 0.9rem;">
                                                <i class="fas fa-credit-card" style="color: #4CAF50;"></i>
                                                <span><?php echo isset($order['payment_method']) ? htmlspecialchars($order['payment_method']) : 'Cash on Delivery'; ?></span>
                                            </div>
                                        </div>

                                        <?php if (!empty($order['notes'])): ?>
                                            <div class="order-notes" style="background-color: #fffde7; padding: 10px 15px; border-radius: 8px; margin-top: 10px; display: flex; align-items: flex-start; gap: 10px;">
                                                <i class="fas fa-sticky-note" style="color: #FFC107; margin-top: 3px;"></i>
                                                <p style="margin: 0; font-size: 0.9rem; color: #666;"><?php echo htmlspecialchars($order['notes']); ?></p>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Order Items Section -->
                                        <div class="order-items-section" style="margin-top: 15px;">
                                            <div class="view-details-btn" onclick="toggleOrderItems(<?php echo $order['order_id']; ?>)" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 15px; background-color: #f5f5f5; border-radius: 8px; cursor: pointer; font-size: 0.9rem; transition: background-color 0.3s ease;">
                                                <span>View Items</span>
                                                <i class="fas fa-chevron-down"></i>
                                            </div>

                                            <div id="orderItems-<?php echo $order['order_id']; ?>" class="order-items-container" style="display: none; margin-top: 15px; background-color: #f9f9f9; border-radius: 8px; padding: 15px;">
                                                <!-- Order items will be loaded via AJAX -->
                                                <div class="loading-spinner" style="display: flex; align-items: center; justify-content: center; gap: 10px; padding: 20px; color: #666;">
                                                    <i class="fas fa-spinner fa-pulse"></i> Loading order items...
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="order-actions" style="display: flex; justify-content: space-between; align-items: center; padding: 15px 20px; background-color: #f8f8f8; border-top: 1px solid #eee; flex-wrap: wrap; gap: 15px;">
                                    <form action="php/update_order_status.php" method="POST" class="status-form" style="display: flex; align-items: center; gap: 10px;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                        <div class="status-select-container" style="display: flex; align-items: center; gap: 10px;">
                                            <label for="status-<?php echo $order['order_id']; ?>">Status:</label>
                                            <select name="status" id="status-<?php echo $order['order_id']; ?>" class="status-select" onchange="this.form.submit()" style="padding: 8px 15px; border: 1px solid #ddd; border-radius: 4px; background-color: #fff; font-size: 0.9rem;">
                                                <option value="Pending" <?php echo $order['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="Processing" <?php echo $order['status'] == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                                <option value="Shipped" <?php echo $order['status'] == 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                <option value="Delivered" <?php echo $order['status'] == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                <option value="Cancelled" <?php echo $order['status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                        </div>
                                    </form>
                                    <button class="print-invoice-btn" onclick="printInvoice(<?php echo $order['order_id']; ?>)" style="background-color: #4CAF50; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 0.9rem; transition: background-color 0.3s ease;">
                                        <i class="fas fa-print"></i> Print Invoice
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <!-- If no orders, add a sample test order to verify display is working -->
                    <div class="orders-list">
                        <div class="order-card animated" data-status="Pending" style="opacity: 1; transform: translateY(0); display: block;">
                            <div class="order-header">
                                <div class="order-id">Order #TEST-123</div>
                                <div class="order-date">13 Aug 2025, 10:00 AM</div>
                                <div class="order-status status-pending">
                                    <span class="status-dot"></span>
                                    Pending
                                </div>
                            </div>
                            <div class="order-body">
                                <div class="customer-info">
                                    <div class="customer-profile">
                                        <div class="avatar-placeholder">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    </div>
                                    <div class="customer-details">
                                        <h4>Test Customer</h4>
                                        <div><i class="fas fa-envelope"></i> test@example.com</div>
                                        <div><i class="fas fa-phone"></i> +880123456789</div>
                                        <div><i class="fas fa-map-marker-alt"></i> Dhaka, Bangladesh</div>
                                    </div>
                                </div>
                                <div class="order-details">
                                    <div class="order-meta">
                                        <div class="meta-item">
                                            <i class="fas fa-box"></i>
                                            <span>2 Items</span>
                                        </div>
                                        <div class="meta-item">
                                            <i class="fas fa-money-bill-alt"></i>
                                            <span>৳500.00</span>
                                        </div>
                                        <div class="meta-item">
                                            <i class="fas fa-credit-card"></i>
                                            <span>Cash on Delivery</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="no-orders" style="margin-top: 20px;">
                        <div class="no-data-icon"><i class="fas fa-shopping-cart"></i></div>
                        <h3>No Orders Found</h3>
                        <p>You don't have any orders yet. When customers purchase your products, they will appear here.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Order cards animation - make all order cards immediately visible
            const orderCards = document.querySelectorAll('.order-card');
            orderCards.forEach(card => {
                card.classList.add('animated');
            });

            // Search and Filter functionality
            const searchInput = document.getElementById('orderSearch');
            const statusFilter = document.getElementById('statusFilter');
            const dateFilter = document.getElementById('dateFilter');

            function filterOrders() {
                const searchTerm = searchInput.value.toLowerCase();
                const statusValue = statusFilter.value;
                const dateValue = dateFilter.value;

                const today = new Date();
                const weekAgo = new Date(today);
                weekAgo.setDate(today.getDate() - 7);
                const monthAgo = new Date(today);
                monthAgo.setMonth(today.getMonth() - 1);

                orderCards.forEach(card => {
                    // Get card data
                    const orderText = card.textContent.toLowerCase();
                    const status = card.dataset.status;
                    const orderDateText = card.querySelector('.order-date').textContent;
                    const orderDate = new Date(orderDateText);

                    // Check if card matches filters
                    const matchesSearch = orderText.includes(searchTerm);
                    const matchesStatus = statusValue === 'all' || status === statusValue;

                    let matchesDate = true;
                    if (dateValue === 'today') {
                        matchesDate = orderDate.toDateString() === today.toDateString();
                    } else if (dateValue === 'week') {
                        matchesDate = orderDate >= weekAgo;
                    } else if (dateValue === 'month') {
                        matchesDate = orderDate >= monthAgo;
                    }

                    if (matchesSearch && matchesStatus && matchesDate) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }

            searchInput.addEventListener('input', filterOrders);
            statusFilter.addEventListener('change', filterOrders);
            dateFilter.addEventListener('change', filterOrders);
        });

        // Toggle order items visibility and load data via AJAX
        function toggleOrderItems(orderId) {
            const itemsContainer = document.getElementById(`orderItems-${orderId}`);
            const button = itemsContainer.previousElementSibling;
            const icon = button.querySelector('i');

            if (itemsContainer.style.display === 'none') {
                itemsContainer.style.display = 'block';
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');

                // Check if items have been loaded already
                if (itemsContainer.querySelector('.loading-spinner')) {
                    // Load items via AJAX
                    fetch(`php/get_order_details.php?id=${orderId}`)
                        .then(response => response.json())
                        .then(data => {
                            let html = '<div class="order-items-list">';

                            if (data.items && data.items.length > 0) {
                                data.items.forEach(item => {
                                    html += `
                                        <div class="order-item">
                                            <div class="product-image">
                                                <img src="${item.image_path}" alt="${item.name}">
                                            </div>
                                            <div class="product-details">
                                                <h4>${item.name}</h4>
                                                <div class="product-meta">
                                                    <span class="price">৳${parseFloat(item.price).toFixed(2)} / ${item.unit}</span>
                                                    <span class="quantity">Quantity: ${item.quantity}</span>
                                                    <span class="subtotal">Subtotal: ৳${(parseFloat(item.price) * item.quantity).toFixed(2)}</span>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                                });
                            } else {
                                html += '<p>No items found for this order.</p>';
                            }

                            html += '</div>';
                            itemsContainer.innerHTML = html;
                        })
                        .catch(error => {
                            itemsContainer.innerHTML = `<p class="error-text">Error loading order items: ${error.message}</p>`;
                        });
                }
            } else {
                itemsContainer.style.display = 'none';
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        }

        // Print invoice function
        function printInvoice(orderId) {
            window.open(`invoice.php?id=${orderId}`, '_blank');
        }
    </script>
    <!-- Include the orders.js script -->
    <script src="js/orders.js"></script>

    <!-- Direct inline script to ensure orders are visible -->
    <script>
        // Ensure all orders are visible
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM fully loaded');

            // Debug info
            const orderCards = document.querySelectorAll('.order-card');
            console.log('Found ' + orderCards.length + ' order cards');

            // Force all cards to be visible
            orderCards.forEach(function(card) {
                card.style.display = 'block';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
                console.log('Card made visible:', card.querySelector('.order-id').textContent);
            });

            // Toggle order items function
            window.toggleOrderItems = function(orderId) {
                console.log('Toggle items for order', orderId);
                const itemsContainer = document.getElementById('orderItems-' + orderId);
                const button = itemsContainer.previousElementSibling;
                const icon = button.querySelector('i');

                if (itemsContainer.style.display === 'none') {
                    itemsContainer.style.display = 'block';
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');

                    // Load items via AJAX
                    fetch('php/get_order_details.php?id=' + orderId)
                        .then(response => response.json())
                        .then(data => {
                            console.log('Order details loaded:', data);
                            let html = '<div class="order-items-list" style="display: flex; flex-direction: column; gap: 10px;">';

                            if (data.items && data.items.length > 0) {
                                data.items.forEach(item => {
                                    html += `
                                        <div class="order-item" style="display: flex; gap: 15px; background-color: #fff; padding: 10px; border-radius: 8px; border: 1px solid #eee;">
                                            <div class="product-image" style="width: 60px; height: 60px; border-radius: 8px; overflow: hidden;">
                                                <img src="${item.image_path}" alt="${item.name}" style="width: 100%; height: 100%; object-fit: cover;">
                                            </div>
                                            <div class="product-details" style="flex: 1;">
                                                <h4 style="margin: 0 0 5px 0; font-size: 1rem;">${item.name}</h4>
                                                <div class="product-meta" style="display: flex; gap: 15px; font-size: 0.85rem; color: #666; flex-wrap: wrap;">
                                                    <span class="price">৳${parseFloat(item.price).toFixed(2)} / ${item.unit}</span>
                                                    <span class="quantity">Quantity: ${item.quantity}</span>
                                                    <span class="subtotal">Subtotal: ৳${(parseFloat(item.price) * item.quantity).toFixed(2)}</span>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                                });
                            } else {
                                html += '<p style="text-align: center; padding: 15px; color: #666;">No items found for this order.</p>';
                            }

                            html += '</div>';
                            itemsContainer.innerHTML = html;
                        })
                        .catch(error => {
                            console.error('Error loading order items:', error);
                            itemsContainer.innerHTML = `<p style="color: #f44336; padding: 10px;">Error loading order items: ${error.message}</p>`;
                        });
                } else {
                    itemsContainer.style.display = 'none';
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                }
            };

            // Print invoice function
            window.printInvoice = function(orderId) {
                console.log('Print invoice for order', orderId);
                window.open('invoice.php?id=' + orderId, '_blank');
            };
        });
    </script>
</body>

</html>
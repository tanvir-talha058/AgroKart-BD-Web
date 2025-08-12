<?php
require_once 'includes/db_connect.php';

// Protect this page and ensure the user is a seller
if (!isset($_SESSION['loggedin']) || $_SESSION['user_role'] !== 'Seller') {
    header('Location: login.php');
    exit;
}


$seller_id = $_SESSION['user_id'];

// --- Fetch All Dashboard Statistics ---

// Total Orders: Count distinct orders that contain at least one product from this seller
$stmt_total_orders = $conn->prepare("SELECT COUNT(DISTINCT o.id) AS total_orders FROM orders o JOIN order_items oi ON o.id = oi.order_id JOIN products p ON oi.product_id = p.id WHERE p.seller_id = ?");
$stmt_total_orders->bind_param("i", $seller_id);
$stmt_total_orders->execute();
$total_orders = $stmt_total_orders->get_result()->fetch_assoc()['total_orders'] ?? 0;
$stmt_total_orders->close();

// Total Sell: Sum of (quantity * price) for items sold by this seller in 'Delivered' orders
$stmt_total_sell = $conn->prepare("SELECT SUM(oi.quantity * oi.price) AS total_sell FROM order_items oi JOIN products p ON oi.product_id = p.id JOIN orders o ON oi.order_id = o.id WHERE p.seller_id = ? AND o.status = 'Delivered'");
$stmt_total_sell->bind_param("i", $seller_id);
$stmt_total_sell->execute();
$total_sell = $stmt_total_sell->get_result()->fetch_assoc()['total_sell'] ?? 0;
$stmt_total_sell->close();

// Total Products: Count of all products listed by this seller
$stmt_total_products = $conn->prepare("SELECT COUNT(id) AS total_products FROM products WHERE seller_id = ?");
$stmt_total_products->bind_param("i", $seller_id);
$stmt_total_products->execute();
$total_products = $stmt_total_products->get_result()->fetch_assoc()['total_products'] ?? 0;
$stmt_total_products->close();

// Pending Orders for the summary card
$stmt_pending = $conn->prepare("SELECT COUNT(DISTINCT o.id) AS pending_orders FROM orders o JOIN order_items oi ON o.id = oi.order_id JOIN products p ON oi.product_id = p.id WHERE p.seller_id = ? AND o.status = 'Pending'");
$stmt_pending->bind_param("i", $seller_id);
$stmt_pending->execute();
$pending_orders = $stmt_pending->get_result()->fetch_assoc()['pending_orders'] ?? 0;
$stmt_pending->close();

// Shipped Orders for the summary card
$stmt_shipped = $conn->prepare("SELECT COUNT(DISTINCT o.id) AS shipped_orders FROM orders o JOIN order_items oi ON o.id = oi.order_id JOIN products p ON oi.product_id = p.id WHERE p.seller_id = ? AND o.status = 'Shipped'");
$stmt_shipped->bind_param("i", $seller_id);
$stmt_shipped->execute();
$shipped_orders = $stmt_shipped->get_result()->fetch_assoc()['shipped_orders'] ?? 0;
$stmt_shipped->close();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard - AgroKartBD</title>
    <link rel="stylesheet" href="css/dashboard.css">
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
                <li class="active"><a href="#"><span class="icon"><i class="fas fa-chart-bar"></i></span>Dashboard</a></li>
                <li><a href="php/logout.php"><span class="icon"><i class="fas fa-sign-out-alt"></i></span>Logout</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="top-header">
                <button class="add-product">Add New Product</button>
                <div class="header-right">
                    <div class="user-profile">
                        <img src="images/profile.jpg" alt="Profile">
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

            <!-- Statistics Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">🛍️</div><span>Total Orders</span>
                    </div>
                    <div class="stat-info">
                        <h2><?php echo $total_orders; ?></h2>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">💰</div><span>Total Sell</span>
                    </div>
                    <div class="stat-info">
                        <h2>৳<?php echo number_format($total_sell, 2); ?></h2>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">📦</div><span>Total Products</span>
                    </div>
                    <div class="stat-info">
                        <h2><?php echo $total_products; ?></h2>
                    </div>
                </div>
            </div>

            <div class="dashboard-cards-container">
                <!-- Order Summary Card -->
                <div class="order-summary-card">
                    <h3>Order Summary</h3>
                    <div class="progress-items">
                        <div class="progress-item">
                            <div class="progress-header">
                                <span>Pending Orders</span>
                                <span class="progress-value"><?php echo $pending_orders; ?>/<?php echo $total_orders; ?> Orders</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress" style="width: <?php echo $total_orders > 0 ? ($pending_orders / $total_orders) * 100 : 0; ?>%; background-color: #FFA500;"></div>
                            </div>
                        </div>
                        <div class="progress-item">
                            <div class="progress-header">
                                <span>Shipped Orders</span>
                                <span class="progress-value"><?php echo $shipped_orders; ?>/<?php echo $total_orders; ?> Orders</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress" style="width: <?php echo $total_orders > 0 ? ($shipped_orders / $total_orders) * 100 : 0; ?>%; background-color: #9155FD;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Summary Card -->
                <div class="payment-summary-card">
                    <h3>Payment Summary</h3>
                    <div class="payment-data">
                        <?php
                        // Query to get payment summary data for this seller
                        $payment_sql = "SELECT 
                            COUNT(DISTINCT p.id) AS total_payments,
                            SUM(oi.quantity * oi.price) AS total_amount,
                            COUNT(DISTINCT CASE WHEN p.status = 'Completed' THEN p.id END) AS completed_payments,
                            COUNT(DISTINCT CASE WHEN p.status = 'Pending' THEN p.id END) AS pending_payments
                        FROM payments p
                        JOIN orders o ON p.order_id = o.id
                        JOIN order_items oi ON o.id = oi.order_id
                        JOIN products pr ON oi.product_id = pr.id
                        WHERE pr.seller_id = ?";

                        $stmt_payment = $conn->prepare($payment_sql);
                        $stmt_payment->bind_param("i", $seller_id);
                        $stmt_payment->execute();
                        $payment_result = $stmt_payment->get_result();
                        $payment_data = $payment_result->fetch_assoc();

                        // Get payment methods breakdown
                        $methods_sql = "SELECT 
                            p.method, 
                            COUNT(DISTINCT p.id) AS count,
                            SUM(oi.quantity * oi.price) AS total
                        FROM payments p
                        JOIN orders o ON p.order_id = o.id
                        JOIN order_items oi ON o.id = oi.order_id
                        JOIN products pr ON oi.product_id = pr.id
                        WHERE pr.seller_id = ?
                        GROUP BY p.method";

                        $stmt_methods = $conn->prepare($methods_sql);
                        $stmt_methods->bind_param("i", $seller_id);
                        $stmt_methods->execute();
                        $methods_result = $stmt_methods->get_result();

                        if ($payment_result->num_rows > 0) {
                            echo '<div class="payment-summary-stats">';
                            echo '<div class="payment-stat"><span>Total Payments:</span> <strong>' . $payment_data['total_payments'] . '</strong></div>';
                            echo '<div class="payment-stat"><span>Total Amount:</span> <strong>৳' . number_format($payment_data['total_amount'], 2) . '</strong></div>';
                            echo '<div class="payment-stat"><span>Completed:</span> <strong>' . $payment_data['completed_payments'] . '</strong></div>';
                            echo '<div class="payment-stat"><span>Pending:</span> <strong>' . $payment_data['pending_payments'] . '</strong></div>';
                            echo '</div>';

                            if ($methods_result->num_rows > 0) {
                                echo '<div class="payment-methods">';
                                echo '<h4>Payment Methods</h4>';
                                echo '<ul>';
                                while ($method = $methods_result->fetch_assoc()) {
                                    echo '<li><div class="method-name">' . htmlspecialchars($method['method']) . '</div>';
                                    echo '<div class="method-count">' . $method['count'] . ' payments</div>';
                                    echo '<div class="method-total">৳' . number_format($method['total'], 2) . '</div></li>';
                                }
                                echo '</ul>';
                                echo '</div>';
                            }
                        } else {
                            echo '<p class="no-data">No payment data available yet.</p>';
                        }

                        $stmt_payment->close();
                        $stmt_methods->close();
                        ?>
                    </div>
                </div>

                <!-- Review Orders Card -->
                <div class="review-orders-card">
                    <div class="card-header">
                        <h3>Recent Orders</h3>
                    </div>
                    <div class="orders-list">
                        <?php
                        $orders_sql = "SELECT o.id AS order_id, o.status, o.notes, u.name AS buyer_name, CONCAT(u.city, ', ', u.district) AS location, o.created_at 
               FROM orders o
               JOIN users u ON o.buyer_id = u.id
               WHERE o.id IN (SELECT DISTINCT oi.order_id FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE p.seller_id = ?)
               ORDER BY o.created_at DESC LIMIT 5";
                        $stmt_orders = $conn->prepare($orders_sql);
                        $stmt_orders->bind_param("i", $seller_id);
                        $stmt_orders->execute();
                        $orders_result = $stmt_orders->get_result();

                        if ($orders_result->num_rows > 0) {
                            while ($order = $orders_result->fetch_assoc()) {
                                echo '<div class="order-item">';
                                echo '<div class="order-date">' . date('d/m/Y', strtotime($order['created_at'])) . '</div>';
                                echo '<div class="order-details"><div class="product-name">Order #' . $order['order_id'] . '</div><div class="order-id">Buyer: ' . htmlspecialchars($order['buyer_name']) . '</div>';

                                // Add order notes if they exist
                                if (!empty($order['notes'])) {
                                    echo '<div class="order-notes"><i class="fas fa-sticky-note"></i> ' . htmlspecialchars($order['notes']) . '</div>';
                                }

                                echo '</div>';
                                echo '<div class="company-details"><div class="location">' . htmlspecialchars($order['location']) . '</div></div>';
                                echo '<form action="php/update_order_status.php" method="POST" class="status-form">';
                                echo '<input type="hidden" name="order_id" value="' . $order['order_id'] . '">';
                                echo '<select name="status" class="status ' . strtolower($order['status']) . '" onchange="this.form.submit()">';
                                $statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
                                foreach ($statuses as $status) {
                                    $selected = ($order['status'] == $status) ? 'selected' : '';
                                    echo '<option value="' . $status . '" ' . $selected . '>' . $status . '</option>';
                                }
                                echo '</select></form>';
                                echo '</div>';
                            }
                        } else {
                            echo '<p>No recent orders found.</p>';
                        }
                        $stmt_orders->close();
                        ?>
                    </div>
                </div>
            </div>

            <!-- Add New Product Modal -->
            <div id="addProductModal" class="modal">
                <div class="modal-content">
                    <span class="close-modal">&times;</span>
                    <h2>Add New Product</h2>
                    <form class="add-product-form" action="php/add_product_process.php" method="POST" enctype="multipart/form-data">
                        <label>Product Name</label>
                        <input type="text" name="product_name" placeholder="Enter product name" required />
                        <label>Category</label>
                        <select name="category" required>
                            <option value="">Select category</option>
                            <option value="Vegetable">Vegetable</option>
                            <option value="Fruit">Fruit</option>
                            <option value="Spice">Spice</option>
                        </select>
                        <label>Price (৳)</label>
                        <input type="number" name="price" placeholder="Enter price" min="0" step="0.01" required />
                        <label>Stock Quantity</label>
                        <input type="number" name="stock" placeholder="Enter stock quantity" min="0" required />
                        <label>Product Image</label>
                        <input type="file" name="product_image" accept="image/*" required />
                        <label>Description</label>
                        <textarea name="description" placeholder="Enter product description" rows="3" required></textarea>
                        <button type="submit" class="submit-btn">Add Product</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <script src="js/dashboard.js"></script>
</body>

</html>
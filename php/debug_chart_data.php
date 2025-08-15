<?php
// Debug chart data to see what's really happening
require_once '../includes/db_connect.php';

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

echo "<h2>Chart Data Debug Information</h2>";
echo "<p>Current Date: " . date('Y-m-d H:i:s') . "</p>";

// Check session
echo "<h3>Session Information:</h3>";
echo "Logged in: " . (isset($_SESSION['loggedin']) ? 'Yes' : 'No') . "<br>";
echo "User Role: " . ($_SESSION['user_role'] ?? 'Not set') . "<br>";
echo "User ID: " . ($_SESSION['user_id'] ?? 'Not set') . "<br>";

if (!isset($_SESSION['loggedin']) || $_SESSION['user_role'] !== 'Seller') {
    echo "<p style='color: red;'>Not logged in as seller!</p>";
    exit;
}

$seller_id = $_SESSION['user_id'];
echo "Seller ID: $seller_id<br>";

// Check what products this seller has
echo "<h3>Your Products:</h3>";
$products_query = "SELECT id, name, seller_id FROM products WHERE seller_id = ?";
$stmt = $conn->prepare($products_query);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$products_result = $stmt->get_result();

echo "<table border='1'>";
echo "<tr><th>Product ID</th><th>Name</th><th>Seller ID</th></tr>";
while ($product = $products_result->fetch_assoc()) {
    echo "<tr><td>{$product['id']}</td><td>{$product['name']}</td><td>{$product['seller_id']}</td></tr>";
}
echo "</table>";

// Check orders for this seller
echo "<h3>Orders with Your Products:</h3>";
$orders_query = "SELECT o.id, o.status, o.created_at, o.delivered_at, oi.quantity, oi.price, p.name as product_name
                 FROM orders o 
                 JOIN order_items oi ON o.id = oi.order_id 
                 JOIN products p ON oi.product_id = p.id 
                 WHERE p.seller_id = ? 
                 ORDER BY o.created_at DESC 
                 LIMIT 10";
$stmt = $conn->prepare($orders_query);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$orders_result = $stmt->get_result();

echo "<table border='1'>";
echo "<tr><th>Order ID</th><th>Product</th><th>Status</th><th>Quantity</th><th>Price</th><th>Created</th><th>Delivered</th></tr>";
while ($order = $orders_result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$order['id']}</td>";
    echo "<td>{$order['product_name']}</td>";
    echo "<td>{$order['status']}</td>";
    echo "<td>{$order['quantity']}</td>";
    echo "<td>{$order['price']}</td>";
    echo "<td>{$order['created_at']}</td>";
    echo "<td>{$order['delivered_at']}</td>";
    echo "</tr>";
}
echo "</table>";

// Test weekly data query
echo "<h3>Weekly Sales Debug:</h3>";
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dayName = date('D', strtotime("-$i days"));

    $salesQuery = "SELECT COALESCE(SUM(oi.price * oi.quantity), 0) as daily_sales,
                          COUNT(DISTINCT o.id) as order_count
                   FROM order_items oi
                   JOIN orders o ON oi.order_id = o.id
                   JOIN products p ON oi.product_id = p.id
                   WHERE p.seller_id = ? 
                   AND DATE(COALESCE(o.delivered_at, o.created_at)) = ?
                   AND o.status = 'Delivered'";

    $stmt = $conn->prepare($salesQuery);
    $stmt->bind_param("is", $seller_id, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    echo "$dayName ($date): Sales = {$row['daily_sales']}, Orders = {$row['order_count']}<br>";
}

echo "<h3>Raw Chart Data JSON:</h3>";
// Include the actual chart data function
include 'load_chart_data.php';

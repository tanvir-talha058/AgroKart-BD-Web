<?php
require_once '../includes/db_connect.php';
session_start();

echo "<h2>User and Seller Debug Info</h2>";
echo "<p>Current session user_id: " . ($_SESSION['user_id'] ?? 'Not set') . "</p>";
echo "<p>Current user_role: " . ($_SESSION['user_role'] ?? 'Not set') . "</p>";

// Check all users in the system
echo "<h3>All Users in System:</h3>";
$users_query = "SELECT id, name, email, role FROM users";
$users_result = $conn->query($users_query);
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>";
while ($user = $users_result->fetch_assoc()) {
    $highlight = ($user['id'] == $_SESSION['user_id']) ? " style='background-color: yellow;'" : "";
    echo "<tr$highlight><td>{$user['id']}</td><td>{$user['name']}</td><td>{$user['email']}</td><td>{$user['role']}</td></tr>";
}
echo "</table>";

// Check all products and their sellers
echo "<h3>All Products and Their Sellers:</h3>";
$products_query = "SELECT p.id, p.name, p.seller_id, u.name as seller_name 
                   FROM products p 
                   LEFT JOIN users u ON p.seller_id = u.id 
                   ORDER BY p.seller_id";
$products_result = $conn->query($products_query);
echo "<table border='1'>";
echo "<tr><th>Product ID</th><th>Product Name</th><th>Seller ID</th><th>Seller Name</th></tr>";
while ($product = $products_result->fetch_assoc()) {
    $highlight = ($product['seller_id'] == $_SESSION['user_id']) ? " style='background-color: lightgreen;'" : "";
    echo "<tr$highlight><td>{$product['id']}</td><td>{$product['name']}</td><td>{$product['seller_id']}</td><td>{$product['seller_name']}</td></tr>";
}
echo "</table>";

// Check delivered orders
echo "<h3>Delivered Orders by Seller:</h3>";
$orders_query = "SELECT p.seller_id, u.name as seller_name, 
                        COUNT(DISTINCT o.id) as order_count,
                        SUM(oi.price * oi.quantity) as total_sales,
                        DATE(o.created_at) as order_date
                 FROM orders o 
                 JOIN order_items oi ON o.id = oi.order_id 
                 JOIN products p ON oi.product_id = p.id
                 LEFT JOIN users u ON p.seller_id = u.id
                 WHERE o.status = 'Delivered'
                 GROUP BY p.seller_id, DATE(o.created_at)
                 ORDER BY order_date DESC, p.seller_id";
$orders_result = $conn->query($orders_query);
echo "<table border='1'>";
echo "<tr><th>Seller ID</th><th>Seller Name</th><th>Date</th><th>Orders</th><th>Sales</th></tr>";
while ($order = $orders_result->fetch_assoc()) {
    $highlight = ($order['seller_id'] == $_SESSION['user_id']) ? " style='background-color: lightblue;'" : "";
    echo "<tr$highlight><td>{$order['seller_id']}</td><td>{$order['seller_name']}</td><td>{$order['order_date']}</td><td>{$order['order_count']}</td><td>{$order['total_sales']}</td></tr>";
}
echo "</table>";

echo "<p><strong>If your current user ID doesn't match the seller ID of the products, that's the problem!</strong></p>";

<?php
// php/delete_customer.php
session_start();
require_once '../includes/db_connect.php';

// Check if user is logged in and is a seller
if (!isset($_SESSION['loggedin']) || $_SESSION['user_role'] !== 'Seller') {
    $_SESSION['error'] = "You don't have permission to perform this action.";
    header('Location: ../dashboard.php');
    exit;
}

$seller_id = $_SESSION['user_id'];

// Check if customer_id is provided
if (!isset($_POST['customer_id']) || empty($_POST['customer_id'])) {
    $_SESSION['error'] = "Invalid request. Customer ID is missing.";
    header('Location: ../customers.php');
    exit;
}

$customer_id = (int)$_POST['customer_id'];

// Verify that this customer has purchased from this seller
$check_sql = "SELECT COUNT(*) as count FROM orders o
              JOIN order_items oi ON o.id = oi.order_id
              JOIN products p ON oi.product_id = p.id
              WHERE o.buyer_id = ? AND p.seller_id = ?";

$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $customer_id, $seller_id);
$check_stmt->execute();
$result = $check_stmt->get_result();
$row = $result->fetch_assoc();
$check_stmt->close();

if ($row['count'] == 0) {
    $_SESSION['error'] = "This customer doesn't exist or hasn't purchased from you.";
    header('Location: ../customers.php');
    exit;
}

// Create a seller_hidden_customers table if it doesn't exist
$create_table_sql = "CREATE TABLE IF NOT EXISTS seller_hidden_customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    customer_id INT NOT NULL,
    hidden_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_seller_customer (seller_id, customer_id),
    FOREIGN KEY (seller_id) REFERENCES users(id),
    FOREIGN KEY (customer_id) REFERENCES users(id)
)";

$conn->query($create_table_sql);

// Add the customer to the hidden list
$hide_sql = "INSERT INTO seller_hidden_customers (seller_id, customer_id) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE hidden_at = CURRENT_TIMESTAMP";

$hide_stmt = $conn->prepare($hide_sql);
$hide_stmt->bind_param("ii", $seller_id, $customer_id);
$result = $hide_stmt->execute();
$hide_stmt->close();

if ($result) {
    $_SESSION['message'] = "Customer has been removed from your list successfully.";
} else {
    $_SESSION['error'] = "Failed to remove customer. Please try again.";
}

header('Location: ../customers.php');
exit;

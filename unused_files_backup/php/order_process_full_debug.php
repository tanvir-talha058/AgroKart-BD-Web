<?php
// Debug version of order_process.php

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Order Process Debug</h1>";

echo "<h2>PHP Info:</h2>";
echo "Request Method: " . $_SERVER["REQUEST_METHOD"] . "<br>";
echo "Current Time: " . date('Y-m-d H:i:s') . "<br>";

echo "<h2>Session Data:</h2>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

echo "<h2>POST Data:</h2>";
echo "<pre>" . print_r($_POST, true) . "</pre>";

// Check database connection
try {
    require_once '../includes/db_connect.php';
    echo "<h2>Database Connection:</h2>";
    echo "Connected successfully to database: " . $conn->get_server_info() . "<br>";
} catch (Exception $e) {
    echo "<h2>Database Error:</h2>";
    echo "Connection failed: " . $e->getMessage() . "<br>";
}

// Check login status
if (!isset($_SESSION['loggedin'])) {
    echo "<h2>Login Check:</h2>";
    echo "❌ User not logged in<br>";
} else {
    echo "<h2>Login Check:</h2>";
    echo "✅ User logged in<br>";
    echo "User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Not set') . "<br>";
    echo "User Role: " . (isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'Not set') . "<br>";
}

// Check cart status
if (empty($_SESSION['cart'])) {
    echo "<h2>Cart Check:</h2>";
    echo "❌ Cart is empty<br>";
} else {
    echo "<h2>Cart Check:</h2>";
    echo "✅ Cart has items<br>";
    echo "Cart contents: <pre>" . print_r($_SESSION['cart'], true) . "</pre>";
}

// Check POST data
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo "<h2>Request Method Check:</h2>";
    echo "❌ Not a POST request<br>";
} else {
    echo "<h2>Request Method Check:</h2>";
    echo "✅ POST request received<br>";
    
    if (empty($_POST['location'])) {
        echo "❌ Location not provided<br>";
    } else {
        echo "✅ Location: " . $_POST['location'] . "<br>";
    }
    
    if (empty($_POST['payment_method'])) {
        echo "❌ Payment method not provided<br>";
    } else {
        echo "✅ Payment method: " . $_POST['payment_method'] . "<br>";
    }
}

// Test what would happen
if ($_SERVER["REQUEST_METHOD"] === "POST" && 
    !empty($_SESSION['cart']) && 
    isset($_SESSION['loggedin']) && 
    $_SESSION['user_role'] === 'Buyer' &&
    !empty($_POST['location']) && 
    !empty($_POST['payment_method'])) {
    
    echo "<h2>✅ ALL CHECKS PASSED - Order would be processed</h2>";
    echo "<p>Setting test order ID and redirecting to payment success...</p>";
    
    $_SESSION['last_order_id'] = 999;
    echo '<a href="../payment_success.php" style="background: green; color: white; padding: 10px; text-decoration: none;">Test Payment Success Page</a>';
    
} else {
    echo "<h2>❌ SOME CHECKS FAILED - Order would NOT be processed</h2>";
    echo "<p>Review the checks above to see what's missing.</p>";
}

?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1 { color: #333; }
h2 { color: #666; border-bottom: 1px solid #ccc; }
pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
</style>

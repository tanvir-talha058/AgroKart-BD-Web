<?php
// Debug version to test order processing

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

echo "Debug Test - Order Processing<br>";
echo "SESSION loggedin: " . (isset($_SESSION['loggedin']) ? 'Yes' : 'No') . "<br>";
echo "SESSION user_role: " . (isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'Not set') . "<br>";
echo "REQUEST_METHOD: " . $_SERVER["REQUEST_METHOD"] . "<br>";
echo "Cart status: " . (empty($_SESSION['cart']) ? 'Empty' : 'Has items') . "<br>";

if (isset($_SESSION['cart'])) {
    echo "Cart contents: <pre>" . print_r($_SESSION['cart'], true) . "</pre>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    echo "POST data: <pre>" . print_r($_POST, true) . "</pre>";
    
    // Test redirect to payment success
    $_SESSION['last_order_id'] = 999; // Test order ID
    echo "<br>Setting test order ID and redirecting...<br>";
    echo '<a href="../payment_success.php">Click here to test payment success page</a>';
    
    // Uncomment this line to test redirect
    // header('Location: ../payment_success.php');
    // exit();
}
?>

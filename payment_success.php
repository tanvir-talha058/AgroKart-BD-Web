<?php
// FILE: payment_success.php

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if order ID exists in session
if (!isset($_SESSION['last_order_id'])) {
    header('Location: index.php');
    exit;
}

// Include header after checking for redirect
include 'includes/header.php';
$order_id = $_SESSION['last_order_id'];
unset($_SESSION['last_order_id']); // Clear it after use
?>
<div class="success-container">
    <div class="success-box">
        <i class="fas fa-check-circle"></i>
        <h1>Payment Successful!</h1>
        <p>Thank you for your purchase. Your order has been placed successfully.</p>
        <p>Your Order ID is: <strong>#<?php echo $order_id; ?></strong></p>
        <p>You can view your order details in the "My Orders" section.</p>
        <div class="receipt">
            <h3>Order Receipt (Simulated)</h3>
            <p><strong>Order ID:</strong> #<?php echo $order_id; ?></p>
            <p><strong>Date:</strong> <?php echo date('F j, Y, g:i a'); ?></p>
            <p>A detailed receipt has been sent to your email.</p>
        </div>
        <a href="index.php" class="btn-primary">Continue Shopping</a>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
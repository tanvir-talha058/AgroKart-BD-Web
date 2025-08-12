<?php
// Start the session and perform checks before any output
session_start();

// Add debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check login status and cart with detailed conditions
if (!isset($_SESSION['loggedin'])) {
    $_SESSION['error'] = "Please login to checkout";
    header('Location: login.php');
    exit;
}

if (empty($_SESSION['cart'])) {
    $_SESSION['error'] = "Your cart is empty";
    header('Location: cart.php');
    exit;
}

// Include header after all redirects
include 'includes/header.php';

// Initialize total
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    if (isset($item['price']) && isset($item['quantity'])) {
        $total += $item['price'] * $item['quantity'];
    }
}

// Add session debugging output (remove in production)
echo "<!-- Debug: ";
print_r($_SESSION);
echo " -->";
?>
<div class="checkout-container">
    <h1>Checkout</h1>
    <div class="checkout-content">
        <div class="order-summary">
            <h3>Order Summary</h3>
            <?php foreach ($_SESSION['cart'] as $item): ?>
            <div class="summary-item">
                <span><?php echo htmlspecialchars($item['name']); ?> (x<?php echo $item['quantity']; ?>)</span>
                <span>৳<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
            </div>
            <?php endforeach; ?>
            <hr>
            <div class="summary-total">
                <strong>Total</strong>
                <strong>৳<?php echo number_format($total, 2); ?></strong>
            </div>
        </div>
        <div class="checkout-form">
            <h3>Shipping & Payment</h3>
            <form action="php/order_process.php" method="POST">
                <div class="form-group">
                    <label for="location">Delivery Location</label>
                    <textarea name="location" id="location" rows="3" required><?php echo htmlspecialchars($_SESSION['user_location']); ?></textarea>
                </div>
                <div class="form-group">
                    <label>Payment Method</label>
                    <div class="payment-options">
                        <label>
                            <input type="radio" name="payment_method" value="bKash" required>
                            <img src="images/payment-methods/bkash.jpg" alt="bKash" class="payment-logo">
                            bKash
                        </label>
                        <label>
                            <input type="radio" name="payment_method" value="Nagad">
                            <img src="images/payment-methods/nagad.png" alt="Nagad" class="payment-logo">
                            Nagad
                        </label>
                        <label>
                            <input type="radio" name="payment_method" value="Card">
                            <img src="images/payment-methods/card.png" alt="Card" class="payment-logo">
                            Card
                        </label>
                        <label>
                            <input type="radio" name="payment_method" value="COD">
                            <img src="" alt="Cash on Delivery" class="payment-logo">
                            Cash on Delivery
                        </label>
                    </div>
                </div>
                <button type="submit" class="btn-primary btn-block">Place Order</button>
            </form>
        </div>
    </div>
</div>
<a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
<?php include 'includes/footer.php'; ?>
<?php
// FILE: checkout.php
include 'includes/header.php';
if (!isset($_SESSION['loggedin']) || empty($_SESSION['cart'])) {
    header('Location: index.php');
    exit;
}
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['quantity'];
}
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
                        <label><input type="radio" name="payment_method" value="bKash" required> bKash</label>
                        <label><input type="radio" name="payment_method" value="Nagad"> Nagad</label>
                        <label><input type="radio" name="payment_method" value="Card"> Card</label>
                        <label><input type="radio" name="payment_method" value="COD"> Cash on Delivery</label>
                    </div>
                </div>
                <button type="submit" class="btn-primary btn-block">Place Order</button>
            </form>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
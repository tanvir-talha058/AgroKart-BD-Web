<?php
// FILE: cart.php
include 'includes/header.php';
$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$total = 0;
?>
<div class="cart-container">
    <h1>Your Shopping Cart</h1>
    <?php
    if (isset($_SESSION['error'])) { echo '<p class="error-message">' . $_SESSION['error'] . '</p>'; unset($_SESSION['error']); }
    ?>
    <?php if (!empty($cart_items)): ?>
    <table class="cart-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cart_items as $product_id => $item): ?>
            <?php $item_total = $item['price'] * $item['quantity']; $total += $item_total; ?>
            <tr>
                <td>
                    <div class="product-info">
                        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <span><?php echo htmlspecialchars($item['name']); ?></span>
                    </div>
                </td>
                <td>৳<?php echo htmlspecialchars($item['price']); ?></td>
                <td>
                    <form action="php/cart_manager.php" method="POST" class="update-form">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <input type="hidden" name="action" value="update">
                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>" onchange="this.form.submit()">
                    </form>
                </td>
                <td>৳<?php echo number_format($item_total, 2); ?></td>
                <td>
                    <form action="php/cart_manager.php" method="POST">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <input type="hidden" name="action" value="remove">
                        <button type="submit" class="remove-btn">Remove</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="cart-summary">
        <h2>Cart Total: ৳<?php echo number_format($total, 2); ?></h2>
        <a href="checkout.php" class="btn-primary">Proceed to Checkout</a>
    </div>
    <?php else: ?>
    <div class="cart-empty">
        <p>Your cart is empty.</p>
        <a href="index.php" class="btn-primary">Continue Shopping</a>
    </div>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
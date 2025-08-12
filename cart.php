<?php
session_start();

// Include header after session start
include 'includes/header.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Initialize total
$cart_items = $_SESSION['cart'];
$total = 0;

// Remove incorrect cart item addition code
// This should only be in your add-to-cart handler
/*
$_SESSION['cart'][] = array(
    'id' => $product_id,
    'name' => $product_name,
    'price' => $product_price,
    'quantity' => $quantity
);
*/

// Calculate total
foreach ($cart_items as $item) {
    if (isset($item['price']) && isset($item['quantity'])) {
        $total += $item['price'] * $item['quantity'];
    }
}

// Add debugging if needed
if (isset($_SESSION['debug'])) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
?>

<!-- Enhanced Cart Section -->
<section class="cart-section">
    <div class="cart-background">
        <div class="cart-pattern"></div>
    </div>

    <div class="cart-container">
        <div class="cart-header">
            <div class="cart-title-wrapper">
                <div class="cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="cart-title-content">
                    <h1 class="cart-title">Your Shopping Cart</h1>
                    <p class="cart-subtitle">Review your selected items and proceed to checkout</p>
                </div>
            </div>
            <div class="cart-stats">
                <div class="stat-item">
                    <i class="fas fa-box"></i>
                    <span><?php echo count($cart_items); ?> Items</span>
                </div>
            </div>
        </div>

        <?php
        if (isset($_SESSION['error'])) {
            echo '<div class="error-message-wrapper">';
            echo '<div class="error-message">';
            echo '<i class="fas fa-exclamation-circle"></i>';
            echo '<span>' . $_SESSION['error'] . '</span>';
            echo '</div>';
            echo '</div>';
            unset($_SESSION['error']);
        }
        ?>

        <?php if (!empty($cart_items)): ?>
            <div class="cart-content">
                <div class="cart-items-section">
                    <div class="cart-items-header">
                        <h3>Cart Items</h3>
                        <span class="items-count"><?php echo count($cart_items); ?> items</span>
                    </div>

                    <div class="cart-items-list">
                        <?php foreach ($cart_items as $product_id => $item): ?>
                            <?php $item_total = $item['price'] * $item['quantity'];
                            $total += $item_total; ?>
                            <div class="cart-item-card">
                                <div class="item-image-container">
                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="item-image">
                                    <div class="item-overlay">
                                        <!-- Removed delete button from overlay -->
                                    </div>
                                </div>

                                <div class="item-details">
                                    <h4 class="item-name"><?php echo htmlspecialchars($item['name']); ?></h4>
                                    <div class="item-price">৳<?php echo htmlspecialchars($item['price']); ?></div>
                                </div>

                                <div class="item-quantity">
                                    <form action="php/cart_manager.php" method="POST" class="update-form">
                                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                        <input type="hidden" name="action" value="update">
                                        <div class="quantity-controls">
                                            <button type="button" class="qty-btn minus-btn" onclick="updateQuantity(this, -1)">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>" class="quantity-input" onchange="this.form.submit()">
                                            <button type="button" class="qty-btn plus-btn" onclick="updateQuantity(this, 1)">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <div class="item-total">
                                    <span class="total-label">Total</span>
                                    <span class="total-amount">৳<?php echo number_format($item_total, 2); ?></span>
                                </div>

                                <!-- Add visible delete button -->
                                <div class="item-remove">
                                    <form action="php/cart_manager.php" method="POST" class="delete-form">
                                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                        <input type="hidden" name="action" value="remove">
                                        <button type="submit" class="delete-btn" title="Remove item">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="cart-summary-section">
                    <div class="summary-card">
                        <div class="summary-header">
                            <h3>Order Summary</h3>
                            <i class="fas fa-receipt"></i>
                        </div>

                        <div class="summary-details">
                            <div class="summary-row">
                                <span>Subtotal</span>
                                <span>৳<?php echo number_format($total, 2); ?></span>
                            </div>
                            <div class="summary-row">
                                <span>Delivery Fee</span>
                                <span class="free-delivery">Free</span>
                            </div>
                            <div class="summary-divider"></div>
                            <div class="summary-row total-row">
                                <span>Total Amount</span>
                                <span class="final-total">৳<?php echo number_format($total, 2); ?></span>
                            </div>
                        </div>

                        <div class="summary-actions">
                            <a href="checkout.php" class="checkout-btn">
                                <i class="fas fa-credit-card"></i>
                                <span>Proceed to Checkout</span>
                            </a>
                            <a href="index.php" class="continue-shopping-btn">
                                <i class="fas fa-arrow-left"></i>
                                <span>Continue Shopping</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-cart-section">
                <div class="empty-cart-content">
                    <div class="empty-cart-icon">
                        <i class="fas fa-shopping-basket"></i>
                    </div>
                    <h2>Your cart is empty</h2>
                    <p>Looks like you haven't added any items to your cart yet. Start shopping to fill it up!</p>
                    <div class="empty-cart-actions">
                        <a href="index.php" class="shop-now-btn">
                            <i class="fas fa-store"></i>
                            <span>Start Shopping</span>
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
    /* Enhanced Cart Styles */
    .cart-section {
        position: relative;
        min-height: 100vh;
        background: linear-gradient(135deg, #f8fff9 0%, #e8f5e8 50%, #d4edda 100%);
        overflow: hidden;
        padding: 40px 0;
    }

    .cart-background {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
    }

    .cart-pattern {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image:
            radial-gradient(circle at 20% 80%, rgba(76, 175, 80, 0.1) 0%, transparent 50%),
            radial-gradient(circle at 80% 20%, rgba(139, 195, 74, 0.1) 0%, transparent 50%),
            radial-gradient(circle at 40% 40%, rgba(76, 175, 80, 0.05) 0%, transparent 50%);
    }

    .cart-container {
        position: relative;
        z-index: 2;
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
    }

    /* Cart Header */
    .cart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 40px;
        background: white;
        padding: 30px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .cart-title-wrapper {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .cart-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #4CAF50, #8BC34A);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        box-shadow: 0 10px 30px rgba(76, 175, 80, 0.3);
    }

    .cart-title {
        font-size: 2.5rem;
        font-weight: 800;
        color: #2c3e50;
        margin: 0;
    }

    .cart-subtitle {
        color: #666;
        font-size: 1.1rem;
        margin: 5px 0 0 0;
    }

    .cart-stats {
        display: flex;
        gap: 20px;
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 8px;
        background: rgba(76, 175, 80, 0.1);
        padding: 10px 20px;
        border-radius: 25px;
        color: #4CAF50;
        font-weight: 600;
    }

    /* Error Message */
    .error-message-wrapper {
        margin-bottom: 30px;
    }

    .error-message {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        color: #856404;
        padding: 15px 20px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
    }

    /* Cart Content */
    .cart-content {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 30px;
    }

    /* Cart Items Section */
    .cart-items-section {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .cart-items-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #f0f0f0;
    }

    .cart-items-header h3 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2c3e50;
        margin: 0;
    }

    .items-count {
        background: linear-gradient(135deg, #4CAF50, #8BC34A);
        color: white;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .cart-items-list {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .cart-item-card {
        display: grid;
        grid-template-columns: auto 1fr auto auto auto;
        gap: 20px;
        align-items: center;
        background: #ffffff;
        /* Changed from #f8fff9 to pure white */
        padding: 20px;
        border-radius: 15px;
        transition: all 0.3s ease;
        position: relative;
        border: 1px solid #e8f5e8;
        /* Added subtle border for definition */
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        /* Enhanced shadow for depth */
    }

    .cart-item-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        /* Stronger hover shadow */
        border-color: #4CAF50;
        /* Green border on hover */
    }

    .item-image-container {
        position: relative;
        width: 100px;
        height: 100px;
        border-radius: 15px;
        overflow: hidden;
    }

    .item-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .item-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .cart-item-card:hover .item-overlay {
        opacity: 0;
    }

    .remove-btn {
        background: white;
        border: none;
        color: #e74c3c;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .remove-btn:hover {
        transform: scale(1.1);
        background: #e74c3c;
        color: white;
    }

    .item-details {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .item-name {
        font-size: 1.2rem;
        font-weight: 600;
        color: #2c3e50;
        margin: 0;
    }

    .item-price {
        font-size: 1.1rem;
        font-weight: 700;
        color: #4CAF50;
    }

    .item-quantity {
        display: flex;
        align-items: center;
    }

    .quantity-controls {
        display: flex;
        align-items: center;
        background: white;
        border-radius: 25px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .qty-btn {
        background: #f8f9fa;
        border: none;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        color: #4CAF50;
    }

    .qty-btn:hover {
        background: #4CAF50;
        color: white;
    }

    .quantity-input {
        width: 60px;
        height: 40px;
        border: none;
        text-align: center;
        font-weight: 600;
        color: #2c3e50;
        background: white;
    }

    .quantity-input:focus {
        outline: none;
    }

    .item-total {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 5px;
        min-width: 100px;
        /* Ensures consistent width for price columns */
        text-align: right;
    }

    .total-label {
        font-size: 0.9rem;
        color: #666;
    }

    .total-amount {
        font-size: 1.3rem;
        font-weight: 700;
        color: #4CAF50;
    }

    /* Cart Summary Section */
    .cart-summary-section {
        position: sticky;
        top: 20px;
    }

    .summary-card {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .summary-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
    }

    .summary-header h3 {
        font-size: 1.3rem;
        font-weight: 700;
        color: #2c3e50;
        margin: 0;
    }

    .summary-header i {
        color: #4CAF50;
        font-size: 1.5rem;
    }

    .summary-details {
        margin-bottom: 30px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        font-size: 1rem;
    }

    .summary-row:last-child {
        margin-bottom: 0;
    }

    .free-delivery {
        color: #4CAF50;
        font-weight: 600;
    }

    .summary-divider {
        height: 1px;
        background: #e0e0e0;
        margin: 20px 0;
    }

    .total-row {
        font-size: 1.2rem;
        font-weight: 700;
        color: #2c3e50;
    }

    .final-total {
        color: #4CAF50;
        font-size: 1.4rem;
    }

    .summary-actions {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .checkout-btn {
        background: linear-gradient(135deg, #4CAF50, #8BC34A);
        color: white;
        padding: 15px 25px;
        border-radius: 15px;
        text-decoration: none;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
    }

    .checkout-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
        color: white;
    }

    .continue-shopping-btn {
        background: transparent;
        color: #4CAF50;
        padding: 15px 25px;
        border: 2px solid #4CAF50;
        border-radius: 15px;
        text-decoration: none;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.3s ease;
    }

    .continue-shopping-btn:hover {
        background: #4CAF50;
        color: white;
    }

    /* Empty Cart Section */
    .empty-cart-section {
        background: white;
        border-radius: 20px;
        padding: 80px 40px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .empty-cart-content {
        max-width: 500px;
        margin: 0 auto;
    }

    .empty-cart-icon {
        width: 120px;
        height: 120px;
        background: linear-gradient(135deg, #4CAF50, #8BC34A);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 30px;
        color: white;
        font-size: 3rem;
        box-shadow: 0 10px 30px rgba(76, 175, 80, 0.3);
    }

    .empty-cart-section h2 {
        font-size: 2rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 15px;
    }

    .empty-cart-section p {
        color: #666;
        font-size: 1.1rem;
        line-height: 1.6;
        margin-bottom: 40px;
    }

    .shop-now-btn {
        background: linear-gradient(135deg, #4CAF50, #8BC34A);
        color: white;
        padding: 18px 40px;
        border-radius: 30px;
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
    }

    .shop-now-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
        color: white;
    }

    /* New delete button styles */
    .cart-item-card {
        display: grid;
        grid-template-columns: auto 1fr auto auto auto;
        /* Added one more column for delete button */
        gap: 20px;
        align-items: center;
        background: #f8fff9;
        padding: 20px;
        border-radius: 15px;
        transition: all 0.3s ease;
        position: relative;
    }

    .item-remove {
        align-self: flex-start;
        margin-top: 5px;
    }

    .delete-btn {
        background: #fff;
        border: 1px solid #e0e0e0;
        color: #e74c3c;
        font-size: 1rem;
        cursor: pointer;
        padding: 8px;
        border-radius: 50%;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .delete-btn:hover {
        background: #e74c3c;
        color: white;
        transform: scale(1.1);
        box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
    }

    /* Update responsive styles to handle the new column */
    @media (max-width: 768px) {
        .cart-item-card {
            grid-template-columns: 1fr;
            padding: 15px;
            gap: 15px;
        }

        .item-remove {
            position: absolute;
            top: 10px;
            right: 10px;
            margin-top: 0;
        }
    }

    @media (max-width: 1200px) {
        .cart-content {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .cart-summary-section {
            position: static;
        }
    }

    @media (max-width: 768px) {
        .cart-header {
            flex-direction: column;
            gap: 20px;
            text-align: center;
        }

        .cart-item-card {
            grid-template-columns: 1fr;
            gap: 15px;
            text-align: center;
        }

        .item-image-container {
            width: 80px;
            height: 80px;
            margin: 0 auto;
        }

        .quantity-controls {
            justify-content: center;
        }

        .item-total {
            align-items: center;
        }

        .empty-cart-section {
            padding: 60px 20px;
        }

        .empty-cart-icon {
            width: 100px;
            height: 100px;
            font-size: 2.5rem;
        }
    }

    @media (max-width: 480px) {
        .cart-container {
            padding: 0 15px;
        }

        .cart-title {
            font-size: 2rem;
        }

        .cart-items-section,
        .summary-card {
            padding: 20px;
        }
    }

    /* Notification container and animations */
    .notification-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .cart-notification {
        background: white;
        border-radius: 10px;
        padding: 15px 20px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        transform: translateX(120%);
        transition: transform 0.3s ease;
        max-width: 300px;
    }

    .cart-notification.show {
        transform: translateX(0);
    }

    .cart-notification.success {
        border-left: 4px solid #4CAF50;
    }

    .cart-notification.error {
        border-left: 4px solid #e74c3c;
    }

    .notification-content {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .notification-content i {
        font-size: 1.2rem;
    }

    .cart-notification.success i {
        color: #4CAF50;
    }

    .cart-notification.error i {
        color: #e74c3c;
    }

    /* Item removal transition */
    .cart-item-card {
        transition: opacity 0.3s ease, transform 0.3s ease, max-height 0.3s ease, margin 0.3s ease, padding 0.3s ease;
        max-height: 500px;
        overflow: hidden;
    }

    /* Badge animation */
    @keyframes pulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.2);
        }

        100% {
            transform: scale(1);
        }
    }

    .badge.pulse {
        animation: pulse 0.5s ease;
        background-color: #e74c3c;
    }
</style>

<script>
    function updateQuantity(button, change) {
        const input = button.parentElement.querySelector('input');
        const currentValue = parseInt(input.value);
        const minValue = parseInt(input.min);
        const maxValue = parseInt(input.max);
        const newValue = currentValue + change;

        if (newValue >= minValue && newValue <= maxValue) {
            input.value = newValue;

            // Trigger the form submission but don't reload
            const form = button.closest('form');

            // Get form data
            const formData = new FormData(form);
            formData.append('ajax', '1');

            // Visual feedback
            const productCard = form.closest('.cart-item-card');
            productCard.style.opacity = '0.7';

            fetch('php/cart_manager.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Response:', data);

                    if (data.success) {
                        showNotification(data.message, 'success');

                        // Update cart count
                        updateCartCount(data.cart_count);

                        // Update item total price
                        const itemTotal = productCard.querySelector('.total-amount');
                        if (itemTotal && data.item_total) {
                            itemTotal.textContent = '৳' + data.item_total.toFixed(2);
                        }

                        // Update cart summary
                        updateCartSummary(data.cart_total);
                    } else {
                        showNotification(data.message, 'error');
                        // Reset to previous value
                        input.value = currentValue;
                    }

                    // Reset product card appearance
                    productCard.style.opacity = '1';
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred. Please try again.', 'error');
                    input.value = currentValue;
                    productCard.style.opacity = '1';
                });
        }
    }

    // Function to handle cart operations without page reload
    document.addEventListener('DOMContentLoaded', function() {
        // Make sure cart badge is properly displayed with the current cart count
        const cartCount = <?php echo $cart_count; ?>;
        updateCartCount(cartCount);

        // Find all delete and remove forms
        const deleteForms = document.querySelectorAll('.delete-form, .remove-form');

        deleteForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault(); // Prevent normal form submission

                const productId = this.querySelector('input[name="product_id"]').value;
                const formData = new FormData(this);
                formData.append('ajax', '1'); // Add AJAX flag

                // Visual feedback - add fading out effect to the product card
                const productCard = this.closest('.cart-item-card');
                productCard.style.opacity = '0.5';
                productCard.style.transform = 'scale(0.98)';

                fetch('php/cart_manager.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Response:', data);

                        if (data.success) {
                            // Show success notification
                            showNotification(data.message, 'success');

                            // Update cart count in header
                            updateCartCount(data.cart_count);

                            // Remove product card with animation
                            productCard.style.opacity = '0';
                            productCard.style.maxHeight = '0';
                            productCard.style.margin = '0';
                            productCard.style.padding = '0';

                            setTimeout(() => {
                                productCard.remove();

                                // Update items count in the cart header
                                const itemsCountElements = document.querySelectorAll('.items-count, .stat-item span');
                                itemsCountElements.forEach(el => {
                                    el.textContent = data.cart_count + ' Items';
                                });

                                // Update cart summary
                                updateCartSummary(data.cart_total);

                                // If cart is empty, reload to show empty cart message
                                if (data.cart_count === 0) {
                                    location.reload();
                                }
                            }, 300);
                        } else {
                            // Show error notification
                            showNotification(data.message, 'error');

                            // Reset product card appearance
                            productCard.style.opacity = '1';
                            productCard.style.transform = 'none';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('An error occurred. Please try again.', 'error');

                        // Reset product card appearance
                        productCard.style.opacity = '1';
                        productCard.style.transform = 'none';
                    });
            });
        });

        // Also make quantity update forms use AJAX
        const updateForms = document.querySelectorAll('.update-form');

        updateForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const productId = this.querySelector('input[name="product_id"]').value;
                const quantity = this.querySelector('input[name="quantity"]').value;
                const formData = new FormData(this);
                formData.append('ajax', '1');

                const productCard = this.closest('.cart-item-card');
                productCard.style.opacity = '0.7';

                fetch('php/cart_manager.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Response:', data);

                        if (data.success) {
                            showNotification(data.message, 'success');

                            // Update cart count
                            updateCartCount(data.cart_count);

                            // Update item total price
                            const itemTotal = productCard.querySelector('.total-amount');
                            if (itemTotal && data.item_total) {
                                itemTotal.textContent = '৳' + data.item_total.toFixed(2);
                            }

                            // Update cart summary
                            updateCartSummary(data.cart_total);
                        } else {
                            showNotification(data.message, 'error');
                        }

                        // Reset product card appearance
                        productCard.style.opacity = '1';
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('An error occurred. Please try again.', 'error');
                        productCard.style.opacity = '1';
                    });
            });
        });
    });

    // Function to update cart summary totals
    function updateCartSummary(total) {
        if (typeof total === 'undefined') return;

        const subtotalEl = document.querySelector('.summary-row:first-child span:last-child');
        const totalEl = document.querySelector('.final-total');

        if (subtotalEl) subtotalEl.textContent = '৳' + total.toFixed(2);
        if (totalEl) totalEl.textContent = '৳' + total.toFixed(2);
    }

    // Notification function
    function showNotification(message, type) {
        // Check if notification container exists, if not create it
        let notificationContainer = document.querySelector('.notification-container');
        if (!notificationContainer) {
            notificationContainer = document.createElement('div');
            notificationContainer.className = 'notification-container';
            document.body.appendChild(notificationContainer);
        }

        // Create notification element
        const notification = document.createElement('div');
        notification.className = `cart-notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                <span>${message}</span>
            </div>
        `;

        // Add to container
        notificationContainer.appendChild(notification);

        // Show notification
        setTimeout(() => notification.classList.add('show'), 10);

        // Hide notification after 3 seconds
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    // Function to update cart count in header
    function updateCartCount(count) {
        // Target all possible cart counter elements
        const cartIcons = document.querySelectorAll('[data-count]');
        const cartBadges = document.querySelectorAll('.badge');
        const cartCountTexts = document.querySelectorAll('.cart-count');
        const itemsCountElements = document.querySelectorAll('.items-count, .stat-item span');
        const cartBadge = document.querySelector('.cart-badge');

        console.log('Updating cart count to:', count);

        // Update data-count attributes
        cartIcons.forEach(icon => {
            icon.setAttribute('data-count', count);
        });

        // Update any badge elements
        cartBadges.forEach(badge => {
            badge.textContent = count;
        });

        // Update text content of any cart count elements
        cartCountTexts.forEach(text => {
            if (text) text.textContent = count;
        });

        // Update items count text
        itemsCountElements.forEach(el => {
            el.textContent = count + ' Items';
        });

        // Update cart badge in header
        if (cartBadge) {
            cartBadge.textContent = count;

            // Add animation effect for badge update
            cartBadge.classList.add('pulse');
            setTimeout(() => {
                cartBadge.classList.remove('pulse');
            }, 500);

            // Hide badge if count is zero
            if (count == 0) {
                cartBadge.style.display = 'none';
            } else {
                cartBadge.style.display = 'flex';
            }
        } else {
            // If badge doesn't exist but should (count > 0), create it
            const cartLink = document.querySelector('.cart-link');
            if (cartLink && count > 0) {
                const cartWrapper = cartLink.querySelector('.cart-wrapper');
                if (cartWrapper) {
                    // Check if badge already exists
                    let badge = cartWrapper.querySelector('.cart-badge');
                    if (!badge) {
                        badge = document.createElement('span');
                        badge.className = 'cart-badge pulse';
                        badge.textContent = count;
                        cartWrapper.appendChild(badge);

                        setTimeout(() => {
                            badge.classList.remove('pulse');
                        }, 500);
                    }
                }
            }
        }

        // Update any parent elements that need to know about cart changes
        document.dispatchEvent(new CustomEvent('cart:updated', {
            detail: {
                count: count
            }
        }));
    }
</script>

<?php include 'includes/footer.php'; ?>
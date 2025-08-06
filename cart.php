<?php
// FILE: cart.php
include 'includes/header.php';
$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$total = 0;
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
                                        <div class="item-actions">
                                            <form action="php/cart_manager.php" method="POST" class="remove-form">
                                                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                                <input type="hidden" name="action" value="remove">
                                                <button type="submit" class="remove-btn">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
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
                                            <i class="fas fa-times"></i>
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
        /* Added one more column for delete button */
        gap: 20px;
        align-items: center;
        background: #f8fff9;
        padding: 20px;
        border-radius: 15px;
        transition: all 0.3s ease;
        position: relative;
    }

    .cart-item-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
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
        background: rgba(76, 175, 80, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .cart-item-card:hover .item-overlay {
        opacity: 1;
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
        background: none;
        border: none;
        color: #e74c3c;
        font-size: 1.2rem;
        cursor: pointer;
        padding: 8px;
        border-radius: 50%;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
    }

    .delete-btn:hover {
        background: #e74c3c;
        color: white;
        transform: rotate(90deg);
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
</style>

<script>
    function updateQuantity(button, change) {
        const input = button.parentElement.querySelector('input');
        const newValue = parseInt(input.value) + change;

        if (newValue >= parseInt(input.min) && newValue <= parseInt(input.max)) {
            input.value = newValue;
            input.form.submit();
        }
    }
</script>

<?php include 'includes/footer.php'; ?>
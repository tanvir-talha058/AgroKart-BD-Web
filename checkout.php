<?php
// FILE: checkout.php

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and has items in cart
if (!isset($_SESSION['loggedin']) || empty($_SESSION['cart'])) {
    header('Location: index.php');
    exit;
}

// Include header after checking for redirect
include 'includes/header.php';
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="checkout-page">
    <!-- Progress Indicator -->
    <div class="checkout-progress">
        <div class="progress-container">
            <div class="progress-step active">
                <div class="step-number">1</div>
                <span>Cart</span>
            </div>
            <div class="progress-line active"></div>
            <div class="progress-step active">
                <div class="step-number">2</div>
                <span>Checkout</span>
            </div>
            <div class="progress-line"></div>
            <div class="progress-step">
                <div class="step-number">3</div>
                <span>Payment</span>
            </div>
        </div>
    </div>

    <div class="checkout-container">
        <div class="checkout-header">
            <h1><i class="fas fa-shopping-bag"></i> Secure Checkout</h1>
            <p>Complete your order safely and securely</p>
        </div>

        <div class="checkout-content">
            <!-- Order Summary Section -->
            <div class="order-summary-section">
                <div class="order-summary">
                    <div class="summary-header">
                        <h3><i class="fas fa-receipt"></i> Order Summary</h3>
                        <span class="item-count"><?php echo count($_SESSION['cart']); ?> items</span>
                    </div>

                    <div class="summary-items">
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <div class="summary-item">
                                <div class="item-info">
                                    <div class="item-image">
                                        <img src="<?php echo htmlspecialchars($item['image'] ?? 'images/default-product.png'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    </div>
                                    <div class="item-details">
                                        <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                        <span class="item-quantity">Qty: <?php echo $item['quantity']; ?></span>
                                    </div>
                                </div>
                                <div class="item-price">
                                    ৳<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="summary-calculations">
                        <div class="calc-row">
                            <span>Subtotal</span>
                            <span>৳<?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="calc-row">
                            <span>Delivery Fee</span>
                            <span class="free">Free</span>
                        </div>
                        <div class="calc-row discount">
                            <span><i class="fas fa-tag"></i> Discount</span>
                            <span>৳0.00</span>
                        </div>
                        <hr>
                        <div class="summary-total">
                            <strong>Total Amount</strong>
                            <strong class="total-price">৳<?php echo number_format($total, 2); ?></strong>
                        </div>
                    </div>

                    <div class="security-badge">
                        <i class="fas fa-shield-alt"></i>
                        <span>Secure SSL Encrypted Payment</span>
                    </div>
                </div>
            </div>

            <!-- Checkout Form Section -->
            <div class="checkout-form-section">
                <div class="checkout-form">
                    <form action="php/order_process.php" method="POST" id="checkoutForm">
                        <!-- Shipping Information -->
                        <div class="form-section">
                            <div class="section-header">
                                <h3><i class="fas fa-truck"></i> Delivery Information</h3>
                            </div>

                            <div class="form-group">
                                <label for="location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    Delivery Address
                                </label>
                                <div class="input-wrapper">
                                    <textarea name="location" id="location" rows="3" required placeholder="Enter your complete delivery address..."><?php echo htmlspecialchars($_SESSION['user_location']); ?></textarea>
                                    <div class="input-icon">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="delivery-options">
                                <div class="delivery-option selected">
                                    <div class="option-icon">
                                        <i class="fas fa-shipping-fast"></i>
                                    </div>
                                    <div class="option-details">
                                        <h4>Standard Delivery</h4>
                                        <p>2-3 business days • Free</p>
                                    </div>
                                    <div class="option-price">৳0</div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="form-section">
                            <div class="section-header">
                                <h3><i class="fas fa-credit-card"></i> Payment Method</h3>
                                <p>Choose your preferred payment option</p>
                            </div>

                            <div class="payment-methods">
                                <div class="payment-option">
                                    <input type="radio" name="payment_method" value="bKash" id="bkash" required>
                                    <label for="bkash" class="payment-label">
                                        <div class="payment-icon bkash">
                                            <i class="fas fa-mobile-alt"></i>
                                        </div>
                                        <div class="payment-info">
                                            <h4>bKash</h4>
                                            <p>Pay with bKash mobile wallet</p>
                                        </div>
                                        <div class="payment-check">
                                            <i class="fas fa-check"></i>
                                        </div>
                                    </label>
                                </div>

                                <div class="payment-option">
                                    <input type="radio" name="payment_method" value="Nagad" id="nagad">
                                    <label for="nagad" class="payment-label">
                                        <div class="payment-icon nagad">
                                            <i class="fas fa-mobile-alt"></i>
                                        </div>
                                        <div class="payment-info">
                                            <h4>Nagad</h4>
                                            <p>Pay with Nagad mobile wallet</p>
                                        </div>
                                        <div class="payment-check">
                                            <i class="fas fa-check"></i>
                                        </div>
                                    </label>
                                </div>

                                <div class="payment-option">
                                    <input type="radio" name="payment_method" value="Card" id="card">
                                    <label for="card" class="payment-label">
                                        <div class="payment-icon card">
                                            <i class="fas fa-credit-card"></i>
                                        </div>
                                        <div class="payment-info">
                                            <h4>Credit/Debit Card</h4>
                                            <p>Visa, MasterCard, American Express</p>
                                        </div>
                                        <div class="payment-check">
                                            <i class="fas fa-check"></i>
                                        </div>
                                    </label>
                                </div>

                                <div class="payment-option">
                                    <input type="radio" name="payment_method" value="COD" id="cod">
                                    <label for="cod" class="payment-label">
                                        <div class="payment-icon cod">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </div>
                                        <div class="payment-info">
                                            <h4>Cash on Delivery</h4>
                                            <p>Pay when you receive your order</p>
                                        </div>
                                        <div class="payment-check">
                                            <i class="fas fa-check"></i>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Order Notes -->
                        <div class="form-section">
                            <div class="section-header">
                                <h3><i class="fas fa-sticky-note"></i> Order Notes (Optional)</h3>
                            </div>

                            <div class="form-group">
                                <textarea name="order_notes" id="order_notes" rows="3" placeholder="Any special instructions for your order..."></textarea>
                            </div>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="form-section">
                            <div class="terms-checkbox">
                                <input type="checkbox" id="terms" required>
                                <label for="terms">
                                    I agree to the <a href="#" target="_blank">Terms & Conditions</a> and <a href="#" target="_blank">Privacy Policy</a>
                                </label>
                            </div>
                        </div>

                        <!-- Place Order Button -->
                        <div class="form-actions">
                            <button type="submit" class="place-order-btn">
                                <i class="fas fa-lock"></i>
                                <span>Place Secure Order</span>
                                <div class="btn-price">৳<?php echo number_format($total, 2); ?></div>
                            </button>

                            <div class="security-info">
                                <i class="fas fa-shield-alt"></i>
                                <span>Your payment information is secure and encrypted</span>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Checkout Page Styles */
    .checkout-page {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
        padding: 20px 0;
    }

    .checkout-progress {
        max-width: 1200px;
        margin: 0 auto 30px;
        padding: 0 20px;
    }

    .progress-container {
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.9);
        padding: 20px;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .progress-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        color: #999;
        transition: color 0.3s ease;
    }

    .progress-step.active {
        color: #4CAF50;
    }

    .step-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e0e0e0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .progress-step.active .step-number {
        background: #4CAF50;
        color: white;
    }

    .progress-line {
        width: 100px;
        height: 2px;
        background: #e0e0e0;
        margin: 0 20px;
        transition: background 0.3s ease;
    }

    .progress-line.active {
        background: #4CAF50;
    }

    .checkout-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .checkout-header {
        text-align: center;
        margin-bottom: 40px;
        background: rgba(255, 255, 255, 0.95);
        padding: 30px;
        border-radius: 20px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .checkout-header h1 {
        color: #2C3E50;
        margin: 0 0 10px 0;
        font-size: 2.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
    }

    .checkout-header p {
        color: #666;
        margin: 0;
        font-size: 1.1rem;
    }

    .checkout-content {
        display: grid;
        grid-template-columns: 1fr 1.5fr;
        gap: 30px;
        align-items: start;
    }

    /* Order Summary Styles */
    .order-summary {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 20px;
        padding: 25px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        position: sticky;
        top: 20px;
    }

    .summary-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
    }

    .summary-header h3 {
        color: #2C3E50;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.4rem;
    }

    .item-count {
        background: #4CAF50;
        color: white;
        padding: 4px 12px;
        border-radius: 15px;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .summary-items {
        margin-bottom: 20px;
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 0;
        border-bottom: 1px solid #f5f5f5;
    }

    .summary-item:last-child {
        border-bottom: none;
    }

    .item-info {
        display: flex;
        align-items: center;
        gap: 12px;
        flex: 1;
    }

    .item-image {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        overflow: hidden;
        background: #f5f5f5;
    }

    .item-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .item-details h4 {
        margin: 0 0 5px 0;
        color: #2C3E50;
        font-size: 1rem;
        font-weight: 600;
    }

    .item-quantity {
        color: #666;
        font-size: 0.9rem;
    }

    .item-price {
        font-weight: 600;
        color: #4CAF50;
        font-size: 1.1rem;
    }

    .summary-calculations {
        border-top: 2px solid #f0f0f0;
        padding-top: 15px;
    }

    .calc-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        color: #666;
    }

    .calc-row.discount {
        color: #4CAF50;
    }

    .free {
        color: #4CAF50;
        font-weight: 600;
    }

    .summary-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 15px;
        font-size: 1.2rem;
    }

    .total-price {
        color: #4CAF50;
        font-size: 1.4rem;
    }

    .security-badge {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: 20px;
        padding: 12px;
        background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
        border-radius: 10px;
        color: #2e7d32;
        font-size: 0.9rem;
        font-weight: 600;
    }

    /* Checkout Form Styles */
    .checkout-form {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .form-section {
        margin-bottom: 30px;
    }

    .section-header {
        margin-bottom: 20px;
    }

    .section-header h3 {
        color: #2C3E50;
        margin: 0 0 5px 0;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.3rem;
    }

    .section-header p {
        color: #666;
        margin: 0;
        font-size: 0.95rem;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        font-weight: 600;
        color: #2C3E50;
    }

    .input-wrapper {
        position: relative;
    }

    .input-wrapper textarea {
        width: 100%;
        padding: 15px 20px;
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        font-family: inherit;
        font-size: 1rem;
        resize: vertical;
        transition: all 0.3s ease;
        background: #fafafa;
    }

    .input-wrapper textarea:focus {
        outline: none;
        border-color: #4CAF50;
        background: white;
        box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
    }

    .input-icon {
        position: absolute;
        right: 15px;
        top: 15px;
        color: #999;
    }

    .delivery-options {
        margin-top: 15px;
    }

    .delivery-option {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px;
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #fafafa;
    }

    .delivery-option.selected {
        border-color: #4CAF50;
        background: rgba(76, 175, 80, 0.05);
    }

    .option-icon {
        width: 50px;
        height: 50px;
        background: #4CAF50;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
    }

    .option-details {
        flex: 1;
    }

    .option-details h4 {
        margin: 0 0 5px 0;
        color: #2C3E50;
        font-size: 1.1rem;
    }

    .option-details p {
        margin: 0;
        color: #666;
        font-size: 0.9rem;
    }

    .option-price {
        font-weight: 600;
        color: #4CAF50;
        font-size: 1.1rem;
    }

    /* Payment Methods */
    .payment-methods {
        display: grid;
        gap: 15px;
    }

    .payment-option {
        position: relative;
    }

    .payment-option input[type="radio"] {
        display: none;
    }

    .payment-label {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 20px;
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #fafafa;
    }

    .payment-option input[type="radio"]:checked+.payment-label {
        border-color: #4CAF50;
        background: rgba(76, 175, 80, 0.05);
    }

    .payment-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
    }

    .payment-icon.bkash {
        background: linear-gradient(135deg, #e2136e, #c91162);
    }

    .payment-icon.nagad {
        background: linear-gradient(135deg, #f47920, #e6691a);
    }

    .payment-icon.card {
        background: linear-gradient(135deg, #1e3a8a, #1e40af);
    }

    .payment-icon.cod {
        background: linear-gradient(135deg, #059669, #047857);
    }

    .payment-info {
        flex: 1;
    }

    .payment-info h4 {
        margin: 0 0 5px 0;
        color: #2C3E50;
        font-size: 1.1rem;
        font-weight: 600;
    }

    .payment-info p {
        margin: 0;
        color: #666;
        font-size: 0.9rem;
    }

    .payment-check {
        width: 24px;
        height: 24px;
        border: 2px solid #e0e0e0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .payment-option input[type="radio"]:checked+.payment-label .payment-check {
        background: #4CAF50;
        border-color: #4CAF50;
        color: white;
    }

    .terms-checkbox {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 10px;
        border: 1px solid #e9ecef;
    }

    .terms-checkbox input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: #4CAF50;
    }

    .terms-checkbox label {
        color: #666;
        font-size: 0.95rem;
        line-height: 1.4;
    }

    .terms-checkbox a {
        color: #4CAF50;
        text-decoration: none;
        font-weight: 600;
    }

    .terms-checkbox a:hover {
        text-decoration: underline;
    }

    .form-actions {
        text-align: center;
        margin-top: 30px;
    }

    .place-order-btn {
        background: linear-gradient(135deg, #4CAF50, #45a049);
        color: white;
        border: none;
        padding: 18px 40px;
        border-radius: 25px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        width: 100%;
        max-width: 400px;
        margin: 0 auto 15px;
        transition: all 0.3s ease;
        box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
    }

    .place-order-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(76, 175, 80, 0.6);
    }

    .place-order-btn:active {
        transform: translateY(0);
    }

    .btn-price {
        background: rgba(255, 255, 255, 0.2);
        padding: 4px 12px;
        border-radius: 15px;
        font-weight: 700;
    }

    .security-info {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        color: #666;
        font-size: 0.9rem;
    }

    /* Order Notes */
    #order_notes {
        width: 100%;
        padding: 15px 20px;
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        font-family: inherit;
        font-size: 1rem;
        resize: vertical;
        transition: all 0.3s ease;
        background: #fafafa;
    }

    #order_notes:focus {
        outline: none;
        border-color: #4CAF50;
        background: white;
        box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .checkout-content {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .order-summary {
            position: static;
            order: 2;
        }

        .checkout-form-section {
            order: 1;
        }

        .progress-container {
            padding: 15px;
        }

        .progress-line {
            width: 60px;
            margin: 0 10px;
        }

        .checkout-header h1 {
            font-size: 2rem;
            flex-direction: column;
            gap: 10px;
        }

        .payment-label {
            padding: 15px;
        }

        .place-order-btn {
            padding: 15px 30px;
        }
    }

    @media (max-width: 480px) {
        .checkout-page {
            padding: 10px 0;
        }

        .checkout-container {
            padding: 0 15px;
        }

        .checkout-header {
            padding: 20px;
        }

        .order-summary,
        .checkout-form {
            padding: 20px;
        }

        .summary-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        .item-price {
            align-self: flex-end;
        }
    }
</style>

<script>
    // Form validation and enhancement
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('checkoutForm');
        const paymentOptions = document.querySelectorAll('input[name="payment_method"]');
        const placeOrderBtn = document.querySelector('.place-order-btn');

        // Payment method selection enhancement
        paymentOptions.forEach(option => {
            option.addEventListener('change', function() {
                // Remove active class from all labels
                document.querySelectorAll('.payment-label').forEach(label => {
                    label.classList.remove('active');
                });

                // Add active class to selected label
                this.nextElementSibling.classList.add('active');
            });
        });

        // Form submission with loading state
        form.addEventListener('submit', function(e) {
            const termsCheckbox = document.getElementById('terms');

            if (!termsCheckbox.checked) {
                e.preventDefault();
                alert('Please accept the Terms & Conditions to proceed.');
                return;
            }

            // Show loading state
            placeOrderBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing Order...';
            placeOrderBtn.disabled = true;

            // Add a small delay to show the loading state
            setTimeout(() => {
                // The form will submit naturally after this
            }, 500);
        });

        // Auto-resize textarea
        const textareas = document.querySelectorAll('textarea');
        textareas.forEach(textarea => {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = this.scrollHeight + 'px';
            });
        });

        // Smooth scroll to form sections
        const sectionHeaders = document.querySelectorAll('.section-header h3');
        sectionHeaders.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', function() {
                this.parentElement.parentElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            });
        });
    });

    // Add floating animation to security badges
    function addFloatingAnimation() {
        const securityBadge = document.querySelector('.security-badge');
        if (securityBadge) {
            securityBadge.style.animation = 'float 3s ease-in-out infinite';
        }
    }

    // Add CSS animation
    const style = document.createElement('style');
    style.textContent = `
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-5px); }
    }
    
    .payment-label.active {
        transform: scale(1.02);
        box-shadow: 0 4px 15px rgba(76, 175, 80, 0.2);
    }
`;
    document.head.appendChild(style);

    // Initialize animations
    addFloatingAnimation();
</script>

<?php include 'includes/footer.php'; ?>
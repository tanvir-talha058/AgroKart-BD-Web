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



<link rel="stylesheet" href="css/checkout-style.css">

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
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

// Total will be calculated during item rendering below to avoid double counting

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
                                    <div class="item-price">
                                        <?php if (isset($item['is_deal']) && $item['is_deal'] && isset($item['original_price'])): ?>
                                            <span class="deal-price">৳<?php echo htmlspecialchars($item['price']); ?></span>
                                            <span class="original-price">৳<?php echo htmlspecialchars($item['original_price']); ?></span>
                                            <span class="deal-badge">HOT DEAL!</span>
                                        <?php else: ?>
                                            ৳<?php echo htmlspecialchars($item['price']); ?>
                                        <?php endif; ?>
                                        <span class="per-unit">per <?php echo htmlspecialchars($item['unit'] ?? 'kg'); ?></span>
                                    </div>
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

<link rel="stylesheet" href="css/cart-style.css">

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
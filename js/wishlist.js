// FILE: js/wishlist.js

document.addEventListener('DOMContentLoaded', function() {
    loadRecommendations();
    initializeWishlistButtons();
});

// Add to cart from wishlist
function addToCart(productId) {
    const button = event.target;
    const originalText = button.innerHTML;
    
    // Show loading state
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    button.disabled = true;
    
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', 1);
    formData.append('action', 'add');
    formData.append('ajax', '1');
    
    fetch('php/cart_manager.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Product added to cart successfully!', 'success');
            // Update cart counter if exists
            const cartIcon = document.querySelector('.cart-icon');
            if (cartIcon && data.cart_count) {
                cartIcon.setAttribute('data-count', data.cart_count);
            }
        } else {
            showNotification(data.message || 'Failed to add to cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    })
    .finally(() => {
        // Restore button state
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

// Remove from wishlist
function removeFromWishlist(productId) {
    if (!confirm('Are you sure you want to remove this item from your wishlist?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('action', 'remove');
    
    fetch('php/wishlist_manager.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            
            // Remove the card with animation
            const card = document.querySelector(`[data-product-id="${productId}"]`);
            if (card) {
                card.style.transform = 'scale(0.8)';
                card.style.opacity = '0';
                setTimeout(() => {
                    card.remove();
                    
                    // Update stats
                    updateWishlistStats(data.wishlist_count);
                    
                    // Show empty state if no items left
                    if (data.wishlist_count === 0) {
                        showEmptyWishlist();
                    }
                }, 300);
            }
            
            // Update wishlist count in header
            updateWishlistCount(data.wishlist_count);
        } else {
            showNotification(data.message || 'Failed to remove from wishlist', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    });
}

// Clear all wishlist items
function clearAllWishlist() {
    if (!confirm('Are you sure you want to clear your entire wishlist? This action cannot be undone.')) {
        return;
    }
    
    const wishlistCards = document.querySelectorAll('.wishlist-card');
    const promises = Array.from(wishlistCards).map(card => {
        const productId = card.getAttribute('data-product-id');
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('action', 'remove');
        
        return fetch('php/wishlist_manager.php', {
            method: 'POST',
            body: formData
        });
    });
    
    Promise.all(promises)
        .then(() => {
            showNotification('Wishlist cleared successfully!', 'success');
            setTimeout(() => {
                location.reload();
            }, 1000);
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while clearing wishlist.', 'error');
        });
}

// Add all wishlist items to cart
function addAllToCart() {
    const wishlistCards = document.querySelectorAll('.wishlist-card');
    const availableProducts = Array.from(wishlistCards).filter(card => {
        return !card.querySelector('.out-of-stock');
    });
    
    if (availableProducts.length === 0) {
        showNotification('No available products to add to cart.', 'error');
        return;
    }
    
    if (!confirm(`Add ${availableProducts.length} available items to cart?`)) {
        return;
    }
    
    let addedCount = 0;
    const totalCount = availableProducts.length;
    
    const addPromises = availableProducts.map(card => {
        const productId = card.getAttribute('data-product-id');
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('quantity', 1);
        formData.append('action', 'add');
        formData.append('ajax', '1');
        
        return fetch('php/cart_manager.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                addedCount++;
            }
        });
    });
    
    Promise.all(addPromises)
        .then(() => {
            if (addedCount === totalCount) {
                showNotification(`All ${addedCount} items added to cart successfully!`, 'success');
            } else {
                showNotification(`${addedCount} out of ${totalCount} items added to cart.`, 'success');
            }
            
            // Update cart counter
            setTimeout(() => {
                location.reload();
            }, 1500);
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Some items could not be added to cart.', 'error');
        });
}

// Request stock notification
function requestNotification(productId) {
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('notification_type', 'stock_alert');
    formData.append('action', 'add');
    
    fetch('php/notification_manager.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('You will be notified when this product is back in stock!', 'success');
            
            // Update button
            const button = event.target;
            button.innerHTML = '<i class="fas fa-check"></i> Notification Set';
            button.disabled = true;
            button.style.background = '#95a5a6';
        } else {
            showNotification(data.message || 'Failed to set notification', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    });
}

// Load product recommendations
function loadRecommendations() {
    fetch('php/recommendation_engine.php?type=wishlist')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.recommendations) {
                displayRecommendations(data.recommendations);
            }
        })
        .catch(error => {
            console.error('Error loading recommendations:', error);
        });
}

// Display recommendations
function displayRecommendations(recommendations) {
    const grid = document.getElementById('recommendations-grid');
    if (!grid || recommendations.length === 0) return;
    
    grid.innerHTML = recommendations.map(product => `
        <div class="recommendation-card">
            <div class="rec-image-container">
                <img src="${product.image_path}" alt="${product.name}" class="rec-image">
                <div class="rec-overlay">
                    <button class="quick-add-btn" onclick="addToCart(${product.id})">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="rec-info">
                <h4 class="rec-name">${product.name}</h4>
                <div class="rec-price">৳${parseFloat(product.price).toFixed(2)}</div>
                <button class="rec-wishlist-btn" onclick="toggleWishlistFromRec(${product.id}, this)">
                    <i class="far fa-heart"></i>
                </button>
            </div>
        </div>
    `).join('');
}

// Toggle wishlist from recommendations
function toggleWishlistFromRec(productId, button) {
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('action', 'toggle');
    
    fetch('php/wishlist_manager.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const icon = button.querySelector('i');
            if (data.is_in_wishlist) {
                icon.className = 'fas fa-heart';
                button.style.color = '#e91e63';
                showNotification('Added to wishlist!', 'success');
            } else {
                icon.className = 'far fa-heart';
                button.style.color = '#7f8c8d';
                showNotification('Removed from wishlist!', 'success');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred.', 'error');
    });
}

// Update wishlist stats
function updateWishlistStats(count) {
    const statItems = document.querySelectorAll('.stat-item span');
    statItems.forEach(item => {
        item.textContent = `${count} Items`;
    });
}

// Update wishlist count in header
function updateWishlistCount(count) {
    const wishlistIcon = document.querySelector('.wishlist-icon');
    if (wishlistIcon) {
        wishlistIcon.setAttribute('data-count', count);
    }
}

// Show empty wishlist state
function showEmptyWishlist() {
    const container = document.querySelector('.wishlist-container');
    container.innerHTML = `
        <div class="wishlist-header">
            <div class="header-content">
                <div class="wishlist-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="wishlist-title-content">
                    <h1 class="wishlist-title">My Wishlist</h1>
                    <p class="wishlist-subtitle">Your favorite products saved for later</p>
                </div>
            </div>
            <div class="wishlist-stats">
                <div class="stat-item">
                    <i class="fas fa-heart"></i>
                    <span>0 Items</span>
                </div>
            </div>
        </div>
        
        <div class="empty-wishlist">
            <div class="empty-icon">
                <i class="fas fa-heart-broken"></i>
            </div>
            <h2>Your wishlist is empty</h2>
            <p>Start browsing and add products you love to your wishlist!</p>
            <a href="index.php" class="shop-now-btn">
                <i class="fas fa-shopping-bag"></i>
                Start Shopping
            </a>
        </div>
    `;
}

// Initialize wishlist buttons on product pages
function initializeWishlistButtons() {
    const wishlistButtons = document.querySelectorAll('.wishlist-toggle-btn');
    wishlistButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            toggleWishlist(productId, this);
        });
    });
}

// Toggle wishlist (for use on product pages)
function toggleWishlist(productId, button) {
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('action', 'toggle');
    
    fetch('php/wishlist_manager.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const icon = button.querySelector('i');
            if (data.is_in_wishlist) {
                icon.className = 'fas fa-heart';
                button.classList.add('active');
                showNotification('Added to wishlist!', 'success');
            } else {
                icon.className = 'far fa-heart';
                button.classList.remove('active');
                showNotification('Removed from wishlist!', 'success');
            }
            
            // Update wishlist count
            updateWishlistCount(data.wishlist_count);
        } else {
            showNotification(data.message || 'Please log in to manage wishlist', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    });
}

// Show notification
function showNotification(message, type) {
    const container = document.getElementById('notification-container');
    if (!container) return;
    
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    notification.innerHTML = `
        <i class="fas ${icon}"></i>
        <span>${message}</span>
    `;
    
    container.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 5000);
}

<?php
// FILE: php/create_wishlist_table.php
include __DIR__ . '/../includes/db_connect.php';

// Create wishlist table
$create_wishlist_table = "
CREATE TABLE IF NOT EXISTS wishlist (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist (user_id, product_id)
)";

if ($conn->query($create_wishlist_table) === TRUE) {
    echo "✅ Wishlist table created successfully!<br>";
} else {
    echo "❌ Error creating wishlist table: " . $conn->error . "<br>";
}

// Create recently viewed table
$create_recently_viewed_table = "
CREATE TABLE IF NOT EXISTS recently_viewed (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    product_id INT NOT NULL,
    session_id VARCHAR(255),
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_user_viewed (user_id, viewed_at),
    INDEX idx_session_viewed (session_id, viewed_at)
)";

if ($conn->query($create_recently_viewed_table) === TRUE) {
    echo "✅ Recently viewed table created successfully!<br>";
} else {
    echo "❌ Error creating recently viewed table: " . $conn->error . "<br>";
}

// Create loyalty points table
$create_loyalty_table = "
CREATE TABLE IF NOT EXISTS loyalty_points (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    points INT DEFAULT 0,
    total_earned INT DEFAULT 0,
    total_redeemed INT DEFAULT 0,
    tier ENUM('Bronze', 'Silver', 'Gold', 'Platinum') DEFAULT 'Bronze',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_loyalty (user_id)
)";

if ($conn->query($create_loyalty_table) === TRUE) {
    echo "✅ Loyalty points table created successfully!<br>";
} else {
    echo "❌ Error creating loyalty points table: " . $conn->error . "<br>";
}

// Create product notifications table
$create_notifications_table = "
CREATE TABLE IF NOT EXISTS product_notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    notification_type ENUM('stock_alert', 'price_drop', 'seasonal_reminder') DEFAULT 'stock_alert',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_notification (user_id, product_id, notification_type)
)";

if ($conn->query($create_notifications_table) === TRUE) {
    echo "✅ Product notifications table created successfully!<br>";
} else {
    echo "❌ Error creating product notifications table: " . $conn->error . "<br>";
}

// Create product comparisons table
$create_comparisons_table = "
CREATE TABLE IF NOT EXISTS product_comparisons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    session_id VARCHAR(255),
    product_ids JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_comparisons (user_id, created_at),
    INDEX idx_session_comparisons (session_id, created_at)
)";

if ($conn->query($create_comparisons_table) === TRUE) {
    echo "✅ Product comparisons table created successfully!<br>";
} else {
    echo "❌ Error creating product comparisons table: " . $conn->error . "<br>";
}

$conn->close();
echo "<br>🎉 All customer feature tables created successfully!";
?>

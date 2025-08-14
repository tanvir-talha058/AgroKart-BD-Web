<?php
require_once '../includes/db_connect.php';

// Create hot_deals table
$sql = "CREATE TABLE IF NOT EXISTS hot_deals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    original_price DECIMAL(10, 2) NOT NULL,
    discount_price DECIMAL(10, 2) NOT NULL,
    discount_percentage INT NOT NULL,
    start_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    end_date DATETIME NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_active (is_active),
    INDEX idx_dates (start_date, end_date)
)";

if ($conn->query($sql) === TRUE) {
    echo "Hot deals table created successfully";

    // Add some sample hot deals
    $sample_deals = [
        "INSERT IGNORE INTO hot_deals (product_id, original_price, discount_price, discount_percentage) 
         SELECT id, price, ROUND(price * 0.8, 2), 20 FROM products WHERE category = 'Vegetable' LIMIT 3",
        "INSERT IGNORE INTO hot_deals (product_id, original_price, discount_price, discount_percentage) 
         SELECT id, price, ROUND(price * 0.75, 2), 25 FROM products WHERE category = 'Fruit' LIMIT 2"
    ];

    foreach ($sample_deals as $deal_sql) {
        $conn->query($deal_sql);
    }

    echo "<br>Sample hot deals added successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();

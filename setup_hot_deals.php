<?php
require_once 'includes/db_connect.php';

// Enable error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

echo "<h2>Creating Hot Deals Table</h2>";

try {
    // Create hot_deals table
    $create_table_sql = "CREATE TABLE IF NOT EXISTS hot_deals (
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

    if ($conn->query($create_table_sql) === TRUE) {
        echo "<p style='color: green;'>✅ Hot deals table created successfully!</p>";

        // Check if products exist
        $products_check = $conn->query("SELECT COUNT(*) as count FROM products");
        $products_count = $products_check->fetch_assoc()['count'];

        echo "<p>Found $products_count products in database.</p>";

        if ($products_count > 0) {
            // Add sample hot deals
            $sample_deals = [
                "INSERT IGNORE INTO hot_deals (product_id, original_price, discount_price, discount_percentage, end_date) 
                 SELECT id, price, ROUND(price * 0.8, 2), 20, DATE_ADD(NOW(), INTERVAL 7 DAY) 
                 FROM products WHERE category = 'Vegetable' AND stock > 0 LIMIT 3",

                "INSERT IGNORE INTO hot_deals (product_id, original_price, discount_price, discount_percentage, end_date) 
                 SELECT id, price, ROUND(price * 0.75, 2), 25, DATE_ADD(NOW(), INTERVAL 5 DAY) 
                 FROM products WHERE category = 'Fruit' AND stock > 0 LIMIT 2",

                "INSERT IGNORE INTO hot_deals (product_id, original_price, discount_price, discount_percentage, end_date) 
                 SELECT id, price, ROUND(price * 0.7, 2), 30, DATE_ADD(NOW(), INTERVAL 3 DAY) 
                 FROM products WHERE stock > 10 LIMIT 2"
            ];

            $deals_added = 0;
            foreach ($sample_deals as $deal_sql) {
                if ($conn->query($deal_sql)) {
                    $deals_added += $conn->affected_rows;
                }
            }

            echo "<p style='color: green;'>✅ $deals_added sample hot deals added!</p>";

            // Show created deals
            $result = $conn->query("SELECT hd.*, p.name, p.category FROM hot_deals hd JOIN products p ON hd.product_id = p.id WHERE hd.is_active = 1");

            if ($result && $result->num_rows > 0) {
                echo "<h3>Created Hot Deals:</h3>";
                echo "<table border='1' cellpadding='10' cellspacing='0'>";
                echo "<tr><th>Product</th><th>Category</th><th>Original Price</th><th>Discount Price</th><th>Discount %</th><th>Valid Until</th></tr>";

                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                    echo "<td>৳" . number_format($row['original_price'], 2) . "</td>";
                    echo "<td>৳" . number_format($row['discount_price'], 2) . "</td>";
                    echo "<td>" . $row['discount_percentage'] . "%</td>";
                    echo "<td>" . $row['end_date'] . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        } else {
            echo "<p style='color: orange;'>⚠️ No products found. Please add some products first before creating hot deals.</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Error creating table: " . $conn->error . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='index.php'>← Go back to homepage</a></p>";
echo "<p><a href='products.php'>← Go to products dashboard</a></p>";

$conn->close();

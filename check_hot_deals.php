<?php
require_once 'includes/db_connect.php';

echo "<h2>🔧 AgroKartBD Hot Deals Database Check & Setup</h2>";

try {
    // Check if hot_deals table exists
    echo "<h3>📋 Checking Database Tables...</h3>";

    $tables_to_check = ['products', 'hot_deals'];
    $table_status = [];

    foreach ($tables_to_check as $table) {
        $check_sql = "SHOW TABLES LIKE '$table'";
        $result = $conn->query($check_sql);
        $table_status[$table] = $result->num_rows > 0;

        if ($table_status[$table]) {
            echo "<p style='color: green;'>✅ Table '$table' exists</p>";
        } else {
            echo "<p style='color: red;'>❌ Table '$table' does not exist</p>";
        }
    }

    // If products table exists but hot_deals doesn't, create it
    if ($table_status['products'] && !$table_status['hot_deals']) {
        echo "<h3>🛠️ Creating Hot Deals Table...</h3>";

        $create_sql = "CREATE TABLE hot_deals (
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

        if ($conn->query($create_sql)) {
            echo "<p style='color: green;'>✅ Hot deals table created successfully!</p>";

            // Add sample deals if products exist
            $products_result = $conn->query("SELECT COUNT(*) as count FROM products WHERE stock > 0");
            $products_count = $products_result->fetch_assoc()['count'];

            if ($products_count > 0) {
                echo "<h3>📦 Adding Sample Hot Deals...</h3>";

                $sample_deals = [
                    "INSERT IGNORE INTO hot_deals (product_id, original_price, discount_price, discount_percentage, end_date) 
                     SELECT id, price, ROUND(price * 0.8, 2), 20, DATE_ADD(NOW(), INTERVAL 7 DAY) 
                     FROM products WHERE stock > 0 ORDER BY RAND() LIMIT 3",

                    "INSERT IGNORE INTO hot_deals (product_id, original_price, discount_price, discount_percentage, end_date) 
                     SELECT id, price, ROUND(price * 0.75, 2), 25, DATE_ADD(NOW(), INTERVAL 5 DAY) 
                     FROM products WHERE stock > 5 ORDER BY RAND() LIMIT 2"
                ];

                $total_deals = 0;
                foreach ($sample_deals as $deal_sql) {
                    if ($conn->query($deal_sql)) {
                        $total_deals += $conn->affected_rows;
                    }
                }

                echo "<p style='color: green;'>✅ $total_deals sample hot deals added!</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Failed to create hot_deals table: " . $conn->error . "</p>";
        }
    }

    // Show current deals status
    if ($table_status['hot_deals'] || (!$table_status['hot_deals'] && $table_status['products'])) {
        echo "<h3>🔥 Current Hot Deals Status</h3>";

        $deals_sql = "SELECT hd.*, p.name, p.category, p.stock 
                      FROM hot_deals hd 
                      JOIN products p ON hd.product_id = p.id 
                      WHERE hd.is_active = 1 
                      ORDER BY hd.discount_percentage DESC";

        $deals_result = $conn->query($deals_sql);

        if ($deals_result && $deals_result->num_rows > 0) {
            echo "<p style='color: green;'>✅ " . $deals_result->num_rows . " active deals found!</p>";
            echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr style='background: #f0f0f0;'><th>Product</th><th>Category</th><th>Original Price</th><th>Deal Price</th><th>Discount</th><th>Stock</th><th>Valid Until</th></tr>";

            while ($deal = $deals_result->fetch_assoc()) {
                $expires = $deal['end_date'] ? date('M j, Y g:i A', strtotime($deal['end_date'])) : 'No expiry';
                echo "<tr>";
                echo "<td>" . htmlspecialchars($deal['name']) . "</td>";
                echo "<td>" . htmlspecialchars($deal['category']) . "</td>";
                echo "<td>৳" . number_format($deal['original_price'], 2) . "</td>";
                echo "<td style='color: green; font-weight: bold;'>৳" . number_format($deal['discount_price'], 2) . "</td>";
                echo "<td style='color: red; font-weight: bold;'>" . $deal['discount_percentage'] . "% OFF</td>";
                echo "<td>" . $deal['stock'] . "</td>";
                echo "<td>" . $expires . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: orange;'>⚠️ No active hot deals found.</p>";

            // Check if there are products available
            $products_check = $conn->query("SELECT COUNT(*) as count FROM products WHERE stock > 0");
            if ($products_check) {
                $products_count = $products_check->fetch_assoc()['count'];
                if ($products_count > 0) {
                    echo "<p>You have $products_count products available. You can create hot deals from the <a href='products.php'>Products Dashboard</a>.</p>";
                } else {
                    echo "<p>Please add some products first before creating hot deals.</p>";
                }
            }
        }
    }

    echo "<h3>🎯 Next Steps</h3>";
    echo "<ul>";
    echo "<li><a href='index.php'>View Homepage</a> - See your hot deals in action</li>";
    echo "<li><a href='products.php'>Products Dashboard</a> - Manage your products and create new hot deals</li>";
    echo "<li><a href='dashboard.php'>Main Dashboard</a> - Go to your seller dashboard</li>";
    echo "</ul>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

$conn->close();
?>

<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 1000px;
        margin: 0 auto;
        padding: 20px;
        background-color: #f8f9fa;
    }

    h2,
    h3 {
        color: #2c3e50;
    }

    table {
        width: 100%;
        background: white;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    th {
        background: #4CAF50;
        color: white;
        padding: 10px;
    }

    td {
        padding: 8px;
    }

    tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    a {
        color: #4CAF50;
        text-decoration: none;
    }

    a:hover {
        text-decoration: underline;
    }
</style>
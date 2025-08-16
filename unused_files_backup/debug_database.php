<?php
// Debug database structure
require_once 'includes/db_connect.php';

echo "=== DATABASE DEBUG INFO ===\n\n";

// Check if tables exist
$tables = ['users', 'products', 'user_cart', 'orders', 'order_items'];

foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "✓ Table '$table' exists\n";
        
        // Show table structure
        $structure = $conn->query("DESCRIBE $table");
        echo "  Columns in $table:\n";
        while ($row = $structure->fetch_assoc()) {
            echo "    - " . $row['Field'] . " (" . $row['Type'] . ")\n";
        }
        echo "\n";
    } else {
        echo "✗ Table '$table' does NOT exist\n\n";
    }
}

// Check the problematic query
echo "=== TESTING PROBLEMATIC QUERY ===\n";
$test_query = "SELECT uc.product_id, uc.quantity, p.name, p.price, p.image_path 
               FROM user_cart uc 
               JOIN products p ON uc.product_id = p.id 
               WHERE uc.user_id = 1";

try {
    $result = $conn->query($test_query);
    echo "✓ Query executed successfully\n";
    echo "Rows returned: " . $result->num_rows . "\n";
} catch (Exception $e) {
    echo "✗ Query failed: " . $e->getMessage() . "\n";
}

$conn->close();
?>

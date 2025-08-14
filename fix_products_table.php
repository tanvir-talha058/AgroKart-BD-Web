<?php
// Fix products table structure
require_once 'includes/db_connect.php';

echo "=== FIXING PRODUCTS TABLE ===\n";

// First, let's see what columns the products table currently has
echo "Current products table structure:\n";
try {
    $result = $conn->query("DESCRIBE products");
    while ($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "Error describing products table: " . $e->getMessage() . "\n";
}

echo "\n=== RECREATING PRODUCTS TABLE ===\n";

// Drop and recreate products table with correct structure
$sql = "DROP TABLE IF EXISTS products";
if ($conn->query($sql)) {
    echo "✓ Dropped existing products table\n";
}

$create_products = "CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `seller_id` int(11) NOT NULL DEFAULT 1,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `category` varchar(100) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

if ($conn->query($create_products)) {
    echo "✓ Created products table with correct structure\n";
} else {
    echo "✗ Error creating products table: " . $conn->error . "\n";
}

// Insert some sample products for testing
$sample_products = "INSERT INTO `products` (`name`, `description`, `price`, `stock`, `category`, `image_path`) VALUES
('Fresh Tomatoes', 'Organic red tomatoes, freshly harvested', 45.00, 100, 'Vegetables', 'images/uploads/tomatoes.jpg'),
('Basmati Rice', 'Premium quality basmati rice, 1kg pack', 120.00, 50, 'Grains', 'images/uploads/rice.jpg'),
('Fresh Spinach', 'Green leafy spinach, rich in iron', 25.00, 80, 'Vegetables', 'images/uploads/spinach.jpg'),
('Organic Carrots', 'Fresh orange carrots, vitamin rich', 35.00, 60, 'Vegetables', 'images/uploads/carrots.jpg');";

if ($conn->query($sample_products)) {
    echo "✓ Added sample products\n";
} else {
    echo "✗ Error adding sample products: " . $conn->error . "\n";
}

// Test the problematic query
echo "\n=== TESTING CART QUERY ===\n";
$test_query = "SELECT uc.product_id, uc.quantity, p.name, p.price, p.image_path, p.stock 
               FROM user_cart uc 
               JOIN products p ON uc.product_id = p.id 
               WHERE uc.user_id = 1";

try {
    $result = $conn->query($test_query);
    echo "✓ Cart query executed successfully\n";
    echo "Rows returned: " . $result->num_rows . "\n";
} catch (Exception $e) {
    echo "✗ Cart query failed: " . $e->getMessage() . "\n";
}

echo "\nFinal products table structure:\n";
$result = $conn->query("DESCRIBE products");
while ($row = $result->fetch_assoc()) {
    echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
}

$conn->close();
?>

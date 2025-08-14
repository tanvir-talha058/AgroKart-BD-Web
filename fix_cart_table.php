<?php
// Fix user_cart table creation
require_once 'includes/db_connect.php';

// First, let's check if the required tables exist
$check_users = "SHOW TABLES LIKE 'users'";
$check_products = "SHOW TABLES LIKE 'products'";

$users_exist = $conn->query($check_users)->num_rows > 0;
$products_exist = $conn->query($check_products)->num_rows > 0;

echo "Users table exists: " . ($users_exist ? "Yes" : "No") . "\n";
echo "Products table exists: " . ($products_exist ? "Yes" : "No") . "\n";

// Drop user_cart table if it exists (to start fresh)
$drop_sql = "DROP TABLE IF EXISTS `user_cart`";
if ($conn->query($drop_sql) === TRUE) {
    echo "Dropped existing user_cart table (if any)\n";
}

// Create user_cart table without foreign keys first
$sql = "CREATE TABLE `user_cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_product_unique` (`user_id`, `product_id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

if ($conn->query($sql) === TRUE) {
    echo "Table user_cart created successfully\n";
    
    // Now try to add foreign keys if the referenced tables exist
    if ($users_exist && $products_exist) {
        $fk1 = "ALTER TABLE `user_cart` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE";
        $fk2 = "ALTER TABLE `user_cart` ADD FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE";
        
        if ($conn->query($fk1) === TRUE) {
            echo "Added foreign key for user_id\n";
        } else {
            echo "Error adding user_id foreign key: " . $conn->error . "\n";
        }
        
        if ($conn->query($fk2) === TRUE) {
            echo "Added foreign key for product_id\n";
        } else {
            echo "Error adding product_id foreign key: " . $conn->error . "\n";
        }
    } else {
        echo "Skipping foreign keys - referenced tables don't exist\n";
    }
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

// Show all tables to verify
echo "\nAll tables in database:\n";
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_array()) {
    echo "- " . $row[0] . "\n";
}

$conn->close();
?>

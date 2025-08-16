<?php
// Complete database setup
$servername = "localhost";
$username = "root";
$password = "";

// Create connection without selecting database first
$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "=== COMPLETE DATABASE SETUP ===\n\n";

// Create database
$conn->query("CREATE DATABASE IF NOT EXISTS agrobd");
$conn->select_db("agrobd");
echo "✓ Database 'agrobd' created/selected\n";


$tables_to_drop = ['user_cart', 'order_items', 'orders', 'reviews', 'payments', 'products', 'users'];
foreach ($tables_to_drop as $table) {
    $conn->query("DROP TABLE IF EXISTS $table");
}
echo "✓ Dropped existing tables\n";

// Create users table
$users_sql = "CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `division` varchar(255) DEFAULT NULL,
  `district` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `role` enum('Buyer','Seller') NOT NULL,
  `profile_image_path` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

if ($conn->query($users_sql)) {
    echo "✓ Created users table\n";
} else {
    echo "✗ Error creating users table: " . $conn->error . "\n";
}

// Create products table
$products_sql = "CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `seller_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `category` varchar(100) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `seller_id` (`seller_id`),
  FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

if ($conn->query($products_sql)) {
    echo "✓ Created products table\n";
} else {
    echo "✗ Error creating products table: " . $conn->error . "\n";
}

// Create user_cart table
$cart_sql = "CREATE TABLE `user_cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_product` (`user_id`, `product_id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

if ($conn->query($cart_sql)) {
    echo "✓ Created user_cart table\n";
} else {
    echo "✗ Error creating user_cart table: " . $conn->error . "\n";
}

// Insert sample data
$insert_users = "INSERT INTO `users` (`name`, `email`, `phone`, `role`, `password`) VALUES
('Test Seller', 'seller@gmail.com', '01776199963', 'Seller', '\$2y\$10\$x8YHVIPGRffeFHLvIBrBK.tzbtecWjQn6gwQECdBADA0ufa2VVdSq'),
('Test Buyer', 'buyer@gmail.com', '01776199964', 'Buyer', '\$2y\$10\$QikQTfdWUHZHL8n8Ju/JAekg.ShXHPyZvx131Q0.SYZKJp4uQXNMO');";

if ($conn->query($insert_users)) {
    echo "✓ Inserted sample users\n";
} else {
    echo "✗ Error inserting users: " . $conn->error . "\n";
}

$insert_products = "INSERT INTO `products` (`seller_id`, `name`, `description`, `price`, `stock`, `category`, `image_path`) VALUES
(1, 'Fresh Tomatoes', 'Organic red tomatoes, freshly harvested', 45.00, 100, 'Vegetables', 'images/uploads/tomatoes.jpg'),
(1, 'Basmati Rice', 'Premium quality basmati rice, 1kg pack', 120.00, 50, 'Grains', 'images/uploads/rice.jpg'),
(1, 'Fresh Spinach', 'Green leafy spinach, rich in iron', 25.00, 80, 'Vegetables', 'images/uploads/spinach.jpg'),
(1, 'Organic Carrots', 'Fresh orange carrots, vitamin rich', 35.00, 60, 'Vegetables', 'images/uploads/carrots.jpg');";

if ($conn->query($insert_products)) {
    echo "✓ Inserted sample products\n";
} else {
    echo "✗ Error inserting products: " . $conn->error . "\n";
}

echo "\n=== TESTING CART QUERY ===\n";
$test_query = "SELECT uc.product_id, uc.quantity, p.name, p.price, p.image_path, p.stock 
               FROM user_cart uc 
               JOIN products p ON uc.product_id = p.id 
               WHERE uc.user_id = 1";

try {
    $result = $conn->query($test_query);
    echo "✓ Cart query executed successfully\n";
} catch (Exception $e) {
    echo "✗ Cart query failed: " . $e->getMessage() . "\n";
}

echo "\n=== DATABASE SETUP COMPLETE ===\n";
echo "You can now access your project at: http://localhost:8000\n";

$conn->close();
?>

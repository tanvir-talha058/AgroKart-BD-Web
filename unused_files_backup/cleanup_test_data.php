<?php
// Clean up sample/test data from database
require_once 'includes/db_connect.php';

echo "=== CLEANING UP TEST DATA ===\n\n";

// Remove test products
$delete_products = "DELETE FROM products WHERE seller_id = 1 AND name IN ('Fresh Tomatoes', 'Basmati Rice', 'Fresh Spinach', 'Organic Carrots')";
if ($conn->query($delete_products)) {
    echo "✓ Removed test products\n";
    echo "  Affected rows: " . $conn->affected_rows . "\n";
} else {
    echo "✗ Error removing test products: " . $conn->error . "\n";
}

// Remove test users (but keep your real accounts)
$delete_test_users = "DELETE FROM users WHERE email IN ('seller@gmail.com', 'buyer@gmail.com')";
if ($conn->query($delete_test_users)) {
    echo "✓ Removed test users\n";
    echo "  Affected rows: " . $conn->affected_rows . "\n";
} else {
    echo "✗ Error removing test users: " . $conn->error . "\n";
}

// Clear any test cart items
$clear_cart = "DELETE FROM user_cart WHERE user_id NOT IN (SELECT id FROM users)";
if ($conn->query($clear_cart)) {
    echo "✓ Cleared orphaned cart items\n";
    echo "  Affected rows: " . $conn->affected_rows . "\n";
} else {
    echo "✗ Error clearing cart: " . $conn->error . "\n";
}

// Show current database state
echo "\n=== CURRENT DATABASE STATE ===\n";

$users_count = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$products_count = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$cart_count = $conn->query("SELECT COUNT(*) as count FROM user_cart")->fetch_assoc()['count'];

echo "Users: $users_count\n";
echo "Products: $products_count\n";
echo "Cart items: $cart_count\n";

if ($products_count > 0) {
    echo "\nRemaining products:\n";
    $products = $conn->query("SELECT id, name, seller_id FROM products");
    while ($product = $products->fetch_assoc()) {
        echo "  - ID: {$product['id']}, Name: {$product['name']}, Seller ID: {$product['seller_id']}\n";
    }
}

if ($users_count > 0) {
    echo "\nRemaining users:\n";
    $users = $conn->query("SELECT id, name, email, role FROM users");
    while ($user = $users->fetch_assoc()) {
        echo "  - ID: {$user['id']}, Name: {$user['name']}, Email: {$user['email']}, Role: {$user['role']}\n";
    }
}

echo "\n=== CLEANUP COMPLETE ===\n";
echo "Your database now only contains data you actually created.\n";
echo "You can now add products through your seller account.\n";

$conn->close();
?>

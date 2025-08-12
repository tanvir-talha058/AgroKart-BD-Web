<?php
// php/delete_product.php
session_start();
require_once '../includes/db_connect.php';

// Check if user is logged in and is a seller
if (!isset($_SESSION['loggedin']) || $_SESSION['user_role'] !== 'Seller') {
    $_SESSION['error'] = "You don't have permission to perform this action.";
    header('Location: ../dashboard.php');
    exit;
}

$seller_id = $_SESSION['user_id'];

// Check if product_id is provided
if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
    $_SESSION['error'] = "Invalid request. Product ID is missing.";
    header('Location: ../products.php');
    exit;
}

$product_id = (int)$_POST['product_id'];

// First, verify that this product belongs to the logged-in seller
$check_sql = "SELECT * FROM products WHERE id = ? AND seller_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $product_id, $seller_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "You don't have permission to delete this product.";
    header('Location: ../products.php');
    exit;
}

// Get product image path before deleting
$product = $result->fetch_assoc();
$image_path = $product['image_path'];
$check_stmt->close();

// Begin transaction to ensure all related operations complete or none
$conn->begin_transaction();
try {
    // Delete from cart_items
    $delete_cart_sql = "DELETE FROM cart_items WHERE product_id = ?";
    $delete_cart_stmt = $conn->prepare($delete_cart_sql);
    $delete_cart_stmt->bind_param("i", $product_id);
    $delete_cart_stmt->execute();
    $delete_cart_stmt->close();

    // Delete from order_items (for future orders - existing orders should keep the product record)
    // Note: In a real-world scenario, you might want to keep product records for order history
    // This is a simplified approach
    $delete_order_sql = "DELETE FROM order_items WHERE product_id = ?";
    $delete_order_stmt = $conn->prepare($delete_order_sql);
    $delete_order_stmt->bind_param("i", $product_id);
    $delete_order_stmt->execute();
    $delete_order_stmt->close();

    // Delete product reviews if any
    $delete_reviews_sql = "DELETE FROM product_reviews WHERE product_id = ?";
    $delete_reviews_stmt = $conn->prepare($delete_reviews_sql);
    $delete_reviews_stmt->bind_param("i", $product_id);
    $delete_reviews_stmt->execute();
    $delete_reviews_stmt->close();

    // Finally delete the product
    $delete_product_sql = "DELETE FROM products WHERE id = ?";
    $delete_product_stmt = $conn->prepare($delete_product_sql);
    $delete_product_stmt->bind_param("i", $product_id);
    $delete_product_stmt->execute();
    $delete_product_stmt->close();

    // Commit the transaction
    $conn->commit();

    // Delete the image file if it exists
    $full_image_path = "../" . $image_path;
    if (file_exists($full_image_path) && is_file($full_image_path)) {
        unlink($full_image_path);
    }

    $_SESSION['message'] = "Product has been deleted successfully.";
} catch (Exception $e) {
    // Roll back the transaction if something went wrong
    $conn->rollback();
    $_SESSION['error'] = "Failed to delete product: " . $e->getMessage();
}

header('Location: ../products.php');
exit;

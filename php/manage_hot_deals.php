<?php
session_start();
require_once '../includes/db_connect.php';

// Define a function to set a notification in session
function setNotification($type, $message)
{
    $_SESSION['notification'] = [
        'type' => $type,
        'message' => $message,
        'time' => time()
    ];
}

// Define a function to redirect back to the previous page
function redirectBack()
{
    // Get the referring page or default to dashboard
    $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../dashboard.php';
    header('Location: ' . $redirect);
    exit;
}

// First check if hot_deals table exists, create if not
$table_check = $conn->query("SHOW TABLES LIKE 'hot_deals'");

if ($table_check->num_rows == 0) {
    // Create the table first
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

    if (!$conn->query($create_sql)) {
        setNotification('error', 'Failed to create hot_deals table: ' . $conn->error);
        redirectBack();
    }
}

// Check if user is logged in and is a seller
if (!isset($_SESSION['loggedin']) || $_SESSION['user_role'] !== 'Seller') {
    setNotification('error', 'Unauthorized access');
    redirectBack();
}

$seller_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $product_id = (int)$_POST['product_id'];
        $discount_percentage = (float)$_POST['discount_percentage'];
        $discount_price = (float)$_POST['discount_price'];
        $valid_until = isset($_POST['valid_until']) && !empty($_POST['valid_until']) ? $_POST['valid_until'] : NULL;

        // Verify the product belongs to this seller
        $verify_sql = "SELECT id, price FROM products WHERE id = ? AND seller_id = ?";
        $verify_stmt = $conn->prepare($verify_sql);
        $verify_stmt->bind_param("ii", $product_id, $seller_id);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();

        if ($verify_result->num_rows === 0) {
            setNotification('error', 'Product not found or unauthorized');
            $verify_stmt->close();
            redirectBack();
        }

        $product = $verify_result->fetch_assoc();
        $verify_stmt->close();

        // Validate discount
        if ($discount_percentage <= 0 || $discount_percentage >= 100) {
            setNotification('error', 'Invalid discount percentage');
            redirectBack();
        }

        // Calculate expected discount price for validation
        $expected_discount_price = $product['price'] - ($product['price'] * $discount_percentage / 100);
        if (abs($discount_price - $expected_discount_price) > 0.01) {
            setNotification('error', 'Discount price calculation mismatch');
            redirectBack();
        }

        // Check if there's already an active deal for this product
        $check_sql = "SELECT id FROM hot_deals WHERE product_id = ? AND is_active = 1";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $product_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            // Update existing deal
            $deal_id = $check_result->fetch_assoc()['id'];
            $update_sql = "UPDATE hot_deals SET discount_percentage = ?, discount_price = ?, end_date = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("dssi", $discount_percentage, $discount_price, $valid_until, $deal_id);

            if ($update_stmt->execute()) {
                setNotification('success', 'Hot deal updated successfully');
            } else {
                setNotification('error', 'Failed to update hot deal: ' . $conn->error);
            }
            $update_stmt->close();
            redirectBack();
        } else {
            // Insert new deal
            $insert_sql = "INSERT INTO hot_deals (product_id, original_price, discount_price, discount_percentage, end_date, is_active) VALUES (?, ?, ?, ?, ?, 1)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("iddds", $product_id, $product['price'], $discount_price, $discount_percentage, $valid_until);

            if ($insert_stmt->execute()) {
                setNotification('success', 'Hot deal created successfully');
            } else {
                setNotification('error', 'Failed to create hot deal: ' . $conn->error);
            }
            $insert_stmt->close();
            redirectBack();
        }
        $check_stmt->close();
    } elseif ($action === 'remove') {
        $product_id = (int)$_POST['product_id'];

        // Verify the product belongs to this seller
        $verify_sql = "SELECT id FROM products WHERE id = ? AND seller_id = ?";
        $verify_stmt = $conn->prepare($verify_sql);
        $verify_stmt->bind_param("ii", $product_id, $seller_id);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();

        if ($verify_result->num_rows === 0) {
            setNotification('error', 'Product not found or unauthorized');
            $verify_stmt->close();
            redirectBack();
        }
        $verify_stmt->close();

        // Remove/deactivate the hot deal
        $remove_sql = "UPDATE hot_deals SET is_active = 0, updated_at = CURRENT_TIMESTAMP WHERE product_id = ? AND is_active = 1";
        $remove_stmt = $conn->prepare($remove_sql);
        $remove_stmt->bind_param("i", $product_id);

        if ($remove_stmt->execute() && $remove_stmt->affected_rows > 0) {
            setNotification('success', 'Hot deal removed successfully');
        } else {
            setNotification('warning', 'No active hot deal found for this product');
        }
        $remove_stmt->close();
        redirectBack();
    } else {
        setNotification('error', 'Invalid action');
        redirectBack();
    }
} else {
    setNotification('error', 'Invalid request method');
    redirectBack();
}

$conn->close();

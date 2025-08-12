<?php

// FILE: /php/reorder_items.php
require_once '../includes/db_connect.php';

if (!isset($_SESSION['loggedin'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'You must be logged in to reorder items.']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['order_id'])) {
    $order_id = intval($_POST['order_id']);
    $user_id = $_SESSION['user_id'];
    
    // Check if the order belongs to this user
    $check_sql = "SELECT id FROM orders WHERE id = ? AND buyer_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $order_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Order not found or you do not have permission to reorder it.']);
        exit();
    }
    
    // Get the order items
    $items_sql = "SELECT oi.product_id, oi.quantity, p.stock, p.name, p.price, p.image_path
                 FROM order_items oi 
                 JOIN products p ON oi.product_id = p.id 
                 WHERE oi.order_id = ? AND p.stock > 0";
    $items_stmt = $conn->prepare($items_sql);
    $items_stmt->bind_param("i", $order_id);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();
    
    // Initialize cart if needed
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    $added_count = 0;
    $out_of_stock = [];
    
    // Add items to cart
    while ($item = $items_result->fetch_assoc()) {
        $product_id = $item['product_id'];
        $requested_qty = min($item['quantity'], $item['stock']); // Don't add more than available stock
        
        if ($requested_qty > 0) {
            // If product already in cart, increase quantity
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id]['quantity'] += $requested_qty;
                // Make sure we don't exceed available stock
                if ($_SESSION['cart'][$product_id]['quantity'] > $item['stock']) {
                    $_SESSION['cart'][$product_id]['quantity'] = $item['stock'];
                }
            } else {
                // Add new product to cart
                $_SESSION['cart'][$product_id] = [
                    'name' => $item['name'],
                    'price' => $item['price'],
                    'image' => $item['image_path'],
                    'quantity' => $requested_qty,
                    'stock' => $item['stock']
                ];
            }
            $added_count++;
        } else {
            $out_of_stock[] = $item['name'];
        }
    }
    
    // Prepare response message
    if ($added_count > 0) {
        $message = $added_count . ' item(s) added to your cart.';
        if (count($out_of_stock) > 0) {
            $message .= ' Some items could not be added due to insufficient stock.';
        }
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => $message,
            'cart_count' => count($_SESSION['cart'])
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'No items could be added to cart. All items are out of stock.'
        ]);
    }
    
    $check_stmt->close();
    $items_stmt->close();
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Missing required parameters.']);
}

$conn->close();
?>
<?php

// FILE: /php/get_order_details.php
require_once '../includes/db_connect.php';

if (!isset($_SESSION['loggedin'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'You must be logged in to view order details.']);
    exit();
}

if (isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);
    $user_id = $_SESSION['user_id'];

    // First get the order details
    $order_sql = "SELECT id, buyer_id, total_amount, delivery_location, status, created_at, notes FROM orders WHERE id = ? AND buyer_id = ?";
    $order_stmt = $conn->prepare($order_sql);
    $order_stmt->bind_param("ii", $order_id, $user_id);
    $order_stmt->execute();
    $order_result = $order_stmt->get_result();

    if ($order_result->num_rows === 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Order not found or you do not have permission to view it.']);
        exit();
    }

    $order = $order_result->fetch_assoc();

    // Now get the order items
    $items_sql = "SELECT oi.quantity, oi.price, p.name, p.image_path as image 
                 FROM order_items oi 
                 JOIN products p ON oi.product_id = p.id 
                 WHERE oi.order_id = ?";
    $items_stmt = $conn->prepare($items_sql);
    $items_stmt->bind_param("i", $order_id);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();

    $items = [];
    while ($item = $items_result->fetch_assoc()) {
        $items[] = $item;
    }

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'order' => $order,
        'items' => $items
    ]);

    $order_stmt->close();
    $items_stmt->close();
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Order ID is required.']);
}

$conn->close();

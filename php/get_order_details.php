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

    // Check if the user is a seller
    $is_seller = ($_SESSION['user_role'] === 'Seller');

    // First get the order details
    if ($is_seller) {
        // For sellers, check if the order contains their products
        $order_sql = "SELECT o.id, o.buyer_id, o.total_amount, o.delivery_location, o.status, o.created_at, o.notes 
                      FROM orders o
                      JOIN order_items oi ON o.id = oi.order_id
                      JOIN products p ON oi.product_id = p.id
                      WHERE o.id = ? AND p.seller_id = ?
                      GROUP BY o.id";
        $order_stmt = $conn->prepare($order_sql);
        $order_stmt->bind_param("ii", $order_id, $user_id);
    } else {
        // For buyers, check if they placed the order
        $order_sql = "SELECT id, buyer_id, total_amount, delivery_location, status, created_at, notes 
                      FROM orders WHERE id = ? AND buyer_id = ?";
        $order_stmt = $conn->prepare($order_sql);
        $order_stmt->bind_param("ii", $order_id, $user_id);
    }

    $order_stmt->execute();
    $order_result = $order_stmt->get_result();

    if ($order_result->num_rows === 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Order not found or you do not have permission to view it.']);
        exit();
    }

    $order = $order_result->fetch_assoc();

    // Get buyer info
    $user_sql = "SELECT u.name, u.email, u.phone, u.city, u.district, u.division
                 FROM orders o
                 JOIN users u ON o.buyer_id = u.id
                 WHERE o.id = ?";
    $user_stmt = $conn->prepare($user_sql);
    $user_stmt->bind_param("i", $order_id);
    $user_stmt->execute();
    $user = $user_stmt->get_result()->fetch_assoc();
    $user_stmt->close();

    // Now get the order items
    if ($is_seller) {
        // For sellers, only show their products
        $items_sql = "SELECT oi.quantity, oi.price, p.name, p.unit, p.image_path 
                     FROM order_items oi 
                     JOIN products p ON oi.product_id = p.id 
                     WHERE oi.order_id = ? AND p.seller_id = ?";
        $items_stmt = $conn->prepare($items_sql);
        $items_stmt->bind_param("ii", $order_id, $user_id);
    } else {
        // For buyers, show all products in the order
        $items_sql = "SELECT oi.quantity, oi.price, p.name, p.unit, p.image_path 
                     FROM order_items oi 
                     JOIN products p ON oi.product_id = p.id 
                     WHERE oi.order_id = ?";
        $items_stmt = $conn->prepare($items_sql);
        $items_stmt->bind_param("i", $order_id);
    }

    $items_stmt->execute();
    $items_result = $items_stmt->get_result();

    $items = [];
    while ($item = $items_result->fetch_assoc()) {
        // Ensure the image path is correctly formatted
        $imagePath = !empty($item['image_path']) ? $item['image_path'] : 'images/AGrO.png';

        $items[] = [
            'name' => $item['name'],
            'quantity' => $item['quantity'],
            'price' => $item['price'],
            'unit' => $item['unit'] ?? 'kg', // Default to kg if not set
            'image_path' => $imagePath
        ];
    }
    $items_stmt->close();

    // Calculate total amount
    $total_amount = 0;
    foreach ($items as $item) {
        $total_amount += $item['quantity'] * $item['price'];
    }

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'order' => [
            'order_id' => $order['id'],
            'status' => $order['status'],
            'created_at' => $order['created_at'],
            'notes' => $order['notes'] ?? '',
            'total_amount' => $total_amount,
            'buyer_name' => $user['name'] ?? 'Unknown',
            'buyer_email' => $user['email'] ?? 'No email',
            'buyer_phone' => $user['phone'] ?? 'No phone',
            'location' => !empty($user) ? implode(', ', array_filter([$user['city'], $user['district'], $user['division']])) : 'No address',
            'items' => $items
        ]
    ]);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Order ID is required.']);
}

$conn->close();

<?php

// FILE: /php/update_order_status_user.php
require_once '../includes/db_connect.php';

if (!isset($_SESSION['loggedin'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'You must be logged in to update order status.']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['order_id']) && isset($_POST['action'])) {
    $order_id = intval($_POST['order_id']);
    $action = $_POST['action'];
    $user_id = $_SESSION['user_id'];

    // Check if the order belongs to this user
    $check_sql = "SELECT status FROM orders WHERE id = ? AND buyer_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $order_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows === 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Order not found or you do not have permission to update it.']);
        exit();
    }

    $current_status = $check_result->fetch_assoc()['status'];

    // Only allow cancellation if order is in 'Pending' status
    if ($action === 'cancel') {
        if ($current_status !== 'Pending') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Only pending orders can be cancelled.']);
            exit();
        }

        // Update order status to 'Cancelled'
        $update_sql = "UPDATE orders SET status = 'Cancelled' WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $order_id);

        if ($update_stmt->execute()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Order has been cancelled successfully.'
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Failed to cancel the order. Please try again.'
            ]);
        }
        $update_stmt->close();
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid action specified.']);
    }

    $check_stmt->close();
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Missing required parameters.']);
}

$conn->close();

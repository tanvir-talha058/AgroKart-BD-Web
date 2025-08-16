<?php
// FILE: /php/update_order_status.php
require_once '../includes/db_connect.php';
if (!isset($_SESSION['loggedin']) || $_SESSION['user_role'] !== 'Seller') {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    $seller_id = $_SESSION['user_id'];

    // Security check: Ensure the order contains a product from this seller
    $stmt_check = $conn->prepare("SELECT o.id FROM orders o JOIN order_items oi ON o.id = oi.order_id JOIN products p ON oi.product_id = p.id WHERE o.id = ? AND p.seller_id = ? LIMIT 1");
    $stmt_check->bind_param("ii", $order_id, $seller_id);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        // No need to check for column existence - use ensure_order_columns.php for table structure

        if ($new_status === 'Delivered') {
            // When delivered, stamp delivered_at to now
            $stmt = $conn->prepare("UPDATE orders SET status = ?, delivered_at = NOW() WHERE id = ?");
        } else {
            // For other statuses, keep delivered_at as is (or nullify if needed)
            $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        }
        $stmt->bind_param("si", $new_status, $order_id);
        if ($stmt->execute()) {
            // Set flag to refresh dashboard data
            $_SESSION['refresh_dashboard'] = true;
            // Clear any cached chart data
            if (isset($_SESSION['dashboard_chart_data'])) {
                unset($_SESSION['dashboard_chart_data']);
            }
            $_SESSION['message'] = "Order #$order_id status updated to '$new_status'.";

            // Add a JavaScript function to refresh charts on dashboard when redirected
            $_SESSION['refresh_charts'] = true;
        } else {
            $_SESSION['error'] = "Failed to update status.";
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "You don't have permission to update this order.";
    }
    $stmt_check->close();
}

// Check if redirect_to parameter is provided
$redirect_to = isset($_POST['redirect_to']) ? $_POST['redirect_to'] : 'dashboard';
$redirect_page = ($redirect_to === 'orders') ? '../orders.php' : '../dashboard.php';

header("Location: $redirect_page");
exit();

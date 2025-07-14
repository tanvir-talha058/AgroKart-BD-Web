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
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $order_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Order #$order_id status updated to '$new_status'.";
        } else {
            $_SESSION['error'] = "Failed to update status.";
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "You are not authorized to update this order.";
    }
    $stmt_check->close();
}
header("Location: ../dashboard.php");
exit();
?>

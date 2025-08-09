<?php
// FILE: /php/order_process.php
require_once '../includes/db_connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['user_role'] !== 'Buyer') {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_SESSION['cart'])) {
    $buyer_id = $_SESSION['user_id'];
    $delivery_location = trim($_POST['location']);
    $payment_method = trim($_POST['payment_method']);
    $order_notes = isset($_POST['order_notes']) ? trim($_POST['order_notes']) : '';
    $total_amount = 0;

    // Calculate total amount
    foreach ($_SESSION['cart'] as $item) {
        $total_amount += $item['price'] * $item['quantity'];
    }

    // Use a transaction to ensure all queries succeed or none do
    $conn->begin_transaction();

    try {
        // 1. Insert into orders table
        $stmt_order = $conn->prepare("INSERT INTO orders (buyer_id, total_amount, delivery_location, notes) VALUES (?, ?, ?, ?)");
        $stmt_order->bind_param("idss", $buyer_id, $total_amount, $delivery_location, $order_notes);
        $stmt_order->execute();
        $order_id = $stmt_order->insert_id;
        $stmt_order->close();

        // 2. Insert into order_items table and update product stock
        $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

        foreach ($_SESSION['cart'] as $product_id => $item) {
            $stmt_item->bind_param("iiid", $order_id, $product_id, $item['quantity'], $item['price']);
            $stmt_item->execute();

            $stmt_stock->bind_param("ii", $item['quantity'], $product_id);
            $stmt_stock->execute();
        }
        $stmt_item->close();
        $stmt_stock->close();

        // 3. Insert into payments table
        $transaction_id = "AGRO-" . strtoupper($payment_method) . "-" . time(); // Simulated TXN ID
        $stmt_payment = $conn->prepare("INSERT INTO payments (order_id, transaction_id, method) VALUES (?, ?, ?)");
        $stmt_payment->bind_param("iss", $order_id, $transaction_id, $payment_method);
        $stmt_payment->execute();
        $stmt_payment->close();

        // If all queries were successful, commit the transaction
        $conn->commit();

        // Clear the cart from session
        unset($_SESSION['cart']);

        // Clear the cart from database for this user
        $clear_cart_stmt = $conn->prepare("DELETE FROM user_cart WHERE user_id = ?");
        $clear_cart_stmt->bind_param("i", $buyer_id);
        $clear_cart_stmt->execute();
        $clear_cart_stmt->close();

        $_SESSION['last_order_id'] = $order_id;
        header('Location: ../payment_success.php');
        exit();
    } catch (mysqli_sql_exception $exception) {
        // If any query fails, roll back the transaction
        $conn->rollback();
        $_SESSION['error'] = "Order could not be placed. Please try again. Error: " . $exception->getMessage();
        header('Location: ../checkout.php');
        exit();
    }
} else {
    header('Location: ../cart.php');
    exit();
}

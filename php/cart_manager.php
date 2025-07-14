<?php
// FILE: /php/cart_manager.php
require_once '../includes/db_connect.php';

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $product_id = $_POST['product_id'];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    switch ($action) {
        case 'add':
            $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
            // Check if product exists in cart
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id]['quantity'] += $quantity;
            } else {
                // Fetch product details from DB
                $stmt = $conn->prepare("SELECT name, price, image_path, stock FROM products WHERE id = ?");
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($product = $result->fetch_assoc()) {
                    if ($quantity <= $product['stock']) {
                        $_SESSION['cart'][$product_id] = [
                            'name' => $product['name'],
                            'price' => $product['price'],
                            'image' => $product['image_path'],
                            'quantity' => $quantity,
                            'stock' => $product['stock']
                        ];
                    } else {
                         $_SESSION['error'] = "Cannot add more than available stock.";
                    }
                }
                $stmt->close();
            }
            break;

        case 'update':
            $quantity = (int)$_POST['quantity'];
            if (isset($_SESSION['cart'][$product_id])) {
                if ($quantity > 0 && $quantity <= $_SESSION['cart'][$product_id]['stock']) {
                    $_SESSION['cart'][$product_id]['quantity'] = $quantity;
                } elseif ($quantity > $_SESSION['cart'][$product_id]['stock']) {
                    $_SESSION['error'] = "Cannot add more than available stock.";
                } else {
                    unset($_SESSION['cart'][$product_id]);
                }
            }
            break;

        case 'remove':
            if (isset($_SESSION['cart'][$product_id])) {
                unset($_SESSION['cart'][$product_id]);
            }
            break;
    }
}
header('Location: ../cart.php');
exit();
?>
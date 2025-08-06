<?php
// FILE: /php/cart_manager.php
require_once '../includes/db_connect.php';

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $product_id = $_POST['product_id'];
    $is_ajax = isset($_POST['ajax']) && $_POST['ajax'] === '1';

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $response = ['success' => false, 'message' => '', 'cart_count' => 0];

    switch ($action) {
        case 'add':
            $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
            // Check if product exists in cart
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id]['quantity'] += $quantity;
                $response['success'] = true;
                $response['message'] = 'Product quantity updated in cart!';
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
                        $response['success'] = true;
                        $response['message'] = 'Product added to cart successfully!';
                    } else {
                        $response['message'] = 'Cannot add more than available stock.';
                        $_SESSION['error'] = "Cannot add more than available stock.";
                    }
                } else {
                    $response['message'] = 'Product not found.';
                }
                $stmt->close();
            }
            break;

        case 'update':
            $quantity = (int)$_POST['quantity'];
            if (isset($_SESSION['cart'][$product_id])) {
                if ($quantity > 0 && $quantity <= $_SESSION['cart'][$product_id]['stock']) {
                    $_SESSION['cart'][$product_id]['quantity'] = $quantity;
                    $response['success'] = true;
                    $response['message'] = 'Cart updated successfully!';
                } elseif ($quantity > $_SESSION['cart'][$product_id]['stock']) {
                    $response['message'] = 'Cannot add more than available stock.';
                    $_SESSION['error'] = "Cannot add more than available stock.";
                } else {
                    unset($_SESSION['cart'][$product_id]);
                    $response['success'] = true;
                    $response['message'] = 'Product removed from cart!';
                }
            }
            break;

        case 'remove':
            if (isset($_SESSION['cart'][$product_id])) {
                unset($_SESSION['cart'][$product_id]);
                $response['success'] = true;
                $response['message'] = 'Product removed from cart!';
            }
            break;
    }

    // Calculate cart count (total quantity of all items)
    $cart_count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
    $response['cart_count'] = $cart_count;

    // Calculate cart totals for JSON response
    if ($is_ajax) {
        $cart_total = 0;
        foreach ($_SESSION['cart'] as $pid => $item) {
            $cart_total += $item['price'] * $item['quantity'];
        }
        $response['cart_total'] = $cart_total;

        // For update action, calculate item total
        if ($action == 'update' && isset($_SESSION['cart'][$product_id])) {
            $response['item_total'] = $_SESSION['cart'][$product_id]['price'] * $_SESSION['cart'][$product_id]['quantity'];
        }

        // Make sure cart count is set
        $response['cart_count'] = count($_SESSION['cart']);

        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }
}

// Default redirect for non-AJAX requests
header('Location: ../cart.php');
exit();

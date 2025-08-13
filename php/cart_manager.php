<?php
// FILE: /php/cart_manager.php
require_once '../includes/db_connect.php';

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $product_id = $_POST['product_id'];
    $is_ajax = isset($_POST['ajax']) && $_POST['ajax'] === '1';

    // Initialize session cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Check if user is logged in
    $is_logged_in = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
    $user_id = $is_logged_in ? $_SESSION['user_id'] : 0;

    $response = ['success' => false, 'message' => '', 'cart_count' => 0];

    switch ($action) {
        case 'add':
            $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
            $deal_price = isset($_POST['deal_price']) ? floatval($_POST['deal_price']) : null;

            // Fetch product details from DB
            $stmt = $conn->prepare("SELECT name, price, unit, image_path, stock FROM products WHERE id = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($product = $result->fetch_assoc()) {
                // Use deal price if available, otherwise use regular price
                $final_price = $deal_price ? $deal_price : $product['price'];

                // Check stock availability
                if ($quantity > $product['stock']) {
                    $response['message'] = 'Cannot add more than available stock.';
                    $_SESSION['error'] = "Cannot add more than available stock.";
                    $stmt->close();
                    break;
                }

                // If user is logged in, update the database cart
                if ($is_logged_in) {
                    // Check if product already exists in user's cart
                    $check_stmt = $conn->prepare("SELECT quantity, price FROM user_cart WHERE user_id = ? AND product_id = ?");
                    $check_stmt->bind_param("ii", $user_id, $product_id);
                    $check_stmt->execute();
                    $check_result = $check_stmt->get_result();

                    if ($check_result->num_rows > 0) {
                        // Update existing cart item with new price if it's a deal
                        $cart_item = $check_result->fetch_assoc();
                        $new_quantity = $cart_item['quantity'] + $quantity;
                        $update_price = $deal_price ? $deal_price : $cart_item['price']; // Use deal price if available

                        $update_stmt = $conn->prepare("UPDATE user_cart SET quantity = ?, price = ? WHERE user_id = ? AND product_id = ?");
                        $update_stmt->bind_param("idii", $new_quantity, $update_price, $user_id, $product_id);
                        $new_quantity = $cart_item['quantity'] + $quantity;

                        if ($new_quantity > $product['stock']) {
                            $new_quantity = $product['stock'];
                        }

                        $update_stmt = $conn->prepare("UPDATE user_cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
                        $update_stmt->bind_param("iii", $new_quantity, $user_id, $product_id);
                        $update_stmt->execute();
                        $update_stmt->close();
                    } else {
                        // Add new cart item
                        $insert_stmt = $conn->prepare("INSERT INTO user_cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                        $insert_stmt->bind_param("iii", $user_id, $product_id, $quantity);
                        $insert_stmt->execute();
                        $insert_stmt->close();
                    }
                    $check_stmt->close();
                }

                // Also update the session cart (for both logged in and non-logged in users)
                if (isset($_SESSION['cart'][$product_id])) {
                    $_SESSION['cart'][$product_id]['quantity'] += $quantity;
                    // Update price if it's a better deal
                    if ($deal_price && $deal_price < $_SESSION['cart'][$product_id]['price']) {
                        $_SESSION['cart'][$product_id]['price'] = $deal_price;
                        $_SESSION['cart'][$product_id]['is_deal'] = true;
                    }
                } else {
                    $_SESSION['cart'][$product_id] = [
                        'name' => $product['name'],
                        'price' => $final_price,
                        'unit' => $product['unit'],
                        'image' => $product['image_path'],
                        'quantity' => $quantity,
                        'stock' => $product['stock'],
                        'is_deal' => $deal_price ? true : false,
                        'original_price' => $deal_price ? $product['price'] : null
                    ];
                }

                $response['success'] = true;
                $response['message'] = 'Product added to cart successfully!';
            } else {
                $response['message'] = 'Product not found.';
            }
            $stmt->close();
            break;

        case 'update':
            $quantity = (int)$_POST['quantity'];
            if (isset($_SESSION['cart'][$product_id])) {
                if ($quantity > 0 && $quantity <= $_SESSION['cart'][$product_id]['stock']) {
                    // Update session cart
                    $_SESSION['cart'][$product_id]['quantity'] = $quantity;

                    // Update database cart if user is logged in
                    if ($is_logged_in) {
                        $update_stmt = $conn->prepare("UPDATE user_cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
                        $update_stmt->bind_param("iii", $quantity, $user_id, $product_id);
                        $update_stmt->execute();
                        $update_stmt->close();
                    }

                    $response['success'] = true;
                    $response['message'] = 'Cart updated successfully!';
                } elseif ($quantity > $_SESSION['cart'][$product_id]['stock']) {
                    $response['message'] = 'Cannot add more than available stock.';
                    $_SESSION['error'] = "Cannot add more than available stock.";
                } else {
                    // Remove from session cart
                    unset($_SESSION['cart'][$product_id]);

                    // Remove from database cart if user is logged in
                    if ($is_logged_in) {
                        $delete_stmt = $conn->prepare("DELETE FROM user_cart WHERE user_id = ? AND product_id = ?");
                        $delete_stmt->bind_param("ii", $user_id, $product_id);
                        $delete_stmt->execute();
                        $delete_stmt->close();
                    }

                    $response['success'] = true;
                    $response['message'] = 'Product removed from cart!';
                }
            }
            break;

        case 'remove':
            if (isset($_SESSION['cart'][$product_id])) {
                // Remove from session cart
                unset($_SESSION['cart'][$product_id]);

                // Remove from database cart if user is logged in
                if ($is_logged_in) {
                    $delete_stmt = $conn->prepare("DELETE FROM user_cart WHERE user_id = ? AND product_id = ?");
                    $delete_stmt->bind_param("ii", $user_id, $product_id);
                    $delete_stmt->execute();
                    $delete_stmt->close();
                }

                $response['success'] = true;
                $response['message'] = 'Product removed from cart!';
            }
            break;
    }

    // Calculate cart count (number of unique products)
    $cart_count = count($_SESSION['cart']);
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

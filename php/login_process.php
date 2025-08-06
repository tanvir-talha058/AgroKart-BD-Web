<?php
// FILE: /php/login_process.php
require_once '../includes/db_connect.php';
$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    if (empty($email) || empty($password)) {
        $error = "Email and password are required.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, password, role, profile_image_path, division, district, city FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $name, $hashed_password, $role, $profile_image_path, $division, $district, $city);
            $stmt->fetch();
            if (password_verify($password, $hashed_password)) {
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = $id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_role'] = $role;
                $_SESSION['profile_image_path'] = $profile_image_path;
                $_SESSION['user_location'] = implode(', ', array_filter([$city, $district, $division]));

                // Determine if the current session has any items in the cart before login
                $has_guest_cart_items = isset($_SESSION['cart']) && count($_SESSION['cart']) > 0;

                // If there are items in the guest cart, save them to the user's database cart
                if ($has_guest_cart_items) {
                    foreach ($_SESSION['cart'] as $product_id => $item) {
                        // Check if the product is already in the user's cart in the database
                        $check_stmt = $conn->prepare("SELECT quantity FROM user_cart WHERE user_id = ? AND product_id = ?");
                        $check_stmt->bind_param("ii", $id, $product_id);
                        $check_stmt->execute();
                        $check_result = $check_stmt->get_result();

                        if ($check_result->num_rows > 0) {
                            // Update existing cart item
                            $cart_item = $check_result->fetch_assoc();
                            $new_quantity = $cart_item['quantity'] + $item['quantity'];

                            // Ensure quantity doesn't exceed stock
                            if ($new_quantity > $item['stock']) {
                                $new_quantity = $item['stock'];
                            }

                            $update_stmt = $conn->prepare("UPDATE user_cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
                            $update_stmt->bind_param("iii", $new_quantity, $id, $product_id);
                            $update_stmt->execute();
                            $update_stmt->close();
                        } else {
                            // Add new cart item
                            $insert_stmt = $conn->prepare("INSERT INTO user_cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                            $insert_stmt->bind_param("iii", $id, $product_id, $item['quantity']);
                            $insert_stmt->execute();
                            $insert_stmt->close();
                        }
                        $check_stmt->close();
                    }
                }

                // Clear the current session cart to prepare for loading the user's cart
                $_SESSION['cart'] = [];

                // The header.php will handle loading the cart from the database
                $_SESSION['cart_loaded'] = false;  // Force reload of cart from database

                if ($role == 'Seller') {
                    header("Location: ../dashboard.php");
                } else {
                    header("Location: ../index.php");
                }
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "No account found with that email.";
        }
        $stmt->close();
    }
}
if (!empty($error)) {
    $_SESSION['error'] = $error;
    header("Location: ../login.php");
    exit();
}
$conn->close();

<?php
// FILE: /php/login_process.php
require_once '../includes/db_connect.php';
$error = '';

// Ensure user_cart table exists and has a price column (mirrors runtime safeguard in cart_manager)
function ensure_user_cart_table($conn)
{
    $sql = "CREATE TABLE IF NOT EXISTS user_cart (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_user_product (user_id, product_id),
        INDEX idx_user (user_id),
        INDEX idx_product (product_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    @$conn->query($sql);

    $colCheck = @$conn->query("SHOW COLUMNS FROM user_cart LIKE 'price'");
    if ($colCheck && $colCheck->num_rows === 0) {
        @$conn->query("ALTER TABLE user_cart ADD COLUMN price DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER quantity");
    }
}
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
                    // Ensure DB table is present before merge
                    ensure_user_cart_table($conn);

                    foreach ($_SESSION['cart'] as $product_id => $item) {
                        // Check if the product is already in the user's cart in the database
                        $check_stmt = $conn->prepare("SELECT quantity, price FROM user_cart WHERE user_id = ? AND product_id = ?");
                        $check_stmt->bind_param("ii", $id, $product_id);
                        $check_stmt->execute();
                        $check_result = $check_stmt->get_result();

                        if ($check_result->num_rows > 0) {
                            // Update existing cart item
                            $cart_item = $check_result->fetch_assoc();
                            $new_quantity = (int)$cart_item['quantity'] + (int)$item['quantity'];

                            // Ensure quantity doesn't exceed stock
                            if ($new_quantity > $item['stock']) {
                                $new_quantity = (int)$item['stock'];
                            }

                            // Preserve the best (lowest) price between existing and session item price
                            $session_price = isset($item['price']) ? (float)$item['price'] : 0.0;
                            $existing_price = isset($cart_item['price']) ? (float)$cart_item['price'] : 0.0;
                            $new_price = $existing_price > 0 && $session_price > 0 ? min($existing_price, $session_price) : max($existing_price, $session_price);

                            $update_stmt = $conn->prepare("UPDATE user_cart SET quantity = ?, price = ? WHERE user_id = ? AND product_id = ?");
                            $update_stmt->bind_param("idii", $new_quantity, $new_price, $id, $product_id);
                            $update_stmt->execute();
                            $update_stmt->close();
                        } else {
                            // Add new cart item
                            $session_price = isset($item['price']) ? (float)$item['price'] : 0.0;
                            $insert_stmt = $conn->prepare("INSERT INTO user_cart (user_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                            $insert_stmt->bind_param("iiid", $id, $product_id, $item['quantity'], $session_price);
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

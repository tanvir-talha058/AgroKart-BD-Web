<?php
// FILE: /php/review_process.php
require_once '../includes/db_connect.php';

if (!isset($_SESSION['loggedin'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'];
    $rating = $_POST['rating'];
    $comment = trim($_POST['comment']);

    if (empty($comment) || empty($rating) || $rating < 1 || $rating > 5) {
        $_SESSION['error'] = "Please provide a valid rating and comment.";
    } else {
        // Check if reviews table exists, if not create it
        $check_table = $conn->query("SHOW TABLES LIKE 'reviews'");
        if ($check_table->num_rows == 0) {
            // Create reviews table
            $conn->query("
                CREATE TABLE `reviews` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `product_id` int(11) NOT NULL,
                  `user_id` int(11) NOT NULL,
                  `rating` int(1) NOT NULL,
                  `comment` text NOT NULL,
                  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                  PRIMARY KEY (`id`),
                  KEY `product_id` (`product_id`),
                  KEY `user_id` (`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
            ");
        }

        // Check if user has already reviewed this product
        $stmt_check = $conn->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ?");
        $stmt_check->bind_param("ii", $user_id, $product_id);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $_SESSION['error'] = "You have already reviewed this product.";
        } else {
            $stmt = $conn->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiis", $product_id, $user_id, $rating, $comment);
            if ($stmt->execute()) {
                $_SESSION['message'] = "Thank you for your review!";
            } else {
                $_SESSION['error'] = "Could not submit review. Please try again.";
            }
            $stmt->close();
        }
        $stmt_check->close();
    }
    header("Location: ../product_details.php?id=" . $product_id);
    exit();
}

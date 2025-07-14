<?php
// FILE: /php/add_product_process.php
require_once '../includes/db_connect.php';
if (!isset($_SESSION['loggedin']) || $_SESSION['user_role'] !== 'Seller') {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $seller_id = $_SESSION['user_id'];
    $product_name = trim($_POST['product_name']);
    $category = trim($_POST['category']);
    $price = trim($_POST['price']);
    $stock = trim($_POST['stock']);
    $description = trim($_POST['description']);

    // Image Upload Handling
    $target_dir = "../images/uploads/";
    $image_name = basename($_FILES["product_image"]["name"]);
    $target_file = $target_dir . $image_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["product_image"]["tmp_name"]);
    if($check === false) {
        $_SESSION['error'] = "File is not an image.";
        header("Location: ../dashboard.php");
        exit();
    }

    // Check if file already exists, if so, rename
    $i = 1;
    $original_name = pathinfo($image_name, PATHINFO_FILENAME);
    while (file_exists($target_file)) {
        $new_name = $original_name . "($i)." . $imageFileType;
        $target_file = $target_dir . $new_name;
        $i++;
    }
    
    if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
        $image_path = "images/uploads/" . basename($target_file);

        $stmt = $conn->prepare("INSERT INTO products (seller_id, name, category, price, stock, description, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssiss", $seller_id, $product_name, $category, $price, $stock, $description, $image_path);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Product added successfully!";
        } else {
            $_SESSION['error'] = "Error adding product: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "Sorry, there was an error uploading your file.";
    }
    header("Location: ../dashboard.php");
    exit();
}
?>
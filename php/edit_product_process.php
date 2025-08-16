<?php
// php/edit_product_process.php
require_once '../includes/db_connect.php';
if (!isset($_SESSION['loggedin']) || $_SESSION['user_role'] !== 'Seller') {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $seller_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'];
    $product_name = trim($_POST['product_name']);
    $category = trim($_POST['category']);
    $price = trim($_POST['price']);
    $unit = trim($_POST['unit']);
    $stock = trim($_POST['stock']);
    $description = trim($_POST['description']);

    // First verify that this product belongs to the seller
    $check_sql = "SELECT * FROM products WHERE id = ? AND seller_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $product_id, $seller_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['error'] = "You don't have permission to edit this product.";
        header("Location: ../products.php");
        exit();
    }

    $product = $result->fetch_assoc();
    $current_image_path = $product['image_path'];
    $check_stmt->close();

    // Check if a new image is uploaded
    $image_path = $current_image_path; // Default to current image
    if (isset($_FILES["product_image"]) && $_FILES["product_image"]["error"] !== UPLOAD_ERR_NO_FILE) {
        // Image Upload Handling
        $target_dir = "../images/uploads/";

        // Check if uploads directory exists, if not create it
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        // Check if file was uploaded successfully
        if ($_FILES["product_image"]["error"] !== UPLOAD_ERR_OK) {
            $upload_errors = array(
                UPLOAD_ERR_INI_SIZE => "The uploaded file exceeds the upload_max_filesize directive in php.ini",
                UPLOAD_ERR_FORM_SIZE => "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
                UPLOAD_ERR_PARTIAL => "The uploaded file was only partially uploaded",
                UPLOAD_ERR_NO_FILE => "No file was uploaded",
                UPLOAD_ERR_NO_TMP_DIR => "Missing a temporary folder",
                UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk",
                UPLOAD_ERR_EXTENSION => "A PHP extension stopped the file upload"
            );

            $error_message = isset($_FILES["product_image"]["error"]) ?
                ($upload_errors[$_FILES["product_image"]["error"]] ?? "Unknown upload error") :
                "No file was uploaded";

            $_SESSION['error'] = $error_message;
            header("Location: ../products.php");
            exit();
        }

        $image_name = basename($_FILES["product_image"]["name"]);
        $target_file = $target_dir . $image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["product_image"]["tmp_name"]);
        if ($check === false) {
            $_SESSION['error'] = "File is not an image.";
            header("Location: ../products.php");
            exit();
        }

        // Check file size (limit to 5MB)
        if ($_FILES["product_image"]["size"] > 5000000) {
            $_SESSION['error'] = "Sorry, your file is too large. Maximum size is 5MB.";
            header("Location: ../products.php");
            exit();
        }

        // Allow certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            $_SESSION['error'] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            header("Location: ../products.php");
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

            // Delete old image if it exists and is different from the default
            if ($current_image_path && file_exists("../" . $current_image_path)) {
                unlink("../" . $current_image_path);
            }
        } else {
            $_SESSION['error'] = "Sorry, there was an error uploading your file. Please try again.";
            header("Location: ../products.php");
            exit();
        }
    }

    // Get quantity from POST data and create display_unit
    $quantity = isset($_POST['quantity']) ? $_POST['quantity'] : 1;
    $display_unit = $quantity . ' ' . $unit;

    // Update the product
    $stmt = $conn->prepare("UPDATE products SET name = ?, category = ?, price = ?, unit = ?, quantity = ?, display_unit = ?, stock = ?, description = ?, image_path = ? WHERE id = ? AND seller_id = ?");
    $stmt->bind_param("ssssssissii", $product_name, $category, $price, $unit, $quantity, $display_unit, $stock, $description, $image_path, $product_id, $seller_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Product updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating product: " . $stmt->error;
    }
    $stmt->close();

    header("Location: ../products.php");
    exit();
}

// If not a POST request, redirect back to products page
header("Location: ../products.php");
exit();

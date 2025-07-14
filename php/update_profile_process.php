<?php
// FILE: /php/update_profile_process.php
require_once '../includes/db_connect.php';

if (!isset($_SESSION['loggedin'])) {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Update basic info
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);

    $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
    $stmt->bind_param("ssi", $name, $phone, $user_id);
    $stmt->execute();
    $stmt->close();
    $_SESSION['user_name'] = $name; // Update session

    // Handle profile photo upload
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
        $target_dir = "../images/profiles/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0755, true); }

        $image_name = "user_" . $user_id . "_" . time() . "." . strtolower(pathinfo($_FILES["profile_photo"]["name"], PATHINFO_EXTENSION));
        $target_file = $target_dir . $image_name;
        
        if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_file)) {
            $image_path = "images/profiles/" . $image_name;
            $stmt_img = $conn->prepare("UPDATE users SET profile_image_path = ? WHERE id = ?");
            $stmt_img->bind_param("si", $image_path, $user_id);
            $stmt_img->execute();
            $stmt_img->close();
            $_SESSION['profile_image_path'] = $image_path; // Update session
        }
    }

    // Handle password change
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];

    if (!empty($current_password) && !empty($new_password) && !empty($confirm_new_password)) {
        if ($new_password !== $confirm_new_password) {
            $_SESSION['error'] = "New passwords do not match.";
        } else {
            $stmt_pass = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt_pass->bind_param("i", $user_id);
            $stmt_pass->execute();
            $result = $stmt_pass->get_result()->fetch_assoc();
            $stmt_pass->close();

            if (password_verify($current_password, $result['password'])) {
                $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt_update_pass = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt_update_pass->bind_param("si", $hashed_new_password, $user_id);
                $stmt_update_pass->execute();
                $stmt_update_pass->close();
                $_SESSION['message'] = "Profile and password updated successfully!";
                header("Location: ../profile.php");
                exit();
            } else {
                $_SESSION['error'] = "Incorrect current password.";
            }
        }
    } else {
        $_SESSION['message'] = "Profile updated successfully!";
    }
}

header("Location: ../profile.php");
exit();
?>

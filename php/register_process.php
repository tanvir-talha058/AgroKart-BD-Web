<?php
// FILE: /php/register_process.php
require_once '../includes/db_connect.php';
$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $division = trim($_POST['division']);
    $district = trim($_POST['district']);
    $city = trim($_POST['city']);
    $role = $_POST['role'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $profile_image_path = null;

    // --- Server-side Validation ---
    if (strlen($name) < 6) { $error = "Name must be at least 6 characters."; }
    // ... (other validations remain the same) ...
    elseif ($password !== $confirm_password) { $error = "Passwords do not match."; }
    else {
        // Handle profile photo upload
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
            $target_dir = "../images/profiles/";
            if (!is_dir($target_dir)) { mkdir($target_dir, 0755, true); }
            $image_name = "user_" . time() . "_" . basename($_FILES["profile_photo"]["name"]);
            $target_file = $target_dir . $image_name;
            if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_file)) {
                $profile_image_path = "images/profiles/" . $image_name;
            }
        }

        $stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $stmt_check->store_result();
        if ($stmt_check->num_rows > 0) {
            $error = "An account with this email already exists.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, phone, division, district, city, role, profile_image_path, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssss", $name, $email, $phone, $division, $district, $city, $role, $profile_image_path, $hashed_password);
            if ($stmt->execute()) {
                $_SESSION['message'] = "Registration successful! Please login.";
                header("Location: ../login.php");
                exit();
            } else { $error = "Error: " . $stmt->error; }
            $stmt->close();
        }
        $stmt_check->close();
    }
}
if (!empty($error)) { $_SESSION['error'] = $error; header("Location: ../registration.php"); exit(); }
$conn->close();
?>

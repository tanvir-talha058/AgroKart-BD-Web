<?php
// FILE: /php/login_process.php
require_once '../includes/db_connect.php';
$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    if (empty($email) || empty($password)) { $error = "Email and password are required."; }
    else {
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

                if ($role == 'Seller') { header("Location: ../dashboard.php"); }
                else { header("Location: ../index.php"); }
                exit();
            } else { $error = "Invalid password."; }
        } else { $error = "No account found with that email."; }
        $stmt->close();
    }
}
if (!empty($error)) { $_SESSION['error'] = $error; header("Location: ../login.php"); exit(); }
$conn->close();
?>

<?php
// FILE: /php/google_mock_login.php

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/db_connect.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    header('Location: ../login.php');
    exit;
}

// Validate input
$google_email = filter_var(trim($_POST['google_email']), FILTER_VALIDATE_EMAIL);
$google_name = trim($_POST['google_name']);

if (!$google_email || !$google_name) {
    $_SESSION['error'] = 'Please provide valid email and name';
    header('Location: ../login.php');
    exit;
}

// Ensure it's a Gmail address
if (!str_ends_with(strtolower($google_email), '@gmail.com')) {
    $_SESSION['error'] = 'Please use a Gmail address for Google sign-in';
    header('Location: ../login.php');
    exit;
}

// Check if user already exists
$stmt = $conn->prepare("SELECT id, name, role, profile_image_path, division, district, city FROM users WHERE email = ?");
$stmt->bind_param("s", $google_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // User exists, log them in
    $user = $result->fetch_assoc();

    // Only allow buyers for Google login
    if ($user['role'] !== 'Buyer') {
        $_SESSION['error'] = 'Google sign-in is only available for buyer accounts. Please use regular login for seller accounts.';
        header('Location: ../login.php');
        exit;
    }

    $_SESSION['loggedin'] = true;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['profile_image_path'] = $user['profile_image_path'];
    $_SESSION['user_location'] = implode(', ', array_filter([$user['city'], $user['district'], $user['division']]));

    $_SESSION['message'] = 'Welcome back! Successfully signed in with Google.';
} else {
    // Create new buyer account
    $default_password = password_hash('google_user_' . uniqid(), PASSWORD_DEFAULT);

    $insert_stmt = $conn->prepare("INSERT INTO users (name, email, password, role, division, district, city) VALUES (?, ?, ?, 'Buyer', '', '', '')");
    $insert_stmt->bind_param("sss", $google_name, $google_email, $default_password);

    if ($insert_stmt->execute()) {
        $new_user_id = $conn->insert_id;

        // Log in the new user
        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = $new_user_id;
        $_SESSION['user_name'] = $google_name;
        $_SESSION['user_role'] = 'Buyer';
        $_SESSION['profile_image_path'] = '';
        $_SESSION['user_location'] = '';

        $_SESSION['message'] = 'Welcome to AgroKart BD! Your Google account has been registered as a buyer.';
    } else {
        $_SESSION['error'] = 'Failed to create user account. Please try again.';
        header("Location: ../login.php");
        exit;
    }

    $insert_stmt->close();
}

$stmt->close();
$conn->close();

// Always redirect to buyer interface (index.php)
header("Location: ../index.php");
exit;

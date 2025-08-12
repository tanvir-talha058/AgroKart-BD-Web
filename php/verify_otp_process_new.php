<?php
// FILE: php/verify_otp_process_new.php

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once '../includes/db_connect.php';

// Set default timezone to ensure consistency
date_default_timezone_set('UTC');

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if we have the email in session
    if (!isset($_SESSION['forgot_email'])) {
        $_SESSION['error'] = "Your session has expired. Please start the password reset process again.";
        header("Location: ../forgot_password.php");
        exit;
    }

    // Get the email from the session
    $email = $_SESSION['forgot_email'];

    // Get the OTP and new password from the form
    $otp = isset($_POST['otp']) ? trim($_POST['otp']) : '';
    $new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';

    // Debug OTP processing
    error_log("OTP received: $otp");

    // Validate inputs
    if (!$otp || strlen($otp) !== 6 || !is_numeric($otp)) {
        $_SESSION['error'] = "Please enter a valid 6-digit verification code.";
        header("Location: ../forgot_password.php");
        exit;
    }

    if (!$new_password || strlen($new_password) < 8) {
        $_SESSION['error'] = "Please enter a password with at least 8 characters.";
        header("Location: ../forgot_password.php");
        exit;
    }

    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "The passwords do not match. Please try again.";
        header("Location: ../forgot_password.php");
        exit;
    }

    // Get user ID from email
    $user_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $user_stmt->bind_param("s", $email);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();

    if ($user_result->num_rows === 0) {
        $_SESSION['error'] = "User not found. Please try again.";
        header("Location: ../forgot_password.php");
        exit;
    }

    $user = $user_result->fetch_assoc();
    $user_id = $user['id'];

    // Get the token from database
    $token_stmt = $conn->prepare("SELECT * FROM password_reset_tokens WHERE user_id = ?");
    $token_stmt->bind_param("i", $user_id);
    $token_stmt->execute();
    $token_result = $token_stmt->get_result();

    if ($token_result->num_rows === 0) {
        $_SESSION['error'] = "No active password reset request found. Please start the process again.";
        header("Location: ../forgot_password.php");
        exit;
    }

    $token_data = $token_result->fetch_assoc();
    $stored_token = $token_data['token'];
    $expiry_time = $token_data['expires_at'];

    // Current time
    $current_time = date('Y-m-d H:i:s');

    // Check if token is correct
    if ($stored_token != $otp) {
        error_log("Token mismatch - Stored: $stored_token, Provided: $otp");
        $_SESSION['error'] = "Invalid verification code. Please check and try again.";
        header("Location: ../forgot_password.php");
        exit;
    }

    // Check if token is expired
    if ($current_time > $expiry_time) {
        error_log("Token expired - Expiry: $expiry_time, Current: $current_time");
        $_SESSION['error'] = "Your verification code has expired. Please request a new code.";
        header("Location: ../forgot_password.php");
        exit;
    }

    // OTP is valid, now update the password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    $update_password_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $update_password_stmt->bind_param("si", $hashed_password, $user_id);
    $update_password_stmt->execute();

    // Delete the used token
    $delete_token_stmt = $conn->prepare("DELETE FROM password_reset_tokens WHERE user_id = ?");
    $delete_token_stmt->bind_param("i", $user_id);
    $delete_token_stmt->execute();

    // Clear the session variable
    unset($_SESSION['forgot_email']);

    // Set success message and redirect to login page
    $_SESSION['message'] = "Your password has been successfully reset. You can now log in with your new password.";
    header("Location: ../login.php");
    exit;
} else {
    // If not a POST request, redirect to the forgot password page
    header("Location: ../forgot_password.php");
    exit;
}

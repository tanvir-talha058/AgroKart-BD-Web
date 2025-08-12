<?php
// FILE: php/verify_otp_process.php

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
    $otp = filter_input(INPUT_POST, 'otp', FILTER_SANITIZE_NUMBER_INT);
    $new_password = filter_input(INPUT_POST, 'new_password', FILTER_SANITIZE_STRING);
    $confirm_password = filter_input(INPUT_POST, 'confirm_password', FILTER_SANITIZE_STRING);

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

    // Log the values for debugging
    error_log("User ID: $user_id, OTP: $otp");

    // First, get the token regardless of expiry to check if it exists
    $token_check_stmt = $conn->prepare("SELECT * FROM password_reset_tokens WHERE user_id = ?");
    $token_check_stmt->bind_param("i", $user_id);
    $token_check_stmt->execute();
    $token_check_result = $token_check_stmt->get_result();

    if ($token_check_result->num_rows > 0) {
        $token_data = $token_check_result->fetch_assoc();
        error_log("Stored token: " . $token_data['token'] . ", Expires: " . $token_data['expires_at']);

        // Try different comparison approaches
        $stored_token = $token_data['token'];
        $is_equal_exact = ($stored_token === $otp);
        $is_equal_loose = ($stored_token == $otp);
        $is_equal_numeric = (intval($stored_token) === intval($otp));

        error_log("Comparison results - Exact: " . ($is_equal_exact ? "Yes" : "No") .
            ", Loose: " . ($is_equal_loose ? "Yes" : "No") .
            ", Numeric: " . ($is_equal_numeric ? "Yes" : "No"));

        // Check expiration
        $current_time = date('Y-m-d H:i:s');
        $is_expired = ($current_time > $token_data['expires_at']);
        error_log("Current time: $current_time, Expires: " . $token_data['expires_at'] . ", Expired: " . ($is_expired ? "Yes" : "No"));
    }

    // Current time in UTC
    $current_time = date('Y-m-d H:i:s');
    
    // Check if the OTP is valid and not expired - use direct string comparison for token and manual time check
    // First get any token for this user regardless of value
    $token_check = $conn->prepare("SELECT * FROM password_reset_tokens WHERE user_id = ?");
    $token_check->bind_param("i", $user_id);
    $token_check->execute();
    $check_result = $token_check->get_result();
    
    if ($check_result->num_rows === 0) {
        // No token exists for this user at all
        error_log("No token found for user $user_id");
        $_SESSION['error'] = "No active password reset request found. Please start the process again.";
        header("Location: ../forgot_password.php");
        exit;
    }
    
    // Now check if the entered token matches
    $token_data = $check_result->fetch_assoc();
    $stored_token = $token_data['token'];
    $expiry_time = $token_data['expires_at'];
    
    error_log("User $user_id - Stored token: $stored_token, Entered: $otp, Expires: $expiry_time, Current: $current_time");
    
    // Check if token matches
    if ($stored_token != $otp) {
        error_log("Token mismatch for user $user_id");
        $_SESSION['error'] = "Invalid verification code. Please check and try again.";
        header("Location: ../forgot_password.php");
        exit;
    }
    
    // Check if token is expired
    if ($current_time > $expiry_time) {
        error_log("Token expired for user $user_id. Expires: $expiry_time, Current: $current_time");
        $_SESSION['error'] = "Your verification code has expired. Please request a new code.";
        header("Location: ../forgot_password.php");
        exit;
    }
    
    // If we got here, token is valid and not expired
    error_log("Valid token for user $user_id");

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

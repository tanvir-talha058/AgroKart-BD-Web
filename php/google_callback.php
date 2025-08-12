<?php
// FILE: /php/google_callback.php

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/db_connect.php';

// This file handles the callback from Google OAuth
// For a complete implementation, you would:
// 1. Exchange the authorization code for tokens
// 2. Fetch the user's Google profile information
// 3. Create or update the user in your database
// 4. Log the user in

// For demonstration purposes, we'll create a placeholder implementation
// In a real application, you would use the Google API PHP Client

// Check if there's an error in the callback
if (isset($_GET['error'])) {
    $_SESSION['error'] = 'Google authentication failed: ' . $_GET['error'];
    header('Location: ../login.php');
    exit;
}

// Check for the authorization code
if (!isset($_GET['code'])) {
    $_SESSION['error'] = 'No authorization code received from Google';
    header('Location: ../login.php');
    exit;
}

// In a real implementation, you would exchange the code for tokens
// and fetch the user profile with the Google API PHP Client

// For demonstration, we'll simulate a successful login
// In a real implementation, you would:
// 1. Verify the Google ID token
// 2. Check if the user exists in your database
// 3. Create a new user if they don't exist
// 4. Log them in

// Simulated user data (in a real app, this would come from Google)
$google_email = 'demo@example.com'; // This would come from Google

// Check if user exists in database
$stmt = $conn->prepare("SELECT id, name, role, profile_image_path, division, district, city FROM users WHERE email = ?");
$stmt->bind_param("s", $google_email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // User exists, log them in
    $stmt->bind_result($id, $name, $role, $profile_image_path, $division, $district, $city);
    $stmt->fetch();
    
    $_SESSION['loggedin'] = true;
    $_SESSION['user_id'] = $id;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_role'] = $role;
    $_SESSION['profile_image_path'] = $profile_image_path;
    $_SESSION['user_location'] = implode(', ', array_filter([$city, $district, $division]));
    
    $_SESSION['message'] = 'Successfully logged in with Google!';
    
    if ($role == 'Seller') {
        header("Location: ../dashboard.php");
    } else {
        header("Location: ../index.php");
    }
} else {
    // In a real implementation, you would create a new user
    // For this demo, we'll just show an error
    $_SESSION['error'] = 'No account found with that Google email. Please register first.';
    header("Location: ../login.php");
}

$stmt->close();
$conn->close();
exit();
?>
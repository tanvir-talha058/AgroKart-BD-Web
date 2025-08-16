<?php
// FILE: php/google_login.php - Real Google OAuth Login Initiation

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Google OAuth Configuration
$client_id = '225851143467-q7o34s8abcia2kn9o4at3il1s6khuege.apps.googleusercontent.com'; // Replace with your actual Client ID
$redirect_uri = 'http://localhost:3000/php/google_callback.php'; // Fixed with port 3000
$scope = 'openid email profile';

// Build Google OAuth URL
$auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id' => $client_id,
    'redirect_uri' => $redirect_uri,
    'response_type' => 'code',
    'scope' => $scope,
    'access_type' => 'online',
    'prompt' => 'select_account', // Always show account selection
    'state' => bin2hex(random_bytes(16)) // CSRF protection
]);

// Redirect to Google OAuth
header('Location: ' . $auth_url);
exit;

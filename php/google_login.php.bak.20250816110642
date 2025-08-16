<?php
// FILE: php/google_login.php - Real Google OAuth Login Initiation

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Load OAuth configuration securely
$oauth_config = require_once '../config/oauth_config_local.php';
$google_config = $oauth_config['google'];

// Google OAuth Configuration
$client_id = $google_config['client_id'];
$redirect_uri = $google_config['redirect_uri'];
$scope = 'openid email profile';

// Check if credentials are loaded correctly
if (empty($client_id) || empty($redirect_uri)) {
    error_log("Google OAuth configuration error: Missing client_id or redirect_uri");
    $_SESSION['error'] = 'Google login is not properly configured. Please contact the administrator.';
    header('Location: ../login.php');
    exit;
}

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

// Log the authorization URL for debugging (without the client_id for security)
$debug_url = preg_replace('/client_id=[^&]+/', 'client_id=REMOVED_FOR_SECURITY', $auth_url);
error_log("Google OAuth authorization URL: " . $debug_url);

// Redirect to Google OAuth
header('Location: ' . $auth_url);
exit;

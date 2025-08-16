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

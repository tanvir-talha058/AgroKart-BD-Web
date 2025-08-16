<?php
// FILE: php/debug_google_oauth.php
// Use this file to debug Google OAuth configuration

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// HTML header
header('Content-Type: text/html; charset=utf-8');
echo "<!DOCTYPE html>
<html>
<head>
    <title>Google OAuth Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .container { max-width: 800px; margin: 0 auto; }
        .section { margin-bottom: 30px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        h1 { color: #2c3e50; }
        h2 { color: #3498db; }
        pre { background-color: #f8f9fa; padding: 15px; overflow: auto; border-radius: 5px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Google OAuth Debug</h1>";

// Load OAuth configuration securely
require_once '../includes/db_connect.php';
echo "<div class='section'><h2>Database Connection</h2>";
echo "Database connection status: ";
if ($conn && !$conn->connect_error) {
    echo "<span class='success'>Connected</span>";
} else {
    echo "<span class='error'>Failed</span>: " . ($conn ? $conn->connect_error : "Connection object not created");
}
echo "</div>";

// Check environment loading
echo "<div class='section'><h2>Environment Variables</h2>";
require_once '../includes/env_loader.php';
echo "<ul>";
echo "<li>GOOGLE_CLIENT_ID: " . (getenv('GOOGLE_CLIENT_ID') ? "<span class='success'>Set</span> (ends with: ..." . substr(getenv('GOOGLE_CLIENT_ID'), -8) . ")" : "<span class='error'>Not set</span>") . "</li>";
echo "<li>GOOGLE_CLIENT_SECRET: " . (getenv('GOOGLE_CLIENT_SECRET') ? "<span class='success'>Set</span> (length: " . strlen(getenv('GOOGLE_CLIENT_SECRET')) . " chars)" : "<span class='error'>Not set</span>") . "</li>";
echo "<li>GOOGLE_REDIRECT_URI: " . (getenv('GOOGLE_REDIRECT_URI') ? "<span class='success'>" . htmlspecialchars(getenv('GOOGLE_REDIRECT_URI')) . "</span>" : "<span class='error'>Not set</span>") . "</li>";
echo "</ul></div>";

// Check oauth config
echo "<div class='section'><h2>OAuth Configuration</h2>";
$oauth_config = require_once '../config/oauth_config_local.php';
echo "<ul>";
echo "<li>google.client_id: " . (isset($oauth_config['google']['client_id']) && $oauth_config['google']['client_id'] ? "<span class='success'>Set</span> (ends with: ..." . substr($oauth_config['google']['client_id'], -8) . ")" : "<span class='error'>Not set</span>") . "</li>";
echo "<li>google.client_secret: " . (isset($oauth_config['google']['client_secret']) && $oauth_config['google']['client_secret'] ? "<span class='success'>Set</span> (length: " . strlen($oauth_config['google']['client_secret']) . " chars)" : "<span class='error'>Not set</span>") . "</li>";
echo "<li>google.redirect_uri: " . (isset($oauth_config['google']['redirect_uri']) && $oauth_config['google']['redirect_uri'] ? "<span class='success'>" . htmlspecialchars($oauth_config['google']['redirect_uri']) . "</span>" : "<span class='error'>Not set</span>") . "</li>";
echo "</ul></div>";

// HTTP details
echo "<div class='section'><h2>HTTP Context</h2>";
echo "<ul>";
echo "<li>Current URL: <code>" . htmlspecialchars("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]") . "</code></li>";
echo "<li>HTTP Host: <code>" . htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'Not available') . "</code></li>";
echo "<li>Port: " . $_SERVER['SERVER_PORT'] . "</li>";
echo "<li>HTTP/HTTPS: " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'HTTPS' : 'HTTP') . "</li>";
echo "</ul></div>";

// Test OAuth URL
echo "<div class='section'><h2>Test OAuth URL</h2>";
$client_id = $oauth_config['google']['client_id'];
$redirect_uri = $oauth_config['google']['redirect_uri'];
$scope = 'openid email profile';

if (empty($client_id) || empty($redirect_uri)) {
    echo "<p class='error'>Missing configuration: client_id or redirect_uri not set</p>";
} else {
    $auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
        'client_id' => $client_id,
        'redirect_uri' => $redirect_uri,
        'response_type' => 'code',
        'scope' => $scope,
        'access_type' => 'online',
        'prompt' => 'select_account',
        'state' => bin2hex(random_bytes(16))
    ]);

    $debug_url = preg_replace('/client_id=[^&]+/', 'client_id=REMOVED_FOR_SECURITY', $auth_url);
    echo "<p>Generated URL: <br><code>" . htmlspecialchars($debug_url) . "</code></p>";
    echo "<p><a href='$auth_url' target='_blank' style='display: inline-block; padding: 10px 20px; background-color: #4285f4; color: white; text-decoration: none; border-radius: 4px;'>Test Google Login</a></p>";
}
echo "</div>";

// Check .env file
echo "<div class='section'><h2>.env File Check</h2>";
$env_file = __DIR__ . '/../.env';
if (file_exists($env_file)) {
    echo "<p class='success'>.env file exists</p>";
    $env_contents = file_get_contents($env_file);
    if (strpos($env_contents, 'GOOGLE_CLIENT_ID') !== false) {
        echo "<p class='success'>GOOGLE_CLIENT_ID found in .env</p>";
    } else {
        echo "<p class='error'>GOOGLE_CLIENT_ID not found in .env</p>";
    }

    if (strpos($env_contents, 'GOOGLE_CLIENT_SECRET') !== false) {
        echo "<p class='success'>GOOGLE_CLIENT_SECRET found in .env</p>";
    } else {
        echo "<p class='error'>GOOGLE_CLIENT_SECRET not found in .env</p>";
    }

    if (strpos($env_contents, 'GOOGLE_REDIRECT_URI') !== false) {
        echo "<p class='success'>GOOGLE_REDIRECT_URI found in .env</p>";
    } else {
        echo "<p class='error'>GOOGLE_REDIRECT_URI not found in .env</p>";
    }
} else {
    echo "<p class='error'>.env file does not exist</p>";
}
echo "</div>";

// Check database structure
echo "<div class='section'><h2>Database Structure Check</h2>";
if ($conn && !$conn->connect_error) {
    $check_query = "SHOW COLUMNS FROM users LIKE 'google_id'";
    $result = $conn->query($check_query);
    if ($result && $result->num_rows > 0) {
        echo "<p class='success'>google_id column exists in users table</p>";
    } else {
        echo "<p class='error'>google_id column does not exist in users table</p>";
    }
} else {
    echo "<p class='error'>Cannot check database structure - no connection</p>";
}
echo "</div>";

// End of document
echo "</div></body></html>";

<?php
// Test file to verify Google OAuth configuration
session_start();

echo "<h2>Google OAuth Configuration Test</h2>";

// Test the callback URL accessibility
echo "<h3>Testing Callback URL</h3>";
$callback_url = 'http://localhost/AgroKart-BD-Web/php/google_callback.php';
echo "Callback URL: <a href='$callback_url' target='_blank'>$callback_url</a><br>";

// Check if files exist
echo "<h3>File Existence Check</h3>";
$files_to_check = [
    '../php/google_login.php',
    '../php/google_callback.php'
];

foreach ($files_to_check as $file) {
    $exists = file_exists($file) ? '✓ EXISTS' : '✗ MISSING';
    $color = file_exists($file) ? 'green' : 'red';
    echo "<span style='color: $color;'>$exists</span> $file<br>";
}

// Test Google OAuth URL generation
echo "<h3>Google OAuth URL Test</h3>";
$client_id = '882382379211-4n465q20rjaurqfio3f56mo7u0l58am3.apps.googleusercontent.com';
$redirect_uri = 'http://localhost/AgroKart-BD-Web/php/google_callback.php';
$scope = 'openid email profile';

$auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id' => $client_id,
    'redirect_uri' => $redirect_uri,
    'response_type' => 'code',
    'scope' => $scope,
    'access_type' => 'online',
    'prompt' => 'select_account'
]);

echo "Generated OAuth URL:<br>";
echo "<a href='$auth_url' target='_blank' style='word-break: break-all;'>$auth_url</a><br><br>";

echo "<h3>Test Google Login</h3>";
echo "<a href='../php/google_login.php' class='btn'>Test Google Login</a>";

echo "<style>
.btn {
    display: inline-block;
    padding: 10px 20px;
    background-color: #4285f4;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    margin: 10px 0;
}
.btn:hover {
    background-color: #357ae8;
    color: white;
    text-decoration: none;
}
</style>";

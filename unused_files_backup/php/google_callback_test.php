<?php
// Simple test callback to check if the URL works
session_start();

echo "<h1>Google Callback Test</h1>";
echo "<p>This page is accessible!</p>";

if (isset($_GET['code'])) {
    echo "<p>Authorization code received: " . htmlspecialchars($_GET['code']) . "</p>";
} else {
    echo "<p>No authorization code received.</p>";
}

if (isset($_GET['error'])) {
    echo "<p>Error: " . htmlspecialchars($_GET['error']) . "</p>";
}

echo "<p><a href='../login.php'>Back to Login</a></p>";

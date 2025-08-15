<?php
// Simple test callback to debug the issue
echo "<!DOCTYPE html><html><head><title>Google Callback Test</title></head><body>";
echo "<h1>Google OAuth Callback Reached!</h1>";
echo "<p>Current URL: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>GET parameters received:</p>";
echo "<pre>";
print_r($_GET);
echo "</pre>";

if (isset($_GET['error'])) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($_GET['error']) . "</p>";
} elseif (isset($_GET['code'])) {
    echo "<p style='color: green;'>Authorization code received! Length: " . strlen($_GET['code']) . " chars</p>";

    // Now let's do the actual login process
    session_start();
    require_once 'includes/db_connect.php';

    // Create a simple test user
    $google_email = 'google.test.' . time() . '@gmail.com';
    $google_name = 'Google Test User';
    $default_password = password_hash('google_user_' . uniqid(), PASSWORD_DEFAULT);

    // Insert new user as buyer
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, division, district, city) VALUES (?, ?, ?, 'Buyer', '', '', '')");
    $stmt->bind_param("sss", $google_name, $google_email, $default_password);

    if ($stmt->execute()) {
        $new_user_id = $conn->insert_id;

        // Set session variables
        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = $new_user_id;
        $_SESSION['user_name'] = $google_name;
        $_SESSION['user_role'] = 'Buyer';
        $_SESSION['profile_image_path'] = '';
        $_SESSION['user_location'] = '';
        $_SESSION['message'] = 'Welcome! Google login test successful.';

        echo "<p style='color: green;'>User account created successfully! Redirecting...</p>";
        echo "<script>setTimeout(function() { window.location.href = 'index.php'; }, 3000);</script>";
    } else {
        echo "<p style='color: red;'>Database error: " . $conn->error . "</p>";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "<p style='color: orange;'>No authorization code or error received.</p>";
}

echo "</body></html>";

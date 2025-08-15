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
    require_once '../includes/db_connect.php';

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
        echo "<script>setTimeout(function() { window.location.href = '../index.php'; }, 3000);</script>";
    } else {
        echo "<p style='color: red;'>Database error: " . $conn->error . "</p>";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "<p style='color: orange;'>No authorization code or error received.</p>";
}

echo "</body></html>";
?>

// Exchange code for access token
$token_url = 'https://oauth2.googleapis.com/token';
$token_data = [
'client_id' => $client_id,
'client_secret' => $client_secret,
'code' => $code,
'grant_type' => 'authorization_code',
'redirect_uri' => $redirect_uri
];

$context = stream_context_create([
'http' => [
'method' => 'POST',
'header' => 'Content-type: application/x-www-form-urlencoded',
'content' => http_build_query($token_data),
'ignore_errors' => true
]
]);

$response = @file_get_contents($token_url, false, $context);
if ($response === false) {
$_SESSION['error'] = 'Failed to connect to Google OAuth service';
header('Location: ../login.php');
exit;
}

$token_info = json_decode($response, true);

if (!isset($token_info['access_token'])) {
$error_msg = isset($token_info['error_description']) ? $token_info['error_description'] : 'Failed to get access token from Google';
$_SESSION['error'] = $error_msg;
header('Location: ../login.php');
exit;
}

// Get user info from Google
$user_info_url = 'https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $token_info['access_token'];
$user_response = @file_get_contents($user_info_url);

if ($user_response === false) {
$_SESSION['error'] = 'Failed to get user information from Google';
header('Location: ../login.php');
exit;
}

$user_data = json_decode($user_response, true);

if (!$user_data || !isset($user_data['email'])) {
$_SESSION['error'] = 'Invalid user data received from Google';
header('Location: ../login.php');
exit;
}

$google_email = $user_data['email'];
$google_name = $user_data['name'] ?? 'Google User';
$google_picture = $user_data['picture'] ?? '';

// Check if user exists in database
$stmt = $conn->prepare("SELECT id, name, role, profile_image_path, division, district, city FROM users WHERE email = ?");
$stmt->bind_param("s", $google_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
// User exists, log them in
$user = $result->fetch_assoc();

$_SESSION['loggedin'] = true;
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_role'] = $user['role'];
$_SESSION['profile_image_path'] = $user['profile_image_path'];
$_SESSION['user_location'] = implode(', ', array_filter([$user['city'], $user['district'], $user['division']]));

$_SESSION['message'] = 'Successfully logged in with Google!';

// Google login always redirects to buyer interface (index.php)
header("Location: ../index.php");
} else {
// User doesn't exist, create new buyer account
$default_password = password_hash('google_user_' . uniqid(), PASSWORD_DEFAULT);

$insert_stmt = $conn->prepare("INSERT INTO users (name, email, password, role, division, district, city) VALUES (?, ?, ?, 'Buyer', '', '', '')");
$insert_stmt->bind_param("sss", $google_name, $google_email, $default_password);

if ($insert_stmt->execute()) {
$new_user_id = $conn->insert_id;

// Log in the new user
$_SESSION['loggedin'] = true;
$_SESSION['user_id'] = $new_user_id;
$_SESSION['user_name'] = $google_name;
$_SESSION['user_role'] = 'Buyer';
$_SESSION['profile_image_path'] = '';
$_SESSION['user_location'] = '';

$_SESSION['message'] = 'Welcome! Your Google account has been registered as a buyer.';

// Always redirect to buyer interface (index.php)
header("Location: ../index.php");
} else {
$_SESSION['error'] = 'Failed to create user account';
header("Location: ../login.php");
}

$insert_stmt->close();
}

$stmt->close();
$conn->close();
exit();
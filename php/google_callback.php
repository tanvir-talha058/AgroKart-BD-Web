<?php
// FILE: php/google_callback.php - Real Google OAuth Callback Handler

session_start();
require_once '../includes/db_connect.php';

// Google OAuth Configuration
$client_id = '225851143467-q7o34s8abcia2kn9o4at3il1s6khuege.apps.googleusercontent.com'; // Replace with your actual Client ID
$client_secret = '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'; // Replace with your actual Client Secret
$redirect_uri = 'http://localhost:3000/php/google_callback.php'; // Fixed with port 3000

// Check if we have an authorization code
if (!isset($_GET['code'])) {
    if (isset($_GET['error'])) {
        $_SESSION['error'] = 'Google login cancelled or failed: ' . $_GET['error'];
    } else {
        $_SESSION['error'] = 'Authorization code not received from Google';
    }
    header('Location: ../login.php');
    exit;
}

$code = $_GET['code'];

// Exchange authorization code for access token
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
    $_SESSION['error'] = 'Google authentication failed: ' . $error_msg;
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

$user_info = json_decode($user_response, true);

if (!isset($user_info['email'])) {
    $_SESSION['error'] = 'Unable to retrieve email from Google account';
    header('Location: ../login.php');
    exit;
}

// Extract user data from Google response
$google_id = $user_info['id'];
$email = $user_info['email'];
$name = $user_info['name'] ?? 'Google User';
$profile_picture = $user_info['picture'] ?? '';

try {
    // Check if user already exists
    $check_stmt = $conn->prepare("SELECT user_id, name, role, profile_image_path FROM users WHERE email = ? OR google_id = ?");
    $check_stmt->bind_param("ss", $email, $google_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        // Existing user - log them in
        $user = $result->fetch_assoc();

        // Update Google ID if not set
        if (empty($user['google_id'])) {
            $update_stmt = $conn->prepare("UPDATE users SET google_id = ? WHERE user_id = ?");
            $update_stmt->bind_param("si", $google_id, $user['user_id']);
            $update_stmt->execute();
            $update_stmt->close();
        }

        // Set session variables
        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['profile_image_path'] = $user['profile_image_path'];
        $_SESSION['user_location'] = '';
        $_SESSION['message'] = 'Welcome back, ' . $user['name'] . '!';
    } else {
        // New user - create account as Buyer
        $default_password = password_hash('google_oauth_' . $google_id, PASSWORD_DEFAULT);

        // Parse name into first and last name
        $name_parts = explode(' ', trim($name), 2);
        $first_name = $name_parts[0];
        $last_name = isset($name_parts[1]) ? $name_parts[1] : '';

        $insert_stmt = $conn->prepare("INSERT INTO users (first_name, last_name, name, email, password, role, google_id, created_at) VALUES (?, ?, ?, ?, ?, 'Buyer', ?, NOW())");
        $insert_stmt->bind_param("ssssss", $first_name, $last_name, $name, $email, $default_password, $google_id);

        if ($insert_stmt->execute()) {
            $new_user_id = $conn->insert_id;

            // Download and save profile picture if available
            $saved_image_path = '';
            if (!empty($profile_picture)) {
                $saved_image_path = saveGoogleProfileImage($profile_picture, $new_user_id);
            }

            // Update profile image path if we saved one
            if ($saved_image_path) {
                $img_update_stmt = $conn->prepare("UPDATE users SET profile_image_path = ? WHERE user_id = ?");
                $img_update_stmt->bind_param("si", $saved_image_path, $new_user_id);
                $img_update_stmt->execute();
                $img_update_stmt->close();
            }

            // Set session variables for new user
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $new_user_id;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_role'] = 'Buyer';
            $_SESSION['profile_image_path'] = $saved_image_path;
            $_SESSION['user_location'] = '';
            $_SESSION['message'] = 'Welcome to AgroKart BD, ' . $name . '! Your account has been created successfully.';
        } else {
            $_SESSION['error'] = 'Failed to create account. Please try again.';
            header('Location: ../login.php');
            exit;
        }

        $insert_stmt->close();
    }

    $check_stmt->close();

    // Redirect to homepage
    header('Location: ../index.php');
    exit;
} catch (Exception $e) {
    error_log("Google OAuth Error: " . $e->getMessage());
    $_SESSION['error'] = 'An error occurred during Google login. Please try again.';
    header('Location: ../login.php');
    exit;
}

// Function to save Google profile image
function saveGoogleProfileImage($image_url, $user_id)
{
    try {
        // Create directory if it doesn't exist
        $upload_dir = '../images/profiles/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Get image content
        $image_content = @file_get_contents($image_url);
        if ($image_content === false) {
            return '';
        }

        // Generate filename
        $file_extension = 'jpg'; // Google usually provides JPG
        $filename = 'user_' . $user_id . '_google_' . time() . '.' . $file_extension;
        $file_path = $upload_dir . $filename;

        // Save image
        if (file_put_contents($file_path, $image_content)) {
            return 'images/profiles/' . $filename;
        }
    } catch (Exception $e) {
        error_log("Profile image save error: " . $e->getMessage());
    }

    return '';
}

$conn->close();

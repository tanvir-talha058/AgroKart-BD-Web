<?php
// This is a fixed version that uses direct token lookup without complex conditions

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once 'includes/db_connect.php';

// Set default timezone to ensure consistency
date_default_timezone_set('UTC');

// If reset form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = isset($_SESSION['forgot_email']) ? $_SESSION['forgot_email'] : '';
    $otp = $_POST['otp'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Simple validation
    if (empty($email) || empty($otp) || empty($new_password) || $new_password !== $confirm_password) {
        echo "<p>Error: Please fill all fields correctly.</p>";
        exit;
    }

    // Get user ID
    $user_query = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $user_query->bind_param("s", $email);
    $user_query->execute();
    $user_result = $user_query->get_result();

    if ($user_result->num_rows === 0) {
        echo "<p>Error: User not found.</p>";
        exit;
    }

    $user = $user_result->fetch_assoc();
    $user_id = $user['id'];

    // Find any token for this user
    $token_query = $conn->prepare("SELECT * FROM password_reset_tokens WHERE user_id = ?");
    $token_query->bind_param("i", $user_id);
    $token_query->execute();
    $token_result = $token_query->get_result();

    if ($token_result->num_rows === 0) {
        echo "<p>Error: No token found for this user.</p>";
        exit;
    }

    // Check token validity
    $token_data = $token_result->fetch_assoc();
    $stored_token = $token_data['token'];
    $expires_at = $token_data['expires_at'];
    $now = date('Y-m-d H:i:s');

    echo "<p>Comparing tokens - Stored: '$stored_token', Entered: '$otp'</p>";
    echo "<p>Token expiry: $expires_at, Current time: $now</p>";

    if ($stored_token != $otp) {
        echo "<p>Error: Invalid token.</p>";
        exit;
    }

    if ($now > $expires_at) {
        echo "<p>Error: Token has expired.</p>";
        exit;
    }

    // All validation passed, token is valid
    echo "<p>Token is valid! Proceeding with password reset...</p>";

    // Update the password (commented out for testing)
    /*
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $update_stmt->bind_param("si", $hashed_password, $user_id);
    $update_stmt->execute();
    
    // Clean up the token
    $delete_stmt = $conn->prepare("DELETE FROM password_reset_tokens WHERE user_id = ?");
    $delete_stmt->bind_param("i", $user_id);
    $delete_stmt->execute();
    */

    echo "<p>Password would be reset successfully.</p>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test OTP Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        form {
            max-width: 400px;
            margin: 0 auto;
        }

        input {
            width: 100%;
            padding: 8px;
            margin: 5px 0 15px;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <h1>Test OTP Verification</h1>

    <?php if (isset($_SESSION['forgot_email'])): ?>
        <p>Email in session: <?php echo htmlspecialchars($_SESSION['forgot_email']); ?></p>
    <?php else: ?>
        <p>No email in session. Setting a test email.</p>
        <?php $_SESSION['forgot_email'] = 'test@example.com'; ?>
    <?php endif; ?>

    <form method="post">
        <div>
            <label for="otp">Verification Code:</label>
            <input type="text" id="otp" name="otp" required>
        </div>
        <div>
            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" required>
        </div>
        <div>
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        <button type="submit">Test Reset Password</button>
    </form>
</body>

</html>
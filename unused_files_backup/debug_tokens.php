<?php
session_start();
require_once 'includes/db_connect.php';

echo "Debugging Password Reset Tokens\n\n";

// Check the structure of the password_reset_tokens table
try {
    $tableStructureQuery = "SHOW CREATE TABLE password_reset_tokens";
    $result = $conn->query($tableStructureQuery);
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Table Structure:\n";
        echo $row['Create Table'] . "\n\n";
    } else {
        echo "Error getting table structure: " . $conn->error . "\n";
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

// Check the content of the password_reset_tokens table
try {
    $tokensQuery = "SELECT * FROM password_reset_tokens";
    $result = $conn->query($tokensQuery);
    if ($result) {
        echo "Current Tokens:\n";
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "ID: {$row['id']}, User ID: {$row['user_id']}, Token: {$row['token']}, Expires At: {$row['expires_at']}, Created At: {$row['created_at']}\n";
            }
        } else {
            echo "No tokens found in the table.\n";
        }
    } else {
        echo "Error getting tokens: " . $conn->error . "\n";
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

// Check if there's any recently sent token
if (isset($_SESSION['forgot_email'])) {
    echo "\nForgot Email in Session: {$_SESSION['forgot_email']}\n";

    // Get user ID
    $email = $_SESSION['forgot_email'];
    $userQuery = "SELECT id FROM users WHERE email = '$email'";
    $userResult = $conn->query($userQuery);
    if ($userResult && $userResult->num_rows > 0) {
        $user = $userResult->fetch_assoc();
        $userId = $user['id'];

        // Check for user's token
        $tokenQuery = "SELECT * FROM password_reset_tokens WHERE user_id = $userId";
        $tokenResult = $conn->query($tokenQuery);
        if ($tokenResult && $tokenResult->num_rows > 0) {
            $token = $tokenResult->fetch_assoc();
            echo "Found token for user: Token: {$token['token']}, Expires: {$token['expires_at']}\n";

            // Check if token is expired
            $now = new DateTime();
            $expires = new DateTime($token['expires_at']);
            echo "Current time: " . $now->format('Y-m-d H:i:s') . "\n";
            echo "Expiry time: " . $expires->format('Y-m-d H:i:s') . "\n";

            if ($now > $expires) {
                echo "Token is EXPIRED\n";
            } else {
                echo "Token is VALID\n";
                echo "Time remaining: " . $now->diff($expires)->format('%H hours, %I minutes, %S seconds') . "\n";
            }
        } else {
            echo "No token found for user with ID $userId\n";
        }
    } else {
        echo "User not found with email $email\n";
    }
} else {
    echo "\nNo forgot_email in session\n";
}

// Debug server time and timezone
echo "\nServer Info:\n";
echo "Current Server Time: " . date('Y-m-d H:i:s') . "\n";
echo "Timezone: " . date_default_timezone_get() . "\n";

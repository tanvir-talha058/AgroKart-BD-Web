<?php
echo "Current timezone: " . date_default_timezone_get() . "\n";
echo "Current time: " . date('Y-m-d H:i:s') . "\n";

// Let's check the current state in the database
require_once 'includes/db_connect.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$session_email = isset($_SESSION['forgot_email']) ? $_SESSION['forgot_email'] : 'None';
echo "Session email: $session_email\n\n";

// Check all tokens
$sql = "SELECT prt.*, u.email FROM password_reset_tokens prt JOIN users u ON prt.user_id = u.id";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $expires = $row['expires_at'];
        $now = date('Y-m-d H:i:s');
        $is_expired = $now > $expires;

        echo "User: {$row['email']}\n";
        echo "Token: {$row['token']}\n";
        echo "Expires: $expires\n";
        echo "Current time: $now\n";
        echo "Is expired: " . ($is_expired ? "Yes" : "No") . "\n";
        echo "Time diff: " . (strtotime($expires) - time()) . " seconds\n\n";
    }
} else {
    echo "No tokens found in database.\n";
}

// Now let's fix the expiry issue by updating all existing tokens
$extend_sql = "UPDATE password_reset_tokens SET expires_at = DATE_ADD(NOW(), INTERVAL 1 HOUR)";
$conn->query($extend_sql);
echo "All token expiry times have been extended by 1 hour from now.\n";

// Verify the update
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    echo "\nUpdated tokens:\n";
    while ($row = $result->fetch_assoc()) {
        echo "User: {$row['email']}, Token: {$row['token']}, New Expiry: {$row['expires_at']}\n";
    }
}

<?php
// Script to reset token expiry time

require_once 'includes/db_connect.php';

// Set default timezone to ensure consistency
date_default_timezone_set('UTC');

// Extend expiry of all tokens to 24 hours from now
$new_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
$update_query = "UPDATE password_reset_tokens SET expires_at = '$new_expiry'";

if ($conn->query($update_query)) {
    echo "All token expiry times have been extended to: $new_expiry";

    // Show all current tokens
    $tokens_query = "SELECT prt.*, u.email FROM password_reset_tokens prt 
                   JOIN users u ON prt.user_id = u.id";
    $result = $conn->query($tokens_query);

    if ($result->num_rows > 0) {
        echo "<h2>Current Tokens:</h2>";
        echo "<table border='1'>";
        echo "<tr><th>User ID</th><th>Email</th><th>Token</th><th>Expires At</th></tr>";

        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['user_id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . $row['token'] . "</td>";
            echo "<td>" . $row['expires_at'] . "</td>";
            echo "</tr>";
        }

        echo "</table>";
    } else {
        echo "<p>No tokens found in the database.</p>";
    }
} else {
    echo "Error updating tokens: " . $conn->error;
}

<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once 'includes/db_connect.php';

// Check if we have the email in session
if (isset($_SESSION['forgot_email'])) {
    $email = $_SESSION['forgot_email'];
    echo "<h2>Checking OTP for email: $email</h2>";

    // Get user ID from email
    $user_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $user_stmt->bind_param("s", $email);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();

    if ($user_result->num_rows === 0) {
        echo "<p>Error: User not found with this email.</p>";
    } else {
        $user = $user_result->fetch_assoc();
        $user_id = $user['id'];
        echo "<p>User ID: $user_id</p>";

        // Get OTP from form if submitted
        $otp = isset($_POST['otp']) ? $_POST['otp'] : '';

        if (!empty($otp)) {
            // Check the database directly
            $token_stmt = $conn->prepare("SELECT * FROM password_reset_tokens WHERE user_id = ?");
            $token_stmt->bind_param("i", $user_id);
            $token_stmt->execute();
            $token_result = $token_stmt->get_result();

            if ($token_result->num_rows === 0) {
                echo "<p>Error: No token found for this user.</p>";
            } else {
                $token_data = $token_result->fetch_assoc();
                $stored_otp = $token_data['token'];
                $expires_at = $token_data['expires_at'];

                echo "<p>Stored OTP in database: $stored_otp</p>";
                echo "<p>Submitted OTP: $otp</p>";
                echo "<p>Expires at: $expires_at</p>";

                // Check if token is expired
                $current_time = date('Y-m-d H:i:s');
                echo "<p>Current time: $current_time</p>";

                if ($stored_otp === $otp) {
                    echo "<p style='color:green'>OTP matches!</p>";
                } else {
                    echo "<p style='color:red'>OTP does not match.</p>";
                }

                if ($current_time > $expires_at) {
                    echo "<p style='color:red'>OTP is expired.</p>";
                } else {
                    echo "<p style='color:green'>OTP is still valid.</p>";
                }

                // Check complete SQL query
                $sql = "SELECT * FROM password_reset_tokens WHERE user_id = $user_id AND token = '$otp' AND expires_at > '$current_time'";
                $result = $conn->query($sql);
                echo "<p>Complete SQL check: " . ($result->num_rows > 0 ? "Valid" : "Invalid") . "</p>";
                echo "<p>SQL: $sql</p>";
            }
        }
    }
} else {
    echo "<p>No email in session. Please start the password reset process first.</p>";
}
?>

<form method="post" action="">
    <h3>Test OTP Verification</h3>
    <label for="otp">Enter OTP:</label>
    <input type="text" name="otp" id="otp" required>
    <button type="submit">Check OTP</button>
</form>

<link rel="stylesheet" href="css/otp-style.css">
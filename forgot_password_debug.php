<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Recovery Debug - AgroKart BD</title>
    <link rel="stylesheet" href="css/form-style.css">
    <style>
        .debug-info {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
            font-family: monospace;
            white-space: pre-wrap;
            max-height: 300px;
            overflow-y: auto;
        }
    </style>
</head>

<body>
    <div class="form-container">
        <div class="logo">
            <img src="images/AGrO.png" alt="AgroKart Logo">
        </div>
        <h1>Password Recovery Debug</h1>
        <p>Enter your email to receive a reset code</p>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; ?>
                <div class="debug-info">
                    <?php
                    echo "Error details: " . $_SESSION['error'];
                    echo "\nSession data: ";
                    print_r($_SESSION);

                    // Check if PHP can send emails
                    echo "\n\nPHP mail function enabled: " . (function_exists('mail') ? 'Yes' : 'No');

                    // Check if OpenSSL is enabled for SMTP over SSL
                    echo "\nOpenSSL enabled: " . (extension_loaded('openssl') ? 'Yes' : 'No');

                    // Check if SMTP connections are possible
                    $connection = @fsockopen('smtp.gmail.com', 587);
                    echo "\nCan connect to SMTP server: " . ($connection ? 'Yes' : 'No');
                    if ($connection) fclose($connection);

                    $connection = @fsockopen('smtp.gmail.com', 465);
                    echo "\nCan connect to SMTP server (SSL): " . ($connection ? 'Yes' : 'No');
                    if ($connection) fclose($connection);
                    ?>
                </div>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $_SESSION['message']; ?>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <?php if (!isset($_SESSION['forgot_email'])): ?>
            <!-- Step 1: Email entry form -->
            <form action="php/forgot_password_process.php" method="post">
                <div class="input-group">
                    <label for="email"><i class="fas fa-envelope"></i></label>
                    <input type="email" id="email" name="email" placeholder="Email address" required>
                    <button type="submit" class="btn-submit">
                        Send Reset Code <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </form>
        <?php else: ?>
            <!-- Step 2: OTP verification and password reset form -->
            <form action="php/verify_otp_process.php" method="post">
                <div class="input-group">
                    <label for="otp"><i class="fas fa-key"></i></label>
                    <input type="text" id="otp" name="otp" placeholder="6-digit verification code" required minlength="6" maxlength="6" pattern="\d{6}">
                </div>
                <div class="input-group">
                    <label for="new_password"><i class="fas fa-lock"></i></label>
                    <input type="password" id="new_password" name="new_password" placeholder="New password" required minlength="8">
                </div>
                <div class="input-group">
                    <label for="confirm_password"><i class="fas fa-lock"></i></label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required minlength="8">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        Reset Password <i class="fas fa-check"></i>
                    </button>
                    <div class="secondary-actions">
                        <a href="php/resend_otp.php">Resend Code</a> |
                        <a href="php/cancel_reset.php">Cancel</a>
                    </div>
                </div>
            </form>
        <?php endif; ?>

        <div class="form-footer">
            <p>Remembered your password? <a href="login.php">Back to Login</a></p>
        </div>
    </div>
    <script src="js/validation.js"></script>
</body>

</html>
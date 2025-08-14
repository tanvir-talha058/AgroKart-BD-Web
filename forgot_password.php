<?php
// FILE: forgot_password.php

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isset($_SESSION['loggedin'])) {
    header('Location: index.php');
    exit;
}

// Include header after checking for redirect
include 'includes/header.php';
?>
<div class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <img src="images/AGrO.png" alt="AgroKart BD Logo">
                </div>
                <h2>Password Recovery</h2>
                <p class="login-subtitle">Enter your email to receive a reset code</p>
            </div>

            <?php
            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ' . $_SESSION['error'] . '</div>';
                unset($_SESSION['error']);
            }
            if (isset($_SESSION['message'])) {
                echo '<div class="alert alert-success"><i class="fas fa-check-circle"></i> ' . $_SESSION['message'] . '</div>';
                unset($_SESSION['message']);
            }
            ?>

            <?php if (!isset($_SESSION['forgot_email'])): ?>
                <!-- Step 1: Email Form -->
                <form action="php/forgot_password_process.php" method="post" class="login-form">
                    <div class="form-group">
                        <div class="input-icon-wrapper">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" placeholder="Email address" required>
                        </div>
                    </div>

                    <button type="submit" class="login-button">
                        <span>Send Reset Code</span>
                        <i class="fas fa-paper-plane"></i>
                    </button>

                    <div class="login-divider"></div>

                    <p class="register-link">Remembered your password? <a href="login.php">Back to Login</a></p>
                </form>

            <?php else: ?>
                <!-- Step 2: OTP Verification Form -->
                <!-- Step 2: OTP verification and password reset form -->
                <form action="php/verify_otp_process_new.php" method="post" class="login-form">
                    <p class="verification-message">A verification code has been sent to <strong><?php echo htmlspecialchars($_SESSION['forgot_email']); ?></strong></p>

                    <div class="form-group">
                        <div class="input-icon-wrapper">
                            <i class="fas fa-key"></i>
                            <input type="text" id="otp" name="otp" placeholder="Enter 6-digit verification code" required pattern="\d{6}" inputmode="numeric" maxlength="6" minlength="6">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-icon-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="new_password" name="new_password" placeholder="New password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                                <i class="fas fa-eye" id="toggleIconNew"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-icon-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                <i class="fas fa-eye" id="toggleIconConfirm"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="login-button">
                        <span>Reset Password</span>
                        <i class="fas fa-check-circle"></i>
                    </button>

                    <div class="login-divider"></div>

                    <p class="resend-link">Didn't receive the code? <a href="php/resend_otp.php">Resend Code</a></p>
                    <p class="register-link"><a href="php/cancel_reset.php">Cancel Password Reset</a></p>
                </form>
            <?php endif; ?>

        </div>
    </div>

    <div class="login-decoration">
        <div class="floating-shape shape1"></div>
        <div class="floating-shape shape2"></div>
        <div class="floating-shape shape3"></div>
    </div>
</div>

<script>
    function togglePassword(fieldId) {
        const passwordInput = document.getElementById(fieldId);
        const toggleIcon = document.getElementById(fieldId === 'new_password' ? 'toggleIconNew' : 'toggleIconConfirm');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }
</script>

<link rel="stylesheet" href="css/forgot-password-style.css">

<?php include 'includes/footer.php'; ?>
<?php
// FILE: login.php

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
                <h2>Welcome Back</h2>
                <p class="login-subtitle">Sign in to access your AgroKart account</p>
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

            <form action="php/login_process.php" method="post" class="login-form">
                <div class="form-group">
                    <div class="input-icon-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="Email address" required>
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-icon-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember me</label>
                    </div>
                    <a href="forgot_password.php" class="forgot-password">Forgot password?</a>
                </div>

                <button type="submit" class="login-button">
                    <span>Sign In</span>
                    <i class="fas fa-arrow-right"></i>
                </button>

                <div class="login-divider">
                    <span>OR</span>
                </div>

                <div class="social-login">
                    <a href="php/google_login.php" class="social-btn google">
                        <i class="fab fa-google"></i>
                        <span>Continue with Google</span>
                    </a>
                </div>

                <p class="register-link">Don't have an account? <a href="registration.php">Create Account</a></p>
            </form>
        </div>
    </div>

    <div class="login-decoration">
        <div class="floating-shape shape1"></div>
        <div class="floating-shape shape2"></div>
        <div class="floating-shape shape3"></div>
    </div>
</div>

<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');

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

<!-- Bootstrap JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php include 'includes/footer.php'; ?>
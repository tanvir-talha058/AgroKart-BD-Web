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
                    <a href="#" onclick="showGoogleLoginModal()" class="social-btn google">
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

<!-- Google Login Modal -->
<div class="modal fade" id="googleLoginModal" tabindex="-1" aria-labelledby="googleLoginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom: 1px solid #e5e5e5;">
                <h5 class="modal-title" id="googleLoginModalLabel">
                    <i class="fab fa-google" style="color: #4285f4;"></i>
                    Sign in with Google
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Enter your Gmail address to continue as a buyer:</p>
                <form id="googleMockForm" action="php/google_mock_login.php" method="post">
                    <div class="mb-3">
                        <div class="input-icon-wrapper">
                            <i class="fab fa-google" style="color: #4285f4;"></i>
                            <input type="email" name="google_email" id="google_email" class="form-control" placeholder="Enter your Gmail address" required pattern=".*@gmail\.com$" title="Please enter a valid Gmail address">
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="input-icon-wrapper">
                            <i class="fas fa-user"></i>
                            <input type="text" name="google_name" id="google_name" class="form-control" placeholder="Your display name" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="googleMockForm" class="btn" style="background-color: #4285f4; color: white;">
                    <i class="fab fa-google"></i> Continue as Buyer
                </button>
            </div>
        </div>
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

    function showGoogleLoginModal() {
        const modal = new bootstrap.Modal(document.getElementById('googleLoginModal'));
        modal.show();
    }
</script>

<!-- Bootstrap JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php include 'includes/footer.php'; ?>
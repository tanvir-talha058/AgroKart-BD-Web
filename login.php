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
include 'includes/header.php';
?>
<div class="form-container">
    <form action="php/login_process.php" method="post">
        <h2>Login to Your Account</h2>
        <?php
        if (isset($_SESSION['error'])) { echo '<p class="error-message">' . $_SESSION['error'] . '</p>'; unset($_SESSION['error']); }
        if (isset($_SESSION['message'])) { echo '<p class="success-message">' . $_SESSION['message'] . '</p>'; unset($_SESSION['message']); }
        ?>
        <div class="form-group"><label for="email">Email</label><input type="email" id="email" name="email" required></div>
        <div class="form-group"><label for="password">Password</label><input type="password" id="password" name="password" required></div>
        <button type="submit" class="submit-button">Login</button>
        <p class="switch-form">Don't have an account? <a href="registration.php">Register</a></p>
    </form>
</div>
<?php include 'includes/footer.php'; ?>
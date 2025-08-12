<?php
// FILE: php/cancel_reset.php

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Clear the forgot_email session variable
unset($_SESSION['forgot_email']);

// Redirect to the login page with a message
$_SESSION['message'] = "Password reset process has been cancelled.";
header("Location: ../login.php");
exit;

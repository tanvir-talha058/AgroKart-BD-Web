<?php
// FILE: php/forgot_password_process.php

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once '../includes/db_connect.php';
require_once '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Function to generate a random 6-digit OTP
function generateOTP()
{
    return rand(100000, 999999);
}

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the email from the form
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    if (!$email) {
        $_SESSION['error'] = "Please provide a valid email address.";
        header("Location: ../forgot_password.php");
        exit;
    }

    // Check if the email exists in the database
    $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // Email not found, but for security reasons, we'll show the same message
        $_SESSION['message'] = "If your email is registered with us, you will receive a password reset code shortly.";
        header("Location: ../forgot_password.php");
        exit;
    }

    // Get user details
    $user = $result->fetch_assoc();
    $user_id = $user['id'];
    $user_name = $user['name'];

    // Set default timezone to ensure consistency
    date_default_timezone_set('UTC');

    // Generate OTP
    $otp = generateOTP();
    $expiry_time = date('Y-m-d H:i:s', strtotime('+1 hour')); // Extended to 1 hour

    // Check if there's an existing OTP for this user
    $check_stmt = $conn->prepare("SELECT id FROM password_reset_tokens WHERE user_id = ?");
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    // Store the OTP in the database (create or update)
    if ($check_result->num_rows > 0) {
        // Update existing token
        $update_stmt = $conn->prepare("UPDATE password_reset_tokens SET token = ?, expires_at = ? WHERE user_id = ?");
        $update_stmt->bind_param("ssi", $otp, $expiry_time, $user_id);
        $update_stmt->execute();
    } else {
        // Create new token
        $insert_stmt = $conn->prepare("INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
        $insert_stmt->bind_param("iss", $user_id, $otp, $expiry_time);
        $insert_stmt->execute();
    }

    // Log the generated OTP for debugging
    error_log("Generated OTP for user ID $user_id: $otp, expires at $expiry_time");

    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'fahimtalha79@gmail.com'; // Your Gmail address
        $mail->Password = 'kpfmqrbyutecsqip'; // Your Gmail app password - generate a new one if needed
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Using SSL instead of TLS
        $mail->Port = 465; // SSL port
        $mail->SMTPDebug = 0; // Turn off debug output for production
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        // Recipients
        $mail->setFrom('fahimtalha79@gmail.com', 'AgroKart BD');
        $mail->addAddress($email, $user_name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Password Reset Request - AgroKart BD";

        $message = "
        <html>
        <head>
            <title>Password Reset Request</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9; border-radius: 10px; }
                .header { text-align: center; padding: 20px 0; }
                .header img { max-width: 150px; }
                .content { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .code { font-size: 24px; font-weight: bold; text-align: center; padding: 15px; margin: 20px 0; background: #f5f5f5; border-radius: 5px; letter-spacing: 5px; }
                .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #777; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <img src='https://ibb.co.com/YFmnNsvQ' alt='AgroKart BD Logo'>
                </div>
                <div class='content'>
                    <h2>Hello, " . htmlspecialchars($user_name) . "!</h2>
                    <p>We received a request to reset your password for your AgroKart BD account. Please use the following verification code to complete the process:</p>
                    
                    <div class='code'>$otp</div>
                    
                    <p>This code will expire in 15 minutes for security reasons.</p>
                    <p>If you didn't request a password reset, please ignore this email or contact customer support if you have concerns.</p>
                    <p>Thank you,<br>AgroKart BD Team</p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " AgroKart BD. All rights reserved.</p>
                    <p>This is an automated message, please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";

        $mail->Body = $message;

        // Send the email
        $mail->send();

        // Store the email in session for the next step
        $_SESSION['forgot_email'] = $email;

        // Redirect back to the forgot password page with success message
        $_SESSION['message'] = "A verification code has been sent to your email address.";
        header("Location: ../forgot_password.php");
        exit;
    } catch (Exception $e) {
        // Log the error
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");

        // For production use a more user-friendly message
        $_SESSION['error'] = "We couldn't send the verification code. Please try again or contact support.";
        header("Location: ../forgot_password.php");
        exit;
    }
} else {
    // If not a POST request, redirect to the forgot password page
    header("Location: ../forgot_password.php");
    exit;
}

<?php
// FILE: php/forgot_password_process.php

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once '../includes/db_connect.php';

// Include PHPMailer
require_once '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Function to generate a random 6-digit OTP
function generateOTP()
{
    return rand(100000, 999999);
}

// Function to send email using PHPMailer with Gmail SMTP
function sendResetEmail($to_email, $otp, $user_name)
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'fahimtalha79@gmail.com';                     // Change this to your Gmail address
        $mail->Password   = 'hswjveecysxdnesl';                        // Change this to your Gmail app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('noreply@agrokartbd.com', 'AgroKart BD');
        $mail->addAddress($to_email, $user_name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'AgroKart BD - Password Reset Code';
        $mail->Body = "
        <html>
        <head>
            <title>Password Reset - AgroKart BD</title>
        </head>
        <body>
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                <div style='text-align: center; margin-bottom: 30px;'>
                    <h2 style='color: #2c5530; margin-bottom: 10px;'>AgroKart BD</h2>
                    <p style='color: #666; font-size: 14px;'>Fresh From Farm</p>
                </div>
                
                <h3 style='color: #333; margin-bottom: 20px;'>Password Reset Request</h3>
                
                <p style='color: #555; line-height: 1.6; margin-bottom: 20px;'>
                    Hi " . htmlspecialchars($user_name) . ",
                </p>
                
                <p style='color: #555; line-height: 1.6; margin-bottom: 20px;'>
                    You have requested to reset your password for your AgroKart BD account. 
                    Use the verification code below to complete your password reset:
                </p>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <div style='display: inline-block; background-color: #f8f9fa; padding: 20px 30px; border: 2px dashed #2c5530; border-radius: 10px;'>
                        <h2 style='color: #2c5530; margin: 0; letter-spacing: 3px; font-size: 32px;'>" . $otp . "</h2>
                    </div>
                </div>
                
                <p style='color: #555; line-height: 1.6; margin-bottom: 20px;'>
                    This code will expire in <strong>15 minutes</strong> for security purposes.
                </p>
                
                <p style='color: #555; line-height: 1.6; margin-bottom: 30px;'>
                    If you did not request this password reset, please ignore this email. Your password will remain unchanged.
                </p>
                
                <div style='border-top: 1px solid #ddd; padding-top: 20px; text-align: center;'>
                    <p style='color: #888; font-size: 12px; margin-bottom: 5px;'>
                        This is an automated message from AgroKart BD
                    </p>
                    <p style='color: #888; font-size: 12px;'>
                        Connecting farmers directly to consumers
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("PHPMailer Error: {$mail->ErrorInfo}");
        return false;
    }
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

    // Send email using PHPMailer
    $email_sent = sendResetEmail($email, $otp, $user_name);

    if ($email_sent) {
        // Store the email in session for the next step
        $_SESSION['forgot_email'] = $email;
        $_SESSION['message'] = "A verification code has been sent to your email address. Please check your inbox.";
        $_SESSION['message_type'] = "success";
    } else {
        // Email sending failed
        $_SESSION['message'] = "Failed to send verification email. Please try again later or contact support.";
        $_SESSION['message_type'] = "error";
        error_log("Failed to send OTP email to: $email");
    }

    header("Location: ../forgot_password.php");
    exit;
} else {
    // If not a POST request, redirect to the forgot password page
    header("Location: ../forgot_password.php");
    exit;
}

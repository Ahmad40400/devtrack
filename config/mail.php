<?php
// =============================================
// Mail Configuration (PHPMailer)
// =============================================

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Require PHPMailer files - Correct Path (Capital P)
require_once __DIR__ . '/../includes/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../includes/PHPMailer/SMTP.php';
require_once __DIR__ . '/../includes/PHPMailer/Exception.php';

// Mail Settings (Gmail - Example)
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'your-email@gmail.com'); // Apna Gmail likho
define('MAIL_PASSWORD', 'your-app-password'); // Gmail App Password
define('MAIL_FROM', 'your-email@gmail.com');
define('MAIL_FROM_NAME', 'DevTrack');

// =============================================
// SITE URL - LOCALHOST (XAMPP) KE LIYE
// =============================================
// ✅ ABHI LOCALHOST KE LIYE YE URL USE HO RAHA HAI
// ⚠️ JAB LIVE SITE PAR HOST KARO TO YE COMMENT HATA KAR NEECHE WALA URL LAGAO
define('SITE_URL', 'http://localhost/devtrack/');

// =============================================
// LIVE URL - JAB ACTUAL SITE PAR HOST KARO
// =============================================
// define('SITE_URL', 'https://devtracker.free.nf/'); // ⚠️ LIVE KE LIYE YE UNCOMMENT KARO

// Send Email Function
function sendEmail($to, $subject, $htmlBody) {
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;
        
        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($to);
        
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags($htmlBody);
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Send OTP Email
function sendOTPEmail($to, $otp) {
    $subject = 'DevTrack - Email Verification Code';
    
    $htmlBody = '
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f8fafc; border-radius: 10px;">
        <div style="text-align: center; margin-bottom: 20px;">
            <h2 style="color: #6366f1;">DevTrack</h2>
            <p style="color: #64748b;">Email Verification</p>
        </div>
        <div style="background: white; padding: 30px; border-radius: 10px; text-align: center;">
            <p style="color: #334155; font-size: 16px;">Your verification code is:</p>
            <h1 style="font-size: 48px; color: #6366f1; letter-spacing: 10px; margin: 20px 0;">' . $otp . '</h1>
            <p style="color: #64748b; font-size: 14px;">Enter this code on the DevTrack website to verify your account.</p>
            <p style="color: #94a3b8; font-size: 12px; margin-top: 20px;">This code is valid for 10 minutes.</p>
        </div>
        <div style="text-align: center; margin-top: 20px;">
            <p style="color: #94a3b8; font-size: 12px;">If you didn\'t request this, please ignore this email.</p>
        </div>
    </div>
    ';
    
    return sendEmail($to, $subject, $htmlBody);
}

// Send Welcome Email
function sendWelcomeEmail($to, $username) {
    $subject = 'Welcome to DevTrack!';
    
    $htmlBody = '
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f8fafc; border-radius: 10px;">
        <div style="text-align: center; margin-bottom: 20px;">
            <h2 style="color: #6366f1;">DevTrack</h2>
            <p style="color: #64748b;">Account Created Successfully</p>
        </div>
        <div style="background: white; padding: 30px; border-radius: 10px;">
            <p style="color: #334155; font-size: 16px;">Hi <strong>' . $username . '</strong>,</p>
            <p style="color: #64748b; font-size: 14px;">Congratulations! Your DevTrack account has been created successfully.</p>
            <p style="color: #64748b; font-size: 14px;">Now you can:</p>
            <ul style="color: #64748b; font-size: 14px;">
                <li>Create and manage projects</li>
                <li>Track your tasks</li>
                <li>Add and improve skills</li>
                <li>Set learning goals</li>
                <li>Showcase your portfolio</li>
            </ul>
            <div style="text-align: center; margin-top: 20px;">
                <a href="' . SITE_URL . '" style="background: #6366f1; color: white; padding: 12px 30px; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: bold;">Get Started</a>
            </div>
        </div>
        <div style="text-align: center; margin-top: 20px;">
            <p style="color: #94a3b8; font-size: 12px;">© 2026 DevTrack. All rights reserved.</p>
        </div>
    </div>
    ';
    
    return sendEmail($to, $subject, $htmlBody);
}

// Send Password Reset Email
function sendPasswordResetEmail($to, $resetLink) {
    $subject = 'DevTrack - Password Reset';
    
    $htmlBody = '
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f8fafc; border-radius: 10px;">
        <div style="text-align: center; margin-bottom: 20px;">
            <h2 style="color: #6366f1;">DevTrack</h2>
            <p style="color: #64748b;">Password Reset Request</p>
        </div>
        <div style="background: white; padding: 30px; border-radius: 10px; text-align: center;">
            <p style="color: #334155; font-size: 16px;">Click the button below to reset your password:</p>
            <div style="text-align: center; margin: 30px 0;">
                <a href="' . $resetLink . '" style="background: #6366f1; color: white; padding: 14px 35px; text-decoration: none; border-radius: 8px; font-size: 16px; font-weight: bold;">Reset Password</a>
            </div>
            <p style="color: #64748b; font-size: 14px;">Or copy this link:</p>
            <p style="color: #6366f1; font-size: 12px; word-break: break-all;">' . $resetLink . '</p>
            <p style="color: #94a3b8; font-size: 12px; margin-top: 20px;">This link is valid for 30 minutes.</p>
        </div>
        <div style="text-align: center; margin-top: 20px;">
            <p style="color: #94a3b8; font-size: 12px;">If you didn\'t request this, please ignore this email.</p>
        </div>
    </div>
    ';
    
    return sendEmail($to, $subject, $htmlBody);
}
?>
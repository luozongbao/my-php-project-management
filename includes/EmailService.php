<?php
/**
 * Email utility class using PHPMailer
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $mailer;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = SMTP_HOST;
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = SMTP_USER;
            $this->mailer->Password = SMTP_PASS;
            $this->mailer->SMTPSecure = SMTP_PROTOCOL;
            $this->mailer->Port = SMTP_PORT;
            
            // Default from address
            $this->mailer->setFrom(SMTP_USER, APP_NAME);
        } catch (Exception $e) {
            error_log("Email configuration failed: " . $e->getMessage());
            throw new Exception("Email service unavailable");
        }
    }
    
    public function sendPasswordResetEmail($to_email, $to_name, $reset_token) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to_email, $to_name);
            
            $reset_url = APP_URL . "/reset_password.php?token=" . $reset_token;
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Password Reset Request - ' . APP_NAME;
            $this->mailer->Body = $this->getPasswordResetTemplate($to_name, $reset_url);
            $this->mailer->AltBody = "Hello $to_name,\n\nYou requested a password reset. Click the following link to reset your password:\n$reset_url\n\nThis link will expire in 1 hour.\n\nIf you didn't request this, please ignore this email.";
            
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Failed to send password reset email: " . $e->getMessage());
            return false;
        }
    }
    
    public function sendWelcomeEmail($to_email, $to_name, $reset_token) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to_email, $to_name);
            
            $reset_url = APP_URL . "/reset_password.php?token=" . $reset_token;
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Welcome to ' . APP_NAME . ' - Set Your Password';
            $this->mailer->Body = $this->getWelcomeTemplate($to_name, $reset_url);
            $this->mailer->AltBody = "Welcome to " . APP_NAME . ", $to_name!\n\nYour account has been created successfully. Please set your password by clicking the following link:\n$reset_url\n\nThis link will expire in 1 hour.";
            
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Failed to send welcome email: " . $e->getMessage());
            return false;
        }
    }
    
    private function getPasswordResetTemplate($name, $reset_url) {
        return "
        <html>
        <body style='font-family: Arial, sans-serif; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #007bff;'>Password Reset Request</h2>
                <p>Hello <strong>$name</strong>,</p>
                <p>You requested a password reset for your " . APP_NAME . " account.</p>
                <p>Click the button below to reset your password:</p>
                <p style='text-align: center; margin: 30px 0;'>
                    <a href='$reset_url' style='background: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>Reset Password</a>
                </p>
                <p><strong>Important:</strong> This link will expire in 1 hour for security reasons.</p>
                <p>If you didn't request this password reset, please ignore this email. Your password will remain unchanged.</p>
                <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>
                <p style='color: #666; font-size: 12px;'>This email was sent by " . APP_NAME . "</p>
            </div>
        </body>
        </html>";
    }
    
    private function getWelcomeTemplate($name, $reset_url) {
        return "
        <html>
        <body style='font-family: Arial, sans-serif; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #28a745;'>Welcome to " . APP_NAME . "!</h2>
                <p>Hello <strong>$name</strong>,</p>
                <p>Your account has been created successfully! Welcome to " . APP_NAME . ".</p>
                <p>To get started, please set your password by clicking the button below:</p>
                <p style='text-align: center; margin: 30px 0;'>
                    <a href='$reset_url' style='background: #28a745; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>Set Password</a>
                </p>
                <p><strong>Important:</strong> This link will expire in 1 hour for security reasons.</p>
                <p>Once you've set your password, you'll be able to:</p>
                <ul>
                    <li>Create and manage projects</li>
                    <li>Track tasks and subtasks</li>
                    <li>Manage project contacts</li>
                    <li>Monitor project progress</li>
                </ul>
                <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>
                <p style='color: #666; font-size: 12px;'>This email was sent by " . APP_NAME . "</p>
            </div>
        </body>
        </html>";
    }
}
?>
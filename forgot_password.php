<?php
require_once 'config.php';
require_once 'includes/Database.php';
require_once 'includes/functions.php';
require_once 'includes/EmailService.php';

startSession();

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$errors = [];
$success = false;

if ($_POST) {
    $email = sanitize($_POST['email'] ?? '');
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!isValidEmail($email)) {
        $errors[] = "Please enter a valid email address";
    } else {
        $db = Database::getInstance();
        
        // Check if user exists
        $user = $db->fetchOne(
            "SELECT id, name, email FROM users WHERE email = ?",
            [$email]
        );
        
        if ($user) {
            // Delete any existing reset tokens for this user
            $db->query(
                "DELETE FROM password_reset_tokens WHERE user_id = ?",
                [$user['id']]
            );
            
            // Generate new reset token
            $reset_token = generateToken();
            $expires_at = date('Y-m-d H:i:s', time() + PASSWORD_RESET_EXPIRY);
            
            $db->query(
                "INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)",
                [$user['id'], $reset_token, $expires_at]
            );
            
            // Send reset email
            $emailService = new EmailService();
            if ($emailService->sendPasswordResetEmail($user['email'], $user['name'], $reset_token)) {
                $success = true;
            } else {
                $errors[] = "Failed to send reset email. Please try again later.";
            }
        } else {
            // For security, don't reveal that email doesn't exist
            $success = true;
        }
    }
}

$title = "Forgot Password";
?>

<?php include 'includes/header.php'; ?>

<div class="auth-container">
    <div class="auth-form">
        <div class="auth-header">
            <i class="fas fa-key"></i>
            <h1>Forgot Password</h1>
            <p>Enter your email to reset your password</p>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <h4>Reset Email Sent</h4>
                <p>If an account with that email exists, we've sent a password reset link to your email address.</p>
                <p>Please check your email and follow the instructions to reset your password.</p>
                <p>The reset link will expire in 1 hour.</p>
            </div>
        <?php else: ?>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <div><?= e($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        Email Address
                    </label>
                    <input type="email" id="email" name="email" 
                           value="<?= e($_POST['email'] ?? '') ?>" 
                           placeholder="Enter your email address" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-paper-plane"></i>
                    Send Reset Link
                </button>
            </form>
        <?php endif; ?>
        
        <div class="auth-links">
            <a href="login.php">
                <i class="fas fa-arrow-left"></i>
                Back to Login
            </a>
            <a href="register.php">Don't have an account? Register</a>
        </div>
    </div>
</div>

<style>
.main-content.no-nav {
    padding: 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

.auth-container {
    width: 100%;
    max-width: 400px;
    padding: 20px;
}

.auth-form {
    background: white;
    padding: 40px;
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.auth-header {
    text-align: center;
    margin-bottom: 30px;
}

.auth-header i {
    font-size: 48px;
    color: #ffc107;
    margin-bottom: 10px;
}

.auth-header h1 {
    margin: 10px 0;
    color: #333;
    font-size: 24px;
}

.auth-header p {
    color: #666;
    margin: 0;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #333;
}

.form-group label i {
    margin-right: 8px;
    color: #666;
    width: 16px;
}

.form-group input {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 16px;
    transition: border-color 0.3s;
    box-sizing: border-box;
}

.form-group input:focus {
    outline: none;
    border-color: #ffc107;
    box-shadow: 0 0 0 2px rgba(255,193,7,0.25);
}

.btn {
    display: inline-block;
    padding: 12px 24px;
    text-decoration: none;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    transition: all 0.3s;
    text-align: center;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
}

.btn-block {
    width: 100%;
}

.auth-links {
    text-align: center;
    margin-top: 30px;
}

.auth-links a {
    display: block;
    margin: 10px 0;
    color: #007bff;
    text-decoration: none;
    font-size: 14px;
}

.auth-links a:hover {
    text-decoration: underline;
}

.alert {
    padding: 12px;
    margin-bottom: 20px;
    border-radius: 5px;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert h4 {
    margin-top: 0;
    margin-bottom: 10px;
}
</style>

<?php include 'includes/footer.php'; ?>
<?php
/**
 * Installation Script for Project Management System
 */

// Check if already installed
if (file_exists('config.php')) {
    header('Location: index.php');
    exit();
}

$step = $_GET['step'] ?? 1;
$errors = [];
$success = false;

// Step 2: Process database configuration
if ($_POST && $step == 2) {
    $db_host = trim($_POST['db_host'] ?? '');
    $db_name = trim($_POST['db_name'] ?? '');
    $db_user = trim($_POST['db_user'] ?? '');
    $db_pass = $_POST['db_pass'] ?? '';
    
    // Validate database inputs
    if (empty($db_host)) $errors[] = "Database host is required";
    if (empty($db_name)) $errors[] = "Database name is required";
    if (empty($db_user)) $errors[] = "Database user is required";
    
    if (empty($errors)) {
        // Test database connection and create database if needed
        try {
            $pdo = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create database if it doesn't exist
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$db_name`");
            
            // Execute schema
            $schema = file_get_contents('database/schema.sql');
            $pdo->exec($schema);
            
            // Store database config in session for next step
            session_start();
            $_SESSION['db_config'] = [
                'host' => $db_host,
                'name' => $db_name,
                'user' => $db_user,
                'pass' => $db_pass
            ];
            
            header('Location: install.php?step=3');
            exit();
            
        } catch (PDOException $e) {
            $errors[] = "Database connection failed: " . $e->getMessage();
        }
    }
}

// Step 3: Process SMTP configuration and complete installation
if ($_POST && $step == 3) {
    session_start();
    
    $smtp_host = trim($_POST['smtp_host'] ?? '');
    $smtp_port = trim($_POST['smtp_port'] ?? '');
    $smtp_protocol = trim($_POST['smtp_protocol'] ?? '');
    $smtp_user = trim($_POST['smtp_user'] ?? '');
    $smtp_pass = $_POST['smtp_pass'] ?? '';
    
    // Validate SMTP inputs
    if (empty($smtp_host)) $errors[] = "SMTP host is required";
    if (empty($smtp_port) || !is_numeric($smtp_port)) $errors[] = "Valid SMTP port is required";
    if (empty($smtp_protocol)) $errors[] = "SMTP protocol is required";
    if (empty($smtp_user)) $errors[] = "SMTP user is required";
    if (empty($smtp_pass)) $errors[] = "SMTP password is required";
    
    if (empty($errors) && isset($_SESSION['db_config'])) {
        $db_config = $_SESSION['db_config'];
        
        // Generate config.php
        $config_content = "<?php\n";
        $config_content .= "// Database Configuration\n";
        $config_content .= "const DB_HOST = '" . addslashes($db_config['host']) . "';\n";
        $config_content .= "const DB_NAME = '" . addslashes($db_config['name']) . "';\n";
        $config_content .= "const DB_USER = '" . addslashes($db_config['user']) . "';\n";
        $config_content .= "const DB_PASS = '" . addslashes($db_config['pass']) . "';\n\n";
        
        $config_content .= "// SMTP Configuration\n";
        $config_content .= "const SMTP_HOST = '" . addslashes($smtp_host) . "';\n";
        $config_content .= "const SMTP_PORT = " . intval($smtp_port) . ";\n";
        $config_content .= "const SMTP_PROTOCOL = '" . addslashes($smtp_protocol) . "';\n";
        $config_content .= "const SMTP_USER = '" . addslashes($smtp_user) . "';\n";
        $config_content .= "const SMTP_PASS = '" . addslashes($smtp_pass) . "';\n\n";
        
        $config_content .= "// Application Configuration\n";
        $config_content .= "const APP_NAME = 'Project Management System';\n";
        $config_content .= "const APP_URL = 'http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "';\n";
        $config_content .= "const TIMEZONE = 'UTC';\n\n";
        
        $config_content .= "// Security\n";
        $config_content .= "const SESSION_LIFETIME = 3600;\n";
        $config_content .= "const PASSWORD_RESET_EXPIRY = 3600;\n";
        $config_content .= "const ENCRYPTION_KEY = '" . bin2hex(random_bytes(16)) . "';\n";
        $config_content .= "?>";
        
        if (file_put_contents('config.php', $config_content)) {
            session_destroy();
            $success = true;
        } else {
            $errors[] = "Failed to create config.php file. Check file permissions.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install - Project Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .install-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 10px;
            background: #e9ecef;
            color: #6c757d;
        }
        .step.active {
            background: #007bff;
            color: white;
        }
        .step.completed {
            background: #28a745;
            color: white;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .btn {
            background: #007bff;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background: #0056b3;
        }
        .alert {
            padding: 15px;
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
    </style>
</head>
<body>
    <div class="install-container">
        <h1 style="text-align: center; color: #333; margin-bottom: 30px;">
            <i class="fas fa-cogs"></i> Project Management System Installation
        </h1>
        
        <div class="step-indicator">
            <div class="step <?= $step >= 1 ? 'active' : '' ?> <?= $step > 1 ? 'completed' : '' ?>">1</div>
            <div class="step <?= $step >= 2 ? 'active' : '' ?> <?= $step > 2 ? 'completed' : '' ?>">2</div>
            <div class="step <?= $step >= 3 ? 'active' : '' ?> <?= $step > 3 ? 'completed' : '' ?>">3</div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <h3>Installation Complete!</h3>
                <p>Your Project Management System has been successfully installed.</p>
                <p><strong>Next Steps:</strong></p>
                <ol>
                    <li>Delete the install.php file for security</li>
                    <li>Create your first user account</li>
                    <li>Start managing your projects!</li>
                </ol>
                <p><a href="index.php" class="btn">Go to Application</a></p>
            </div>
        <?php elseif ($step == 1): ?>
            <h2>Step 1: Welcome</h2>
            <p>Welcome to the Project Management System installation wizard. This process will:</p>
            <ul>
                <li>Configure your database connection</li>
                <li>Create the necessary database tables</li>
                <li>Set up your email configuration</li>
                <li>Generate your configuration file</li>
            </ul>
            <p><strong>Requirements:</strong></p>
            <ul>
                <li>PHP 8.3 or higher</li>
                <li>MySQL/MariaDB database</li>
                <li>SMTP server for email notifications</li>
                <li>Write permissions on the application directory</li>
            </ul>
            <br>
            <a href="install.php?step=2" class="btn">Start Installation</a>

        <?php elseif ($step == 2): ?>
            <h2>Step 2: Database Configuration</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="db_host">Database Host:</label>
                    <input type="text" id="db_host" name="db_host" value="<?= htmlspecialchars($_POST['db_host'] ?? 'localhost') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="db_name">Database Name:</label>
                    <input type="text" id="db_name" name="db_name" value="<?= htmlspecialchars($_POST['db_name'] ?? 'project_management') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="db_user">Database User:</label>
                    <input type="text" id="db_user" name="db_user" value="<?= htmlspecialchars($_POST['db_user'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="db_pass">Database Password:</label>
                    <input type="password" id="db_pass" name="db_pass" value="<?= htmlspecialchars($_POST['db_pass'] ?? '') ?>">
                </div>
                
                <button type="submit" class="btn">Test Database & Continue</button>
            </form>

        <?php elseif ($step == 3): ?>
            <h2>Step 3: SMTP Configuration</h2>
            <p>Configure your email server settings for password reset emails and notifications.</p>
            
            <form method="POST">
                <div class="form-group">
                    <label for="smtp_host">SMTP Host:</label>
                    <input type="text" id="smtp_host" name="smtp_host" value="<?= htmlspecialchars($_POST['smtp_host'] ?? 'smtp.gmail.com') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="smtp_port">SMTP Port:</label>
                    <input type="number" id="smtp_port" name="smtp_port" value="<?= htmlspecialchars($_POST['smtp_port'] ?? '587') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="smtp_protocol">SMTP Protocol:</label>
                    <select id="smtp_protocol" name="smtp_protocol" required>
                        <option value="tls" <?= ($_POST['smtp_protocol'] ?? 'tls') == 'tls' ? 'selected' : '' ?>>TLS</option>
                        <option value="ssl" <?= ($_POST['smtp_protocol'] ?? '') == 'ssl' ? 'selected' : '' ?>>SSL</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="smtp_user">SMTP Username (Email):</label>
                    <input type="email" id="smtp_user" name="smtp_user" value="<?= htmlspecialchars($_POST['smtp_user'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="smtp_pass">SMTP Password:</label>
                    <input type="password" id="smtp_pass" name="smtp_pass" value="<?= htmlspecialchars($_POST['smtp_pass'] ?? '') ?>" required>
                </div>
                
                <button type="submit" class="btn">Complete Installation</button>
            </form>
        <?php endif; ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>
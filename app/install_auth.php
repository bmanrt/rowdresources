<?php
require_once('../db_config.php');

$error = '';
$success = '';

try {
    // Read the SQL file
    $sql = file_get_contents(__DIR__ . '/app_users.sql');
    
    // Execute the SQL commands
    if ($conn->multi_query($sql)) {
        do {
            // Store first result set
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());
        
        $success = 'Authentication system installed successfully! The app_users table has been created with a default admin user.';
        $success .= '<br><br>Default admin credentials:<br>';
        $success .= 'Username: admin<br>';
        $success .= 'Password: Admin@123<br>';
        $success .= '<br>Please change these credentials after your first login.';
    } else {
        throw new Exception($conn->error);
    }
} catch (Exception $e) {
    $error = 'Error installing authentication system: ' . $e->getMessage();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install Authentication - Media Resource Portal</title>
    <link rel="stylesheet" href="auth.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-logo">
                <img src="assets/images/logo.webp" alt="Logo">
            </div>
            <h1 class="auth-title">Authentication Setup</h1>
            
            <?php if ($error): ?>
                <div class="auth-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
                <div class="auth-links">
                    <p><a href="javascript:location.reload()">Try Again</a></p>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="auth-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
                <div class="auth-links" style="margin-top: 2rem;">
                    <p><a href="login.php" class="auth-button" style="display: inline-block; text-decoration: none;">
                        <i class="fas fa-sign-in-alt"></i>
                        Proceed to Login
                    </a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

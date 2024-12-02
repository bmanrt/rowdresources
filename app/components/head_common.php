<?php
// Common head elements for all pages
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/webp" href="assets/images/logo.webp">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="styles.css">
<?php
// Add auth.css only for authentication pages
$auth_pages = ['login.php', 'register.php', 'forgot_password.php', 'reset_password.php'];
if (in_array(basename($_SERVER['PHP_SELF']), $auth_pages)): 
?>
<link rel="stylesheet" href="auth.css">
<?php endif; ?>

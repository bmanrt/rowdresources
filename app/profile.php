<?php
session_start();
require_once('auth_check.php');
require_once('../db_config.php');

// Ensure user is authenticated
if (!isAuthenticated()) {
    redirectToLogin();
    exit();
}

$error = '';
$success = '';
$user = null;

// Get current user data
$currentUser = getCurrentUser();

// Get detailed user data from database
$stmt = $conn->prepare("SELECT * FROM app_users WHERE id = ?");
$stmt->bind_param("i", $currentUser['id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $full_name = trim($_POST['full_name'] ?? '');
        
        // Validate input
        if (empty($username) || empty($email) || empty($full_name)) {
            $error = 'Please fill in all required fields';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address';
        } else {
            // Check if username or email is already taken by another user
            $check_stmt = $conn->prepare("SELECT id FROM app_users WHERE (username = ? OR email = ?) AND id != ?");
            $check_stmt->bind_param("ssi", $username, $email, $currentUser['id']);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = 'Username or email is already taken';
            } else {
                // Handle profile image upload
                $profile_image = $user['profile_image'];
                if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                    $file_type = $_FILES['profile_image']['type'];
                    
                    if (!in_array($file_type, $allowed_types)) {
                        $error = 'Only JPG, PNG and GIF images are allowed';
                    } else {
                        $upload_dir = 'uploads/profiles/';
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }
                        
                        $file_extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
                        $file_name = uniqid('profile_') . '.' . $file_extension;
                        $upload_path = $upload_dir . $file_name;
                        
                        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                            // Delete old profile image if exists
                            if ($profile_image && file_exists($profile_image)) {
                                unlink($profile_image);
                            }
                            $profile_image = $upload_path;
                        } else {
                            $error = 'Failed to upload profile image';
                        }
                    }
                }
                
                if (!$error) {
                    // Update profile
                    $update_stmt = $conn->prepare("UPDATE app_users SET username = ?, email = ?, full_name = ?, profile_image = ? WHERE id = ?");
                    $update_stmt->bind_param("ssssi", $username, $email, $full_name, $profile_image, $currentUser['id']);
                    
                    if ($update_stmt->execute()) {
                        $_SESSION['app_username'] = $username;
                        $_SESSION['app_email'] = $email;
                        $success = 'Profile updated successfully';
                        
                        // Refresh user data
                        $stmt = $conn->prepare("SELECT * FROM app_users WHERE id = ?");
                        $stmt->bind_param("i", $currentUser['id']);
                        $stmt->execute();
                        $user = $stmt->get_result()->fetch_assoc();
                        $stmt->close();
                    } else {
                        $error = 'Failed to update profile';
                    }
                    $update_stmt->close();
                }
            }
            $check_stmt->close();
        }
    } elseif ($action === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = 'Please fill in all password fields';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match';
        } elseif (strlen($new_password) < 8) {
            $error = 'Password must be at least 8 characters long';
        } elseif (!password_verify($current_password, $user['password'])) {
            $error = 'Current password is incorrect';
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE app_users SET password = ? WHERE id = ?");
            $update_stmt->bind_param("si", $hashed_password, $currentUser['id']);
            
            if ($update_stmt->execute()) {
                $success = 'Password changed successfully';
            } else {
                $error = 'Failed to change password';
            }
            $update_stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Media Resource Portal</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="auth.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .profile-container {
            padding-top: calc(var(--header-height) + 2rem);
            max-width: 800px;
            margin: 0 auto;
            padding-left: 2rem;
            padding-right: 2rem;
        }

        .profile-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 0 auto 1rem;
            position: relative;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-avatar .upload-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.7);
            padding: 0.5rem;
            font-size: 0.8rem;
            text-align: center;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .profile-avatar:hover .upload-overlay {
            opacity: 1;
        }

        .profile-sections {
            display: grid;
            gap: 2rem;
        }

        .profile-section {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .section-title {
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            color: var(--white);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .profile-container {
                padding-left: 1rem;
                padding-right: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include('components/header.php'); ?>

    <div class="profile-container">
        <div class="profile-header">
            <form id="avatarForm" method="POST" enctype="multipart/form-data" style="display: none;">
                <input type="hidden" name="action" value="update_profile">
                <input type="file" id="profileImageInput" name="profile_image" accept="image/*">
            </form>
            
            <div class="profile-avatar" onclick="document.getElementById('profileImageInput').click()">
                <?php if ($user['profile_image']): ?>
                    <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile">
                <?php else: ?>
                    <i class="fas fa-user-circle" style="font-size: 3rem; color: var(--gray-400);"></i>
                <?php endif; ?>
                <div class="upload-overlay">
                    <i class="fas fa-camera"></i>
                    Change Photo
                </div>
            </div>
            <h1><?php echo htmlspecialchars($user['full_name']); ?></h1>
            <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
        </div>

        <?php if ($error): ?>
            <div class="auth-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="auth-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <div class="profile-sections">
            <div class="profile-section">
                <h2 class="section-title">
                    <i class="fas fa-user"></i>
                    Profile Information
                </h2>
                <form method="POST" class="auth-form">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" required 
                                   value="<?php echo htmlspecialchars($user['username']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" required 
                                   value="<?php echo htmlspecialchars($user['email']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" required 
                               value="<?php echo htmlspecialchars($user['full_name']); ?>">
                    </div>
                    
                    <button type="submit" class="auth-button">
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>
                </form>
            </div>

            <div class="profile-section">
                <h2 class="section-title">
                    <i class="fas fa-lock"></i>
                    Change Password
                </h2>
                <form method="POST" class="auth-form">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="auth-button">
                        <i class="fas fa-key"></i>
                        Change Password
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
    <script>
        // Handle profile image upload
        document.getElementById('profileImageInput').addEventListener('change', function() {
            if (this.files && this.files[0]) {
                document.getElementById('avatarForm').submit();
            }
        });
    </script>
</body>
</html>

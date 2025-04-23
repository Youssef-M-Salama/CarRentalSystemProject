<?php
session_start();
include "includes/config.php";

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: Login-Signup-Logout/login.php");
    exit;
}

// Display status messages
$notification = '';
if (isset($_SESSION['message'])) {
    $notification = $_SESSION['message'];
    unset($_SESSION['message']);
    unset($_SESSION['msg_type']);
}

// Get user data
$user_id = $_SESSION['user']['id'];
$query = "SELECT * FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);
$current_role = $user['role'];

// Form submission handling
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Email uniqueness check
    $email_check_query = "SELECT * FROM users WHERE email = '$email' AND id != $user_id";
    $email_check_result = mysqli_query($conn, $email_check_query);
    if (mysqli_num_rows($email_check_result) > 0) {
        $errors[] = "Email already exists.";
    }

    // Password validation
    if (!empty($new_password)) {
        if ($new_password !== $confirm_password) {
            $errors[] = "Passwords do not match.";
        }
    }

    if (empty($errors)) {
        $password_clause = !empty($new_password) ? ", password = '$new_password'" : "";
        $update_query = "UPDATE users 
                         SET username = '$username', 
                             email = '$email' 
                             $password_clause
                         WHERE id = $user_id";
        mysqli_query($conn, $update_query);
        $success = "Profile updated successfully!";
        
        // Update session data
        $_SESSION['user']['username'] = $username;
        $_SESSION['user']['email'] = $email;
        
        header("Location: profile.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings</title>
    <link rel="stylesheet" href="css/forms.css">
    <style>
        /* Professional Notification Styling */
        .notification-card {
            position: relative;
            padding: 25px;
            border-radius: 12px;
            margin: 20px 0;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 20px;
            animation: slideIn 0.5s ease;
        }

        .notification-card.success {
            background: #e8f5e9;
            border-left: 5px solid #28a745;
        }

        .notification-card .icon-container {
            flex-shrink: 0;
            width: 45px;
            height: 45px;
        }

        .checkmark-circle {
            stroke: #28a745;
            stroke-width: 3;
            stroke-dasharray: 166;
            stroke-dashoffset: 166;
            animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
        }

        .checkmark-check {
            stroke: #28a745;
            stroke-width: 3;
            stroke-dasharray: 48;
            stroke-dashoffset: 48;
            animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
        }

        .notification-card h4 {
            color: #155724;
            margin: 0 0 8px;
            font-size: 1.25rem;
        }

        .notification-card p {
            color: #525f69;
            margin: 0;
            line-height: 1.5;
        }

        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: 0;
            font-size: 1.2rem;
            color: #6c757d;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .close-btn:hover {
            color: #343a40;
        }

        @keyframes stroke {
            100% {
                stroke-dashoffset: 0;
            }
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Responsive design */
        @media (max-width: 576px) {
            .notification-card {
                flex-direction: column;
                text-align: center;
            }
            
            .checkmark {
                margin: 0 auto 15px;
            }
        }

        /* Profile container styling */
        .profile-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        .profile-form input {
            width: 100%;
            padding: 15px;
            border: 2px solid #bdc3c7;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-cancel {
            background: #e74c3c;
            color: white;
            margin-left: 15px;
        }

        .error-list {
            list-style: none;
            padding: 20px;
            background: #ffebee;
            border-radius: 6px;
            margin-bottom: 25px;
        }

        .success {
            padding: 20px;
            background: #e8f5e9;
            color: #2e7d32;
            border-radius: 6px;
            margin-bottom: 25px;
        }

        @media (max-width: 768px) {
            .profile-form {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <main class="profile-container">
        <?= $notification ?>
        
        <div class="profile-header">
            <h1>Profile Settings</h1>
            <p>Manage your account information</p>
        </div>
        
        <?php if (!empty($success)): ?>
            <div class="success"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <ul class="error-list">
                <?php foreach ($errors as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form method="POST" class="profile-form">
            <div>
                <label for="username">Username</label>
                <input type="text" id="username" name="username" 
                       value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            
            <div>
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" 
                       value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            
            <div>
                <label for="type">Account Type</label>
                <input type="text" id="type" name="type" 
                       value="<?= htmlspecialchars($current_role) ?>" disabled>
            </div>

            <div class="full-width">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" 
                       placeholder="Leave blank to keep current password">
            </div>
            
            <div class="full-width">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password">
            </div>

            <div class="full-width">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="index.php" class="btn btn-cancel">Cancel</a>
            </div>
        </form>

        <?php if (in_array($current_role, ['client', 'premium'])): ?>
            <div class="full-width" style="margin-top: 30px;">
                <form action="request_to_change_role.php" method="POST">
                    <input type="hidden" name="request_type" 
                           value="<?= $current_role == 'client' ? 'premium' : 'client' ?>">
                    <button type="submit" class="btn <?= $current_role == 'client' ? 'btn-success' : 'btn-warning' ?>">
                        <?= $current_role == 'client' ? 'Upgrade to Premium' : 'Downgrade to Client' ?>
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <div class="full-width" style="margin-top: 20px;">
            <a href="index.php" class="btn btn-secondary">Back to Home</a>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Dismiss notifications
            document.querySelectorAll('.notification-card .close-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const notification = this.closest('.notification-card');
                    notification.style.opacity = '0';
                    setTimeout(() => notification.remove(), 300);
                });
            });
        });
    </script>
</body>
</html>
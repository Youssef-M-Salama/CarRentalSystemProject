<?php
session_start();
include "includes/config.php";

if (!isset($_SESSION['user'])) {
    header("Location: Login-Signup-Logout/login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$query = "SELECT * FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

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
        /* Add professional styling */
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

        .profile-header h1 {
            color: #2c3e50;
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .profile-header p {
            color: #7f8c8d;
            font-size: 1.2em;
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

        .profile-form input:focus {
            border-color: #3498db;
            outline: none;
        }

        .profile-form label {
            display: block;
            margin-bottom: 8px;
            color: #34495e;
            font-weight: 600;
        }

        .full-width {
            grid-column: span 2;
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background: #2980b9;
        }

        .btn-cancel {
            background: #e74c3c;
            color: white;
            margin-left: 15px;
        }

        .btn-cancel:hover {
            background: #c0392b;
        }

        .error-list {
            list-style: none;
            padding: 20px;
            background: #ffebee;
            border-radius: 6px;
            margin-bottom: 25px;
        }

        .error-list li {
            color: #c62828;
            padding: 5px 0;
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
                <label for="type">Type </label>
                <input type="text" id="type" name="type" disable
                 value="<?= htmlspecialchars($user['role']) ?>" disabled >
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
        <br>
        <a href=index.php > Home </a>
    </main>
</body>
</html>
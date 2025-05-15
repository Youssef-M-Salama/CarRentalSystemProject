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
    <!-- google fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@100..900&display=swap" rel="stylesheet">
 <!-- font awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <!-- bootstrap css -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <!-- Include CSS stylesheets -->
    <link rel="stylesheet" href="css/forms.css">
    <link rel="stylesheet" href="css/general.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/main-content.css">
    <link rel="stylesheet" href="css/buttons.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/sort-filter.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/offers.css">
    <link rel="stylesheet" href="css/profile.css">
</head>
<body>
         <!-- Website Header Section -->
<header class="navbar navbar-expand-lg bg-body-tertiary d-flex justify-content-center align-items-center">
    <nav class="container-fluid d-flex justify-content-center align-items-center">
    <h1 class="navbar-brand">Car Rental Service</h1>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav">
        <li class="nav-item"><a href="index.php" class="nav-link" >Home</a></li>
        
        <?php if (isset($_SESSION['user'])): ?>
          <li class="nav-item"><a class="nav-link" href="my_rental.php">My Rentals</a></li>
          <li class="nav-item"><a class="nav-link" aria-current="page" href="offers.php">Special Offers</a></li>
          <li class="nav-item"><a class="nav-link" href="Login-Signup-Logout/logout.php">Logout</a></li>
        <?php endif; ?>
        
        <li class="nav-item"><a class="nav-link" href="about us.html">About Us</a></li>
        
        <li class="nav-item">
            <a href="profile.php" class=" nav-link profile-link active" aria-current="page">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
            </svg>
            </a>
        </li>
      </ul>
      </div>
    </nav>
  </header>

    <main class="profile">
        <?= $notification ?>
        <div class="profile-container">
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
            <div class="full-width">
                <form action="request_to_change_role.php" method="POST">
                    <input type="hidden" name="request_type" 
                        value="<?= $current_role == 'client' ? 'premium' : 'client' ?>">
                    <button type="submit" class="btn btn-warning <?= $current_role == 'client' ? 'btn-success' : 'btn-warning' ?>">
                        <?= $current_role == 'client' ? 'Upgrade to Premium' : 'Downgrade to Client' ?>
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </main>
    <!-- Footer Section -->
    <footer>
        <div class="footer-container">
            <!-- Contact Information -->
            <div class="footer-section">
                <h3>Contact Us</h3>
                <a href="mailto:info@carrentalservice.com" >Email: info@carrentalservice.com</a>
                <a href="01234567890" >Phone: 01234567890</a>
            </div>
            
            <!-- Social Media Links -->
            <div class="footer-section">
                <h3>Follow Us</h3>
                <ul class="social-links">
                    <li><a href="#"><i class="fab fa-facebook"></i></a></li>
                    <li><a href="https://github.com/Youssef-M-Salama/CarRentalSystemProject"><i class="fa-brands fa-github"></i></a></li>
                    <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                    <li><a href="#"><i class="fab fa-linkedin"></i></a></li>
                </ul>
            </div>
            
            <!-- Newsletter Subscription -->
            <div class="footer-section">
                <h3>Subscribe</h3>
                <form>
                    <input type="email" placeholder="Enter your email" required>
                    <button type="submit">Subscribe</button>
                </form>
            </div>
        </div>
        
        <!-- Copyright Notice -->
        <div class="copyright">
            <p>&copy; 2025 Car Rental Service. All rights reserved.</p>
        </div>
    </footer>
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
        <!-- bootstrap js -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
</body>
</html>
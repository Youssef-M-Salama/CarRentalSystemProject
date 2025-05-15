<?php
session_start();
include "../includes/config.php";

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../Login-Signup-Logout/login.php");
    exit;
}

// Get the user ID from the query string
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

// Fetch the user's details from the database
$query = "SELECT * FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Handle form submission to update the user's role
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_role = mysqli_real_escape_string($conn, $_POST['role']);

    // Update the user's role in the database
    $update_query = "UPDATE users SET role = '$new_role' WHERE id = $user_id";
    mysqli_query($conn, $update_query);

    // Redirect back to the admin dashboard
    header("Location: DashboardAdmin.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <!-- google fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@100..900&display=swap" rel="stylesheet">
    <!-- font awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <!-- bootstrap css -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <!-- Include CSS stylesheets -->
    <link rel="stylesheet" href="../css/rent-request.css">
    <link rel="stylesheet" href="../css/AdminDashboard.css">
    <link rel="stylesheet" href="../css/general.css">
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/main-content.css">
    <link rel="stylesheet" href="../css/sort-filter.css">
    <link rel="stylesheet" href="../css/offers.css">
    <link rel="stylesheet" href="../css/buttons.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="../css/index.css">
</head>
<body>
<!-- Header Section -->
<header class="navbar navbar-expand-lg bg-body-tertiary d-flex justify-content-center align-items-center">
    <nav class="container-fluid d-flex justify-content-center align-items-center">
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <h1 class="navbar-brand">Car Rental Service</h1>
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="DashboardAdmin.php">Back to Admin Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="../index.php">Back to Home</a></li>
                <li class="nav-item"><a class="nav-link" href="../Login-Signup-Logout/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>
</header>

    <!-- Main Content -->
    <main class="edit-user-container">
        <h2>Edit User: <?php echo htmlspecialchars($user['username']); ?></h2>
        <form method="POST" action="edit_user.php?user_id=<?php echo $user['id']; ?>">
            <label for="role">Role:</label>
            <select name="role" id="role" required>
                <option value="client" <?php echo ($user['role'] === 'client') ? 'selected' : ''; ?>>Client</option>
                <option value="admin" <?php echo ($user['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                <option value="premium" <?php echo ($user['role'] === 'premium') ? 'selected' : ''; ?>>premium</option>
            </select>
            <button type="submit">Update Role</button>
        </form>
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
    <!-- bootstrap js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
</body>
</html>
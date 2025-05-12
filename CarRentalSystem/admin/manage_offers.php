<?php
session_start();
require_once '../includes/config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../Login-Signup-Logout/login.php');
    exit();
}

// Fetch all cars for the dropdown
$cars_sql = "SELECT id, name, model FROM cars WHERE status = 'available'";
$cars_result = $conn->query($cars_sql);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $discount_percentage = $_POST['discount_percentage'];
    $user_type = $_POST['user_type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $status = $_POST['status'];
    $car_id = $_POST['car_id'] ?: null;

    $sql = "INSERT INTO offers (title, description, discount_percentage, user_type, start_date, end_date, status, car_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdssssi", $title, $description, $discount_percentage, $user_type, $start_date, $end_date, $status, $car_id);

    if ($stmt->execute()) {
        $success_message = "Offer created successfully!";
    } else {
        $error_message = "Error creating offer: " . $conn->error;
    }
}

// Fetch all offers with car details
$sql = "SELECT o.*, c.name as car_name, c.model as car_model 
        FROM offers o 
        LEFT JOIN cars c ON o.car_id = c.id 
        ORDER BY o.id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Premium Offers - Admin Panel</title>
    <!-- google fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@100..900&display=swap" rel="stylesheet">
    <!-- font awesome -->
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <!-- bootstrap css -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <!-- Include CSS stylesheets -->
    <link rel="stylesheet" href="../css/manage-offers.css">
    <link rel="stylesheet" href="../css/AdminDashboard.css">
    <link rel="stylesheet" href="../css/general.css">
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/main-content.css">
    <link rel="stylesheet" href="../css/sort-filter.css">
    <link rel="stylesheet" href="../css/offers.css">
    <link rel="stylesheet" href="../css/buttons.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="../css/index.css">
    <style>
    
    </style>
</head>
<body>

<!-- Header Section -->
<header class="navbar navbar-expand-lg bg-body-tertiary d-flex justify-content-center align-items-center">
    <nav class="container-fluid d-flex justify-content-center align-items-center">
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <h1 class="navbar-brand">Manage Premium Offers</h1>
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="DashboardAdmin.php">Back to Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="../index.php">Back to Home</a></li>
                <li class="nav-item"><a class="nav-link" href="../Login-Signup-Logout/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>
</header>

<main>
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
    <?php elseif (isset($error_message)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <!-- Tab Navigation -->
    <div class="tab-navigation">
        <a href="#create-offer" onclick="showTab('create-offer')">Create Offer</a>
        <a href="#offers-list" onclick="showTab('offers-list')">Current Offers</a>
    </div>

    <!-- Create Offer Form -->
    <section id="create-offer" class="tab-content active ">
        <form method="POST">
            <h3>Create New Offer</h3>
            <div class="form-group">
                <label for="title">Offer Title</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" required></textarea>
            </div>
            <div class="form-group">
                <label for="discount_percentage">Discount Percentage</label>
                <input type="number" id="discount_percentage" name="discount_percentage" min="0" max="100" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="user_type">User Type</label>
                <select id="user_type" name="user_type" required>
                    <option value="client">Regular Users</option>
                    <option value="premium">Premium Users</option>
                    <option value="all">All Users</option>
                </select>
            </div>
            <div class="form-group">
                <label for="start_date">Start Date</label>
                <input type="date" id="start_date" name="start_date" required>
            </div>
            <div class="form-group">
                <label for="end_date">End Date</label>
                <input type="date" id="end_date" name="end_date" required>
            </div>
            <div class="form-group">
                <label for="car_id">Select a Car</label>
                <select id="car_id" name="car_id">
                    <option disabled selected>Select a car</option>
                    <optgroup label="All cars">
                        <?php while ($car = $cars_result->fetch_assoc()): ?>
                            <option value="<?= $car['id'] ?>">
                                <?= htmlspecialchars($car['name'] . ' ' . $car['model']) ?>
                            </option>
                        <?php endwhile; ?>
                    </optgroup>
                </select>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn">Create Offer</button>
        </form>
    </section>

    <!-- Current Offers Table -->
    <section id="offers-list" class="tab-content">
        <h3>Current Offers</h3>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Discount</th>
                    <th>User Type</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Car</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($offer = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($offer['title']) ?></td>
                        <td><?= htmlspecialchars($offer['description']) ?></td>
                        <td><?= $offer['discount_percentage'] ?>%</td>
                        <td><span class="badge type-<?= $offer['user_type'] ?>"><?= ucfirst($offer['user_type']) ?></span></td>
                        <td><?= $offer['start_date'] ?></td>
                        <td><?= $offer['end_date'] ?></td>
                        <td><?= $offer['car_name'] ? htmlspecialchars($offer['car_name'] . ' ' . $offer['car_model']) : 'All Cars' ?></td>
                        <td><span class="badge status-<?= $offer['status'] ?>"><?= ucfirst($offer['status']) ?></span></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </section>
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
    function showTab(tabId) {
        document.querySelectorAll('.tab-content').forEach(function(tab) {
            tab.classList.remove('active');
        });
        document.getElementById(tabId).classList.add('active');
    }
</script>
<!-- bootstrap js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
</body>
</html>
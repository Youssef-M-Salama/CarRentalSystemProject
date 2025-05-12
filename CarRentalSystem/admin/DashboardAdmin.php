<?php
session_start();
require_once '../includes/config.php';

// Get pending rental requests count
$pending_rentals_sql = "SELECT COUNT(*) as count FROM rental_requests WHERE status = 'pending'";
$pending_rentals_result = mysqli_query($conn, $pending_rentals_sql);
$pending_rentals = mysqli_fetch_assoc($pending_rentals_result)['count'];

// Get rented cars count
$rented_cars_sql = "SELECT COUNT(*) as count FROM cars WHERE status = 'rented'";
$rented_cars_result = mysqli_query($conn, $rented_cars_sql);
$rented_cars = mysqli_fetch_assoc($rented_cars_result)['count'];

// Get pending role change requests count
$role_requests_sql = "SELECT COUNT(*) as count FROM role_change_requests WHERE status = 'pending'";
$role_requests_result = mysqli_query($conn, $role_requests_sql);
$role_requests = mysqli_fetch_assoc($role_requests_result)['count'];

// Check if the user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../Login-Signup-Logout/login.php");
    exit;
}

// Handle Add Car Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_car'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $model = mysqli_real_escape_string($conn, $_POST['model']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $price_per_day = floatval($_POST['price_per_day']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);

    // Handle image upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../images/';
        $uploadFile = $uploadDir . basename($_FILES['image']['name']);

        // Create the directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Move the uploaded file to the images directory
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            $image = htmlspecialchars(basename($_FILES['image']['name']));
        } else {
            die("Error uploading image.");
        }
    } else {
        die("No image uploaded or an error occurred.");
    }

    // Insert car into the database
    $query = "INSERT INTO cars (name, model, type, price_per_day, image, status, category) 
              VALUES ('$name', '$model', '$type', '$price_per_day', '$image', '$status', '$category')";
    mysqli_query($conn, $query);

    // Redirect back to the admin dashboard
    header("Location: DashboardAdmin.php");
    exit;
}

// Handle Make Available Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['make_available'])) {
    $car_id = intval($_POST['car_id']);
    $query = "UPDATE cars SET status = 'available' WHERE id = $car_id";
    mysqli_query($conn, $query);
    header("Location: DashboardAdmin.php");
    exit;
}

// Fetch All Cars
$query = "SELECT * FROM cars";
$cars = mysqli_query($conn, $query);

// Fetch All Users
$query = "SELECT * FROM users";
$users = mysqli_query($conn, $query);

// Fetch All Rental Requests with user and car details
$query = "SELECT r.*, u.username, c.name as car_name, c.model, c.image, c.category
          FROM rental_requests r 
          JOIN users u ON r.user_id = u.id 
          JOIN cars c ON r.car_id = c.id 
          ORDER BY r.created_at DESC";
$rental_requests = mysqli_query($conn, $query);

// Fetch Unavailable Cars (status = 'rented')
$query = "SELECT * FROM cars WHERE status = 'rented'";
$unavailable_cars = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- google fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@100..900&display=swap" rel="stylesheet">
    <!-- font awesome -->
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <!-- bootstrap css -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <!-- Include CSS stylesheets -->
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
            <h1 class="navbar-brand">Admin Dashboard</h1>
            <ul class="navbar-nav">
                <li class="nav-item"><a  class="nav-link" href="../index.php">Back to Home</a></li>
                <li class="nav-item"><a  class="nav-link" href="../Login-Signup-Logout/logout.php">Logout</a></li>
            </ul>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message">
                <?php echo $_SESSION['error']; ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <!-- Tab Navigation -->
        <div class="tab-navigation">
            <a href="#add-car" onclick="showTab('add-car')">Add Car</a>
            <a href="#car-list" onclick="showTab('car-list')">Car List</a>
            <a href="#user-management" onclick="showTab('user-management')">User Management</a>
            <a href="#rental-requests" onclick="showTab('rental-requests')">
                Rental Requests 
                <?php if ($pending_rentals > 0): ?>
                    <span class="notification-badge"><?= $pending_rentals ?></span>
                <?php endif; ?>
            </a>
            <a href="#retrieve-cars" onclick="showTab('retrieve-cars')">
                Retrieve Cars 
                <?php if ($rented_cars > 0): ?>
                    <span class="notification-badge"><?= $rented_cars ?></span>
                <?php endif; ?>
            </a>
            <a href="manage_offers.php" class="offers-btn">
                Manage Premium Offers
                <?php 
                // Get pending offers count
                $pending_offers_sql = "SELECT COUNT(*) as count FROM offers WHERE status = 'pending'";
                $pending_offers_result = mysqli_query($conn, $pending_offers_sql);
                $pending_offers = mysqli_fetch_assoc($pending_offers_result)['count'];
                if ($pending_offers > 0): ?>
                    <span class="notification-badge"><?= $pending_offers ?></span>
                <?php endif; ?>
            </a>
            <a href="admin_deal_with_request_to_change_role.php">
                Role Change Requests 
                <?php if ($role_requests > 0): ?>
                    <span class="notification-badge"><?= $role_requests ?></span>
                <?php endif; ?>
            </a>
        </div>

        <!-- Add Car Form -->
        <section id="add-car" class="tab-content active">
            <form method="POST" action="DashboardAdmin.php" enctype="multipart/form-data">
                <h3>Add New Car</h3>
                <input type="text" name="name" placeholder="Car Name" required>
                <input type="text" name="model" placeholder="Model" required>
                <select name="type" required>
                    <option value="Sedan">Sedan</option>
                    <option value="SUV">SUV</option>
                    <option value="Crossover">Crossover</option>
                </select>
                <input type="number" step="0.01" name="price_per_day" placeholder="Price Per Day" required min='0'>
                <select name="status" required>
                    <option value="available">Available</option>
                    <option value="not available">Not Available</option>
                </select>
                <select name="category" required>
                    <option value="free">Free</option>
                    <option value="premium">Premium</option>
                </select>
                <label for="image">Upload Image:</label>
                <input type="file" name="image" id="image" accept="image/*" required>
                <button type="submit" name="add_car">Add Car</button>
            </form>
        </section>

        <!-- Car List -->
        <section id="car-list" class="tab-content">
            <h3>All Cars</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Model</th>
                        <th>Type</th>
                        <th>Price Per Day</th>
                        <th>Status</th>
                        <th>Image</th>
                        <th>Category</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($car = mysqli_fetch_assoc($cars)): ?>
                        <tr>
                            <td><?= $car['id'] ?></td>
                            <td><?= htmlspecialchars($car['name']) ?></td>
                            <td><?= htmlspecialchars($car['model']) ?></td>
                            <td><?= htmlspecialchars($car['type']) ?></td>
                            <td>$<?= number_format($car['price_per_day'], 2) ?></td>
                            <td><?= htmlspecialchars($car['status']) ?></td>
                            <td>
                                <?php if (!empty($car['image'])): ?>
                                    <img src="../images/<?= htmlspecialchars($car['image']) ?>" 
                                        alt="<?= htmlspecialchars($car['name']) ?>" 
                                        width="50">
                                <?php else: ?>
                                    No Image
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($car['category']) ?></td>
                            <td>
                                <form class="delete-form" method="POST" action="delete_car.php">
                                    <input type="hidden" name="car_id" value="<?= $car['id'] ?>">
                                    <button type="submit" onclick="return confirm('Are you sure you want to delete this car?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>

        <!-- Retrieve Cars Section -->
        <section id="retrieve-cars" class="tab-content">
            <h3>Retrieve Unavailable Cars</h3>
            <?php if (mysqli_num_rows($unavailable_cars) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Model</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Category</th>
                            <th>Image</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($car = mysqli_fetch_assoc($unavailable_cars)): ?>
                            <tr>
                                <td><?= $car['id'] ?></td>
                                <td><?= htmlspecialchars($car['name']) ?></td>
                                <td><?= htmlspecialchars($car['model']) ?></td>
                                <td><?= htmlspecialchars($car['type']) ?></td>
                                <td><?= htmlspecialchars($car['status']) ?></td>
                                <td><?= htmlspecialchars($car['category']) ?></td>
                                <td>
                                    <?php if (!empty($car['image'])): ?>
                                        <img src="../images/<?= htmlspecialchars($car['image']) ?>" 
                                            alt="<?= htmlspecialchars($car['name']) ?>" 
                                            width="50">
                                    <?php else: ?>
                                        No Image
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" action="DashboardAdmin.php">
                                        <input type="hidden" name="car_id" value="<?= $car['id'] ?>">
                                        <button type="submit" name="make_available" class="btn-approve">Make Available</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No unavailable cars found.</p>
            <?php endif; ?>
        </section>

        <!-- User Management -->
        <section id="user-management" class="tab-content">
            <h3>User Management</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = mysqli_fetch_assoc($users)): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['role']) ?></td>
                            <td>
                                <a href="edit_user.php?user_id=<?= $user['id'] ?>" class="btn-approve">Edit</a>
                                <form method="POST" action="delete_user.php" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <button type="submit" onclick="return confirm('Are you sure you want to delete this user?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>

        <!-- Rental Requests -->
        <section id="rental-requests" class="tab-content">
            <h3>Rental Requests</h3>
            <?php if ($rental_requests && mysqli_num_rows($rental_requests) > 0): ?>
                <div class="rental-requests-container">
                    <?php while ($request = mysqli_fetch_assoc($rental_requests)): ?>
                        <div class="rental-request">
                            <div class="rental-request-header">
                                <h4>Request #<?= $request['id'] ?></h4>
                                <?php 
                                    $statusClass = '';
                                    switch($request['status']) {
                                        case 'pending':
                                            $statusClass = 'status-pending';
                                            break;
                                        case 'approved':
                                            $statusClass = 'status-approved';
                                            break;
                                        case 'rejected':
                                            $statusClass = 'status-rejected';
                                            break;
                                    }
                                ?>
                                <span class="<?= $statusClass ?>"><?= ucfirst($request['status']) ?></span>
                            </div>
                            <div class="rental-request-content">
                                <img src="../images/<?= htmlspecialchars($request['image']) ?>" 
                                    alt="<?= htmlspecialchars($request['car_name']) ?>" 
                                    class="rental-car-image">
                                <div class="rental-info">
                                    <p><strong>User:</strong> <?= htmlspecialchars($request['username']) ?></p>
                                    <p><strong>Car:</strong> <?= htmlspecialchars($request['car_name']) ?> (<?= htmlspecialchars($request['model']) ?>)</p>
                                    <p><strong>Period:</strong> <?= date('M d, Y', strtotime($request['start_date'])) ?> to <?= date('M d, Y', strtotime($request['end_date'])) ?></p>
                                    <p><strong>Category:</strong> <?= htmlspecialchars($request['category']) ?></p>
                                    <p><strong>Requested on:</strong> <?= date('M d, Y H:i', strtotime($request['created_at'])) ?></p>
                                    
                                    <?php if ($request['status'] === 'pending'): ?>
                                        <div class="rental-actions">
                                            <form method="POST" action="approve_request.php">
                                                <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                                <button type="submit" class="btn-approve">Approve</button>
                                            </form>
                                            <form method="POST" action="reject_request.php">
                                                <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                                <button type="submit" class="btn-reject">Reject</button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>No rental requests found.</p>
            <?php endif; ?>
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
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(function(tab) {
                tab.classList.remove('active');
            });

            // Show the selected tab content
            document.getElementById(tabId).classList.add('active');
        }
    </script>
    <!-- bootstrap js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
</body>
</html>

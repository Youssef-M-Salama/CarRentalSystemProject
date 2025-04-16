<?php
session_start();
include "../includes/config.php";

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
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

    // Handle image upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../images/';
        $uploadFile = $uploadDir . basename($_FILES['image']['name']);

        // Create the directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true); // Create directory with write permissions
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
    $query = "INSERT INTO cars (name, model, type, price_per_day, image, status) 
              VALUES ('$name', '$model', '$type', '$price_per_day', '$image', '$status')";
    mysqli_query($conn, $query);

    // Redirect back to the admin dashboard
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
$query = "SELECT r.*, u.username, c.name as car_name, c.model, c.image 
          FROM rental_requests r 
          JOIN users u ON r.user_id = u.id 
          JOIN cars c ON r.car_id = c.id 
          ORDER BY r.created_at DESC";
$rental_requests = mysqli_query($conn, $query);
?>

<?php
// Handle Make Available Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['make_available'])) {
    $car_id = intval($_POST['car_id']);
    $query = "UPDATE cars SET status = 'available' WHERE id = $car_id";
    mysqli_query($conn, $query);
    header("Location: DashboardAdmin.php");
    exit;
}

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
    
    <link rel="stylesheet" href="../css/general.css">
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/main-content.css">

    <style>
        /* Tab Navigation Styles */
        .tab-navigation {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .tab-navigation a {
            text-decoration: none;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
        }
        .tab-navigation a:hover {
            background-color: #0056b3;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }

        /* Rental Request Styles */
        .rental-request {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .rental-request-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .rental-request-content {
            display: flex;
            gap: 20px;
        }
        .rental-car-image {
            width: 120px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }
        .rental-info {
            flex-grow: 1;
        }
        .rental-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        .status-pending {
            color: #856404;
            background-color: #fff3cd;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.9em;
        }
        .status-approved {
            color: #155724;
            background-color: #d4edda;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.9em;
        }
        .status-rejected {
            color: #721c24;
            background-color: #f8d7da;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.9em;
        }
        .btn-approve {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-reject {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <header>
        <h1>Admin Dashboard</h1>
        <nav>
            <ul>
                <li><a href="../index.php">Back to Home</a></li>
                <li><a href="../Login-Signup-Logout/logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <!-- Main Content -->
    <main>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message" style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                <?php echo $_SESSION['error']; ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <!-- Tab Navigation -->
        <div class="tab-navigation">
            <a href="#add-car" onclick="showTab('add-car')">Add Car</a>
            <a href="#car-list" onclick="showTab('car-list')">Car List</a>
            <a href="#user-management" onclick="showTab('user-management')">User Management</a>
            <a href="#rental-requests" onclick="showTab('rental-requests')">Rental Requests</a>
            <a href="#retrieve-cars" onclick="showTab('retrieve-cars')">Retrieve Cars</a>
        </div>

        <!-- Add Car Form -->
        <section id="add-car" class="tab-content active">
            <h3>Add New Car</h3>
            <form method="POST" action="DashboardAdmin.php" enctype="multipart/form-data">
                <input type="text" name="name" placeholder="Car Name" required>
                <input type="text" name="model" placeholder="Model" required>
                <!-- <input type="text" name="type" placeholder="Type" required> convert to select from menu instead of typing  -->
                 <select name = "type" required>
                    <!-- <option value="" >Type</option> hance it later -->
                 <option value="Sedan">Sedan</option>
                 <option value="SUV">SUV</option>
                 <option value="Crossover">Crossover</option>
                </select>

                <input type="number" step="0.01" name="price_per_day" placeholder="Price Per Day" required>
                <select name="status" required>
                    <option value="available">Available</option>
                    <option value="not available">Not Available</option>
                </select>
                <!-- File Input for Image Upload -->
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
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($car = mysqli_fetch_assoc($cars)) { ?>
                        <tr>
                            <td><?php echo $car['id']; ?></td>
                            <td><?php echo htmlspecialchars($car['name']); ?></td>
                            <td><?php echo htmlspecialchars($car['model']); ?></td>
                            <td><?php echo htmlspecialchars($car['type']); ?></td>
                            <td><?php echo '$' . number_format($car['price_per_day'], 2); ?></td>
                            <td><?php echo htmlspecialchars($car['status']); ?></td>
                            <td>
                                <?php if (!empty($car['image'])): ?>
                                    <img src="../images/<?php echo htmlspecialchars($car['image']); ?>" alt="<?php echo htmlspecialchars($car['name']); ?>" width="50">
                                <?php else: ?>
                                    No Image
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" action="delete_car.php">
                                    <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
                                    <button type="submit" onclick="return confirm('Are you sure you want to delete this car?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </section>







          <!-- New Retrieve Cars Section -->
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
                                <td>
                                <?php if (!empty($car['image'])): ?>
                                    <img src="../images/<?php echo htmlspecialchars($car['image']); ?>" alt="<?php echo htmlspecialchars($car['name']); ?>" width="50">
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
                    <?php while ($user = mysqli_fetch_assoc($users)) { ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td>
                                <!-- Edit Button -->
                                <a href="edit_user.php?user_id=<?php echo $user['id']; ?>" class="btn-edit">Edit</a>
                                <!-- Delete Button -->
                                <form method="POST" action="delete_user.php" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" onclick="return confirm('Are you sure you want to delete this user?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
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
                                <h4>Request #<?php echo $request['id']; ?></h4>
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
                                <span class="<?php echo $statusClass; ?>"><?php echo ucfirst($request['status']); ?></span>
                            </div>
                            <div class="rental-request-content">
                                <img src="../images/<?php echo htmlspecialchars($request['image']); ?>" alt="<?php echo htmlspecialchars($request['car_name']); ?>" class="rental-car-image">
                                <div class="rental-info">
                                    <p><strong>User:</strong> <?php echo htmlspecialchars($request['username']); ?></p>
                                    <p><strong>Car:</strong> <?php echo htmlspecialchars($request['car_name']); ?> (<?php echo htmlspecialchars($request['model']); ?>)</p>
                                    <p><strong>Period:</strong> <?php echo date('M d, Y', strtotime($request['start_date'])); ?> to <?php echo date('M d, Y', strtotime($request['end_date'])); ?></p>
                                    <p><strong>Requested on:</strong> <?php echo date('M d, Y H:i', strtotime($request['created_at'])); ?></p>
                                    
                                    <?php if ($request['status'] === 'pending'): ?>
                                        <div class="rental-actions">
                                            <form method="POST" action="approve_request.php">
                                                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                <button type="submit" class="btn-approve">Approve</button>
                                            </form>
                                            <form method="POST" action="reject_request.php">
                                                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
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

    <!-- JavaScript for Tab Navigation -->
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
</body>
</html>

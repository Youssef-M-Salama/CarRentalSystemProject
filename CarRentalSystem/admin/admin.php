<?php
session_start();
include "../includes/config.php";

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
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
    header("Location: admin.php");
    exit;
}

// Fetch All Cars
$query = "SELECT * FROM cars";
$cars = mysqli_query($conn, $query);

// Fetch All Users
$query = "SELECT * FROM users";
$users = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../styles.css">
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
    </style>
</head>
<body>
    <!-- Header Section -->
    <header>
        <h1>Admin Dashboard</h1>
        <nav>
            <ul>
                <li><a href="../users/index.php">Back to Home</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <!-- Main Content -->
    <main>
        <!-- Tab Navigation -->
        <div class="tab-navigation">
            <a href="#add-car" onclick="showTab('add-car')">Add Car</a>
            <a href="#car-list" onclick="showTab('car-list')">Car List</a>
            <a href="#user-management" onclick="showTab('user-management')">User Management</a>
        </div>

        <!-- Add Car Form -->
        <section id="add-car" class="tab-content active">
            <h3>Add New Car</h3>
            <form method="POST" action="admin.php" enctype="multipart/form-data">
                <input type="text" name="name" placeholder="Car Name" required>
                <input type="text" name="model" placeholder="Model" required>
                <input type="text" name="type" placeholder="Type" required>
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

        <!-- User Management -->
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
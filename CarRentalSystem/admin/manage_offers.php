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
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .admin-nav {
            margin-bottom: 20px;
        }
        .admin-nav a {
            margin-right: 15px;
            text-decoration: none;
            color: #333;
            padding: 8px 15px;
            border-radius: 5px;
            background: #f0f0f0;
            transition: background 0.3s;
        }
        .admin-nav a:hover {
            background: #e0e0e0;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
        }
        .form-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #218838;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f8f9fa;
            font-weight: bold;
        }
        .badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8em;
        }
        .type-client {
            background: #6c757d;
            color: white;
        }
        .type-premium {
            background: #ffc107;
            color: black;
        }
        .type-all {
            background: #17a2b8;
            color: white;
        }
        .status-active {
            background: #28a745;
            color: white;
        }
        .status-inactive {
            background: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Manage Premium Offers</h1>
        
        <div class="admin-nav">
            <a href="DashboardAdmin.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            <a href="../index.php"><i class="fas fa-home"></i> Back to Home</a>
            <a href="../Login-Signup-Logout/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <div class="form-section">
            <h2>Create New Offer</h2>
            <form method="POST">
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
                    <label for="car_id">Select a Car </label>
                    <select id="car_id" name="car_id">
                        <!-- Disabled placeholder option (outside optgroup) -->
                        <option disabled selected>Select a car</option>
                        
                        <!-- Grouped options -->
                        <optgroup label="All cars">
                        <?php while ($car = $cars_result->fetch_assoc()): ?>
                            <option value="<?php echo $car['id']; ?>">
                            <?php echo htmlspecialchars($car['name'] . ' ' . $car['model']); ?>
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
        </div>

        <div class="offers-list">
            <h2>Current Offers</h2>
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
                        <td><?php echo htmlspecialchars($offer['title']); ?></td>
                        <td><?php echo htmlspecialchars($offer['description']); ?></td>
                        <td><?php echo $offer['discount_percentage']; ?>%</td>
                        <td>
                            <span class="badge type-<?php echo $offer['user_type']; ?>">
                                <?php echo ucfirst($offer['user_type']); ?>
                            </span>
                        </td>
                        <td><?php echo $offer['start_date']; ?></td>
                        <td><?php echo $offer['end_date']; ?></td>
                        <td>
                            <?php if ($offer['car_id']): ?>
                                <?php echo htmlspecialchars($offer['car_name'] . ' ' . $offer['car_model']); ?>
                            <?php else: ?>
                                All Cars
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge status-<?php echo $offer['status']; ?>">
                                <?php echo ucfirst($offer['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html> 
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

    <!-- Styles -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css ">
    <link rel="stylesheet" href="../css/AdminDashboard.css">
    <link rel="stylesheet" href="../css/header.css">

    <style>
        main {
            padding: 40px;
        }

        .form-section {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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
            background-color: #9AA6B2;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .btn:hover {
            background-color: #BCCCDC;
            transform: translateY(-1px);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            font-family: "League Spartan", sans-serif;
            font-size: 0.95rem;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #9AA6B2;
            color: white;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
        }

        .type-client {
            background: #fff3e0;
            color: #f57c00;
        }

        .type-premium {
            background: #e3f2fd;
            color: #1976d2;
        }

        .type-all {
            background: #f1f1f1;
            color: #333;
        }

        .status-active {
            background: #ecffe8;
            color: #1e7e34;
        }

        .status-inactive {
            background: #ffe6e7;
            color: #99151b;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .alert-success {
            background: #ecffe8;
            color: #1e7e34;
        }

        .alert-error {
            background: #ffe6e7;
            color: #99151b;
        }

        .tab-navigation {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            font-family: "League Spartan", sans-serif;
        }

        .tab-navigation a {
            text-decoration: none;
            padding: 10px 15px;
            background-color: #9AA6B2;
            color: white;
            border-radius: 5px;
            position: relative;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .tab-navigation a:hover {
            background-color: #BCCCDC;
            transform: translateY(-2px);
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
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
    <section id="create-offer" class="tab-content active">
        <h3>Create New Offer</h3>
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

<script>
    function showTab(tabId) {
        document.querySelectorAll('.tab-content').forEach(function(tab) {
            tab.classList.remove('active');
        });
        document.getElementById(tabId).classList.add('active');
    }
</script>

</body>
</html>
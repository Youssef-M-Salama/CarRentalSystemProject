<?php
session_start();
require_once 'includes/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: Login-Signup-Logout/login.php');
    exit();
}

// Get user role and name safely
$user_role = isset($_SESSION['user']['role']) ? $_SESSION['user']['role'] : 'client';
$user_name = isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : 'User';
$user_email = isset($_SESSION['user']['email']) ? $_SESSION['user']['email'] : '';

// Fetch active offers based on user role
$current_date = date('Y-m-d');
$sql = "SELECT o.*, c.name as car_name, c.model as car_model, c.image as car_image, c.type as car_type, c.price_per_day
        FROM offers o 
        JOIN cars c ON o.car_id = c.id 
        WHERE o.status = 'active' 
        AND o.start_date <= ? 
        AND o.end_date >= ?
        AND (o.user_type = ? OR o.user_type = 'all')
        AND c.status = 'available'
        ORDER BY o.id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $current_date, $current_date, $user_role);
$stmt->execute();
$result = $stmt->get_result();

// Debug information
echo "<!-- Debug Query Info:";
echo "\nCurrent Date: " . $current_date;
echo "\nSQL Query: " . $sql;
echo "\nNumber of Offers Found: " . $result->num_rows;
echo "\n-->";

// If no offers found, check why
if ($result->num_rows == 0) {
    // Check if there are any offers at all
    $check_sql = "SELECT COUNT(*) as total FROM offers";
    $total_offers = $conn->query($check_sql)->fetch_assoc()['total'];
    
    // Check if there are any active offers
    $active_sql = "SELECT COUNT(*) as active FROM offers WHERE status = 'active'";
    $active_offers = $conn->query($active_sql)->fetch_assoc()['active'];
    
    // Check if there are any offers with valid dates
    $date_sql = "SELECT COUNT(*) as valid FROM offers WHERE start_date <= ? AND end_date >= ?";
    $date_stmt = $conn->prepare($date_sql);
    $date_stmt->bind_param("ss", $current_date, $current_date);
    $date_stmt->execute();
    $valid_dates = $date_stmt->get_result()->fetch_assoc()['valid'];
    
    echo "<!-- Debug No Offers Info:";
    echo "\nTotal Offers in Database: " . $total_offers;
    echo "\nActive Offers: " . $active_offers;
    echo "\nOffers with Valid Dates: " . $valid_dates;
    echo "\n-->";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Special Offers - Car Rental System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .offer-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            background: white;
            overflow: hidden;
        }
        .offer-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        .offer-header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 20px;
            position: relative;
        }
        .offer-header::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 0;
            right: 0;
            height: 20px;
            background: white;
            border-radius: 0 0 15px 15px;
        }
        .discount-badge {
            background: #28a745;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
            margin: 10px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .car-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin: 10px 0;
            border: 1px solid #e9ecef;
        }
        .validity {
            color: #6c757d;
            font-size: 0.9rem;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 10px;
            margin: 10px 0;
        }
        .section-title {
            color: #007bff;
            margin-bottom: 30px;
            font-weight: bold;
            text-align: center;
            position: relative;
            padding-bottom: 15px;
        }
        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: linear-gradient(90deg, #007bff, #0056b3);
        }
        .no-offers {
            text-align: center;
            padding: 50px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .car-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 15px;
            transition: transform 0.3s ease;
        }
        .car-image:hover {
            transform: scale(1.05);
        }
        .price-info {
            background: #e9ecef;
            padding: 10px;
            border-radius: 10px;
            margin: 10px 0;
        }
        .original-price {
            text-decoration: line-through;
            color: #dc3545;
        }
        .discounted-price {
            color: #28a745;
            font-weight: bold;
            font-size: 1.2em;
        }
        .rent-button {
            background: linear-gradient(135deg, #28a745 0%, #218838 100%);
            border: none;
            padding: 12px 25px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .rent-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        }
        .user-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255, 255, 255, 0.2);
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8em;
        }
        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .user-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #007bff;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .user-info {
            display: flex;
            flex-direction: column;
        }
        .user-name {
            font-size: 0.9rem;
            font-weight: bold;
        }
        .user-role {
            font-size: 0.7rem;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Car Rental System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="my_rental.php">My Rentals</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="offers.php">Special Offers</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about us.html">About Us</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <div class="user-profile">
                            <div class="user-avatar">
                                <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                            </div>
                            <div class="user-info">
                                <span class="user-name"><?php echo htmlspecialchars($user_name); ?></span>
                                <span class="user-role"><?php echo ucfirst($user_role); ?></span>
                            </div>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="Login-Signup-Logout/logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5 mb-5">
        <h2 class="text-center section-title">Special Offers</h2>
        
        <?php if ($result->num_rows > 0): ?>
            <div class="row">
                <?php while ($offer = $result->fetch_assoc()): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card offer-card h-100">
                            <div class="offer-header">
                                <span class="user-badge">
                                    <i class="fas fa-users"></i> <?php echo ucfirst($offer['user_type']); ?>
                                </span>
                                <h4 class="card-title mb-0"><?php echo htmlspecialchars($offer['title']); ?></h4>
                            </div>
                            <div class="card-body">
                                <img src="images/<?php echo htmlspecialchars($offer['car_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($offer['car_name']); ?>" 
                                     class="car-image">
                                
                                <div class="car-info">
                                    <h5><?php echo htmlspecialchars($offer['car_name'] . ' ' . $offer['car_model']); ?></h5>
                                    <p class="mb-1"><strong>Type:</strong> <?php echo $offer['car_type']; ?></p>
                                    
                                    <div class="price-info">
                                        <p class="mb-1">
                                            <span class="original-price">$<?php echo number_format($offer['price_per_day'], 2); ?>/day</span>
                                            <span class="discounted-price">$<?php echo number_format($offer['price_per_day'] * (1 - ($offer['discount_percentage'] / 100)), 2); ?>/day</span>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="discount-badge">
                                    <i class="fas fa-percentage"></i> <?php echo $offer['discount_percentage']; ?>% OFF
                                </div>
                                
                                <p class="card-text"><?php echo htmlspecialchars($offer['description']); ?></p>
                                
                                <div class="validity">
                                    <i class="far fa-calendar-alt"></i> 
                                    Valid from <?php echo date('M d, Y', strtotime($offer['start_date'])); ?> 
                                    to <?php echo date('M d, Y', strtotime($offer['end_date'])); ?>
                                </div>
                                
                                <div class="mt-3">
                                    <a href="rent_car.php?car_id=<?php echo $offer['car_id']; ?>&offer_id=<?php echo $offer['id']; ?>" 
                                       class="btn btn-success rent-button w-100">
                                        <i class="fas fa-car"></i> Rent Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-offers">
                <i class="fas fa-gift fa-3x mb-3" style="color: #007bff;"></i>
                <h4>No special offers available at the moment</h4>
                <p class="text-muted">Check back later for exclusive offers!</p>
                <?php if ($user_role === 'client'): ?>
                    <div class="mt-3">
                        <a href="premium/offers.php" class="btn btn-primary">
                            <i class="fas fa-crown"></i> View Premium Offers
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
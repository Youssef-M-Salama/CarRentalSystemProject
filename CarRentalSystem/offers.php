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
    <link rel="stylesheet" href="css/offers.css">
    <link rel="stylesheet" href="css/profile.css">
    <link rel="stylesheet" href="css/index.css">
    
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
        <li class="nav-item"><a class="nav-link" href="Login-Signup-Logout/logout.php">Logout</a></li>
        <li class="nav-item"><a class="nav-link active" aria-current="page" href="offers.php">Special Offers</a></li>
        <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="Login-Signup-Logout/signup.php">Sign Up</a></li>
            <li class="nav-item"><a class="nav-link" href="Login-Signup-Logout/login.php">Login</a></li>
        <?php endif; ?>
        
        <li class="nav-item"><a class="nav-link" href="about us.html">About Us</a></li>
        
        <li class="nav-item">
            <a href="profile.php" class=" nav-link profile-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
            </svg>
            </a>
        </li>
    </ul>
    </div>
    </nav>
</header>

    <div class="container mt-5 mb-5">
        <div class="row">
        <?php if ($result->num_rows > 0): ?>
                <?php while ($offer = $result->fetch_assoc()): ?>
                    <div class="col-md-4 mb-4">
                        <div class="offer-card">
                            <div class="offer-header">
                                <span class="user-badge">
                                    <i class="fas fa-users"></i> <?php echo ucfirst($offer['user_type']); ?>
                                </span>
                                <h4 class="card-title mb-0"><?php echo htmlspecialchars($offer['title']); ?></h4>
                            </div>
                            <div class="offer-card-body">
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
                                <?php echo $offer['discount_percentage']; ?>% OFF
                                </div>
                                
                                <p class="card-text"><?php echo htmlspecialchars($offer['description']); ?></p>
                                
                                <div class="validity"> 
                                    Valid from <?php echo date('M d, Y', strtotime($offer['start_date'])); ?> 
                                    to <?php echo date('M d, Y', strtotime($offer['end_date'])); ?>
                                </div>
                                
                                <div class="mt-3">
                                    <a href="rent_car.php?car_id=<?php echo $offer['car_id']; ?>&offer_id=<?php echo $offer['id']; ?>" 
                                    class="btn btn-success rent-button w-100">
                                    Rent Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
        <?php else: ?>
            <div class="no-offers">
                <h4>No special offers available at the moment</h4>
                <p class="text-muted">Check back later for exclusive offers!</p>
            </div>
        <?php endif; ?>
        </div>
    </div>
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

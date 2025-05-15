<?php
session_start();
require_once 'includes/config.php';
// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: Login-Signup-Logout/login.php');
    exit();
}
// Get car and offer details
$car_id = isset($_GET['car_id']) ? (int)$_GET['car_id'] : 0;
$offer_id = isset($_GET['offer_id']) ? (int)$_GET['offer_id'] : 0;
// Fetch car and offer details
$sql = "SELECT c.*, o.discount_percentage, o.title as offer_title 
        FROM cars c 
        LEFT JOIN offers o ON o.id = ? AND o.car_id = c.id 
        WHERE c.id = ? AND c.status = 'available'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $offer_id, $car_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header('Location: offers.php');
    exit();
}
$car = $result->fetch_assoc();
$has_offer = !empty($car['discount_percentage']);
$discounted_price = $has_offer ? 
    $car['price_per_day'] * (1 - ($car['discount_percentage'] / 100)) : 
    $car['price_per_day'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent Car - Car Rental System</title>    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/general.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/main-content.css">
    <link rel="stylesheet" href="css/forms.css">
    <link rel="stylesheet" href="css/rent_car.css">


 
</head>
<body>

<!-- Website Header Section -->
<header class="navbar navbar-expand-lg bg-body-tertiary d-flex justify-content-center align-items-center">
    <nav class="container-fluid d-flex justify-content-center align-items-center">
        <h1 class="navbar-brand">Car Rental Service</h1>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav">
                <li class="nav-item"><a href="index.php" class="nav-link">Home</a></li>
                <?php if (isset($_SESSION['user'])): ?>
                    <li class="nav-item"><a class="nav-link" href="my_rental.php">My Rentals</a></li>
                    <li class="nav-item"><a class="nav-link" href="Login-Signup-Logout/logout.php">Logout</a></li>
                    <li class="nav-item"><a class="nav-link" href="offers.php">Special Offers</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="Login-Signup-Logout/signup.php">Sign Up</a></li>
                    <li class="nav-item"><a class="nav-link" href="Login-Signup-Logout/login.php">Login</a></li>
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link" href="about us.html">About Us</a></li>
                <li class="nav-item">
                    <a href="profile.php" class="nav-link profile-link">
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
    <h2 class="text-center">Submit Rental Request</h2>
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="rental-request">
                <div class="d-flex align-items-center">
                    <img src="images/<?php echo htmlspecialchars($car['image']); ?>" 
                         alt="<?php echo htmlspecialchars($car['name']); ?>" 
                         class="car-image me-3">

                    <div class="flex-grow-1">
                        <h4><?php echo htmlspecialchars($car['name'] . ' (' . $car['model'] . ')'); ?></h4>
                        <p><strong>Type:</strong> <?php echo htmlspecialchars($car['type']); ?></p>
                        <p><strong>Category:</strong> <?php echo htmlspecialchars($car['category']); ?></p>
                        <p><strong>Price/Day:</strong> $<?php echo number_format($discounted_price, 2); ?></p>
                    </div>
                </div>

                <form action="process_rental.php" method="POST" class="mt-3">
                    <input type="hidden" name="car_id" value="<?php echo $car_id; ?>">
                    <input type="hidden" name="offer_id" value="<?php echo $offer_id; ?>">

                    <div class="mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control date-picker" id="start_date" name="start_date"
                               min="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control date-picker" id="end_date" name="end_date"
                               min="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="total_price" class="form-label" >Estimated Total Price</label>
                        <input type="text" class="form-control" id="total_price" readonly>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-success submit-button">
                            <i class="fas fa-check"></i> Submit Rental Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Footer Section -->
<footer>
    <div class="footer-container">
        <div class="footer-section">
            <h3>Contact Us</h3>
            <a href="mailto:info@carrentalservice.com">Email: info@carrentalservice.com</a>
            <a href="tel:01234567890">Phone: 01234567890</a>
        </div>
        <div class="footer-section">
            <h3>Follow Us</h3>
            <ul class="social-links">
                <li><a href="#"><i class="fab fa-facebook"></i></a></li>
                <li><a href="#"><i class="fa-brands fa-github"></i></a></li>
                <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                <li><a href="#"><i class="fab fa-linkedin"></i></a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h3>Subscribe</h3>
            <form>
                <input type="email" placeholder="Enter your email" required>
                <button type="submit">Subscribe</button>
            </form>
        </div>
    </div>
    <div class="copyright">
        <p>&copy; 2025 Car Rental Service. All rights reserved.</p>
    </div>
</footer>

<script>
    // Calculate total price when dates change
    document.getElementById('start_date').addEventListener('change', calculateTotal);
    document.getElementById('end_date').addEventListener('change', calculateTotal);

    function calculateTotal() {
        const startDate = new Date(document.getElementById('start_date').value);
        const endDate = new Date(document.getElementById('end_date').value);
        if (startDate && endDate && startDate <= endDate) {
            const days = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
            const pricePerDay = <?php echo $discounted_price; ?>;
            const total = days * pricePerDay;
            document.getElementById('total_price').value = '$' + total.toFixed(2);
        } else {
            document.getElementById('total_price').value = '';
        }
    }
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap @5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
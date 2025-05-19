<?php
session_start();
include('includes/config.php');

// التحقق من وجود car_id في الرابط
if (isset($_GET['car_id']) && is_numeric($_GET['car_id'])) {
    $carId = intval($_GET['car_id']);

    // استعلام جلب بيانات السيارة
    $query = "SELECT * FROM cars WHERE id = $carId";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $car = mysqli_fetch_assoc($result);
    } else {
        echo "<p style='color: red;'>Car not found.</p>";
        exit;
    }
} else {
    echo "<p style='color: red;'>Invalid request.</p>";
    exit;
}

// معالجة التقييم إذا تم إرساله
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating']) && isset($_SESSION['user']['id'])) {
    $rating = intval($_POST['rating']);
    $userId = $_SESSION['user']['id'];

    // التحقق من وجود إيجار مُعتمد للسيارة
    $rentalQuery = "SELECT id FROM rental_requests WHERE user_id = $userId AND car_id = $carId AND status = 'approved'";
    $rentalResult = mysqli_query($conn, $rentalQuery);

    if ($rentalResult && mysqli_num_rows($rentalResult) > 0) {
        $rental = mysqli_fetch_assoc($rentalResult);
        $rentalId = $rental['id'];

        // التحقق من صحة التقييم
        if ($rating >= 1 && $rating <= 5) {
            $ratingQuery = "INSERT INTO rating (car_id, user_id, rental_id, rating) VALUES ($carId, $userId, $rentalId, $rating)";
            if (mysqli_query($conn, $ratingQuery)) {
                echo "<p style='color: green;'>Rating submitted successfully!</p>";
            } else {
                echo "<p style='color: red;'>Error submitting rating: " . mysqli_error($conn) . "</p>";
            }
        } else {
            echo "<p style='color: red;'>Invalid rating value (1-5 only).</p>";
        }
    } else {
        echo "<p style='color: red;'>You must have an approved rental to rate this car.</p>";
    }
}

// حساب متوسط التقييم
$avgQuery = "SELECT AVG(rating) AS avg_rating FROM rating WHERE car_id = $carId";
$avgResult = mysqli_query($conn, $avgQuery);
$avgRow = mysqli_fetch_assoc($avgResult);
$averageRating = round($avgRow['avg_rating'] ?? 0, 1);

// جلب جميع التقييمات
$ratingsQuery = "SELECT * FROM rating WHERE car_id = $carId";
$ratingsResult = mysqli_query($conn, $ratingsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Car Details</title>
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
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/car-details.css">
</head>
<body>
        <!-- Website Header Section -->
<header class="navbar navbar-expand-lg bg-body-tertiary d-flex justify-content-center align-items-center">
    <nav class="container-fluid d-flex justify-content-center align-items-center">
    <h1 class="navbar-brand">Car Rental Service</h1>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav">
        <li class="nav-item"><a href="index.php" class="nav-link" >Back To Home</a></li>      
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

<main>
    <h2 class="fw-bold">Car Details: <?= htmlspecialchars($car['name']) ?> <?= htmlspecialchars($car['model']) ?></h2>

    <div class="car-details">
        <img src="<?= 'images/' . htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['name']) ?>">
        <div class="car-info">
            <p><strong>Type:</strong> <?= htmlspecialchars($car['type']) ?></p><hr>
            <p><strong>Price:</strong> $<?= number_format($car['price_per_day'], 2) ?> per day</p><hr>
            <p><strong>Status:</strong> <?= $car['status'] === 'available' ? 'Available' : 'Not Available' ?></p><hr>
            <p><strong>Category:</strong> <?= ucfirst(htmlspecialchars($car['category'])) ?></p><hr>

            <div class="rating-section">
                <strong>Ratings:</strong>
                <?php if ($ratingsResult && mysqli_num_rows($ratingsResult) > 0): ?>
                    <div class="rating-stars">
                        <?php
                        $fullStars = floor($averageRating);
                        $halfStar = ($averageRating - $fullStars >= 0.5) ? 1 : 0;
                        $emptyStars = 5 - $fullStars - $halfStar;
                        ?>
                        <?php for ($i = 0; $i < $fullStars; $i++): ?>
                            <i class="fas fa-star"></i>
                        <?php endfor; ?>
                        <?php if ($halfStar): ?>
                            <i class="fas fa-star-half-alt"></i>
                        <?php endif; ?>
                        <?php for ($i = 0; $i < $emptyStars; $i++): ?>
                            <i class="far fa-star"></i>
                        <?php endfor; ?>
                        <span><?= $averageRating ?>/5</span>
                    </div>
                <?php else: ?>
                    <p style="color: #006A71;">No ratings yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
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

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
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="css/general.css">
  <link rel="stylesheet" href="css/header.css">
  <link rel="stylesheet" href="css/sidebar.css">
  <link rel="stylesheet" href="css/main-content.css">
  <link rel="stylesheet" href="css/buttons.css">
  <link rel="stylesheet" href="css/footer.css">
  <link rel="stylesheet" href="css/forms.css">
  <link rel="stylesheet" href="css/sort-filter.css">
  <link rel="stylesheet" href="css/admin-dashboard.css">
  <link rel="stylesheet" href="css/rent-request.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        .rating-stars {
        display: flex;
        align-items: center;
        gap: 2px;
        margin: 10px 0;
        }
    </style>

</head>
<body>
<header>
    <h1>Car Rental Service</h1>
</header>

<main>
    <h2>Car Details: <?= htmlspecialchars($car['name']) ?> (<?= htmlspecialchars($car['model']) ?>)</h2>

    <div class="car-details">
        <img src="<?= 'images/' . htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['name']) ?>" width="300">
        <p><strong>Type:</strong> <?= htmlspecialchars($car['type']) ?></p>
        <p><strong>Price:</strong> $<?= number_format($car['price_per_day'], 2) ?> per day</p>
        <p><strong>Status:</strong> <?= $car['status'] === 'available' ? 'Available' : 'Not Available' ?></p>
        <p><strong>Category:</strong> <?= ucfirst(htmlspecialchars($car['category'])) ?></p>







        <h3>Ratings:</h3>
        <?php if ($ratingsResult && mysqli_num_rows($ratingsResult) > 0): ?>

            <div class="rating-stars">
    <?php
    // Calculate stars (same logic as chunk 2)
    $fullStars = floor($averageRating);
    $halfStar = ($averageRating - $fullStars >= 0.5) ? 1 : 0;
    $emptyStars = 5 - $fullStars - $halfStar;
    ?>
    
    <!-- Full stars -->
    <?php for ($i = 0; $i < $fullStars; $i++): ?>
        <i class="fas fa-star" style="color: gold;"></i>
    <?php endfor; ?>
    
    <!-- Half star -->
    <?php if ($halfStar): ?>
        <i class="fas fa-star-half-alt" style="color: gold;"></i>
    <?php endif; ?>
    
    <!-- Empty stars -->
    <?php for ($i = 0; $i < $emptyStars; $i++): ?>
        <i class="far fa-star" style="color: gold;"></i>
    <?php endfor; ?>
    
    <!-- Numeric rating -->
    <span style="margin-left: 5px; font-size: 0.9em; color: #666;">
        <?= $averageRating ?>/5
    </span>
</div>




        <?php else: ?>
            <p>No ratings yet.</p>
        <?php endif; ?>
    </div>

    <br><br>
    <a href="index.php">Back to Car List</a>
</main>

<footer>
    <div class="footer-container">
        <p>&copy; 2025 Car Rental Service. All rights reserved.</p>
    </div>
</footer>
</body>
</html>
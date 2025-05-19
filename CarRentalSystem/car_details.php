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

    <!-- Internal Styles -->
    <style>
        body {
            font-family: "League Spartan", sans-serif !important;
            background-color: #F7F9FA;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #006A71;
            color: white;
            text-align: center;
            padding: 15px 0;
        }
        header h1 {
            margin: 0;
            font-size: 2rem;
        }

        main {
            margin: 30px auto;
            padding: 0 20px;
        }

        h2 {
            color: #006A71;
            text-align: center;
            margin-bottom: 20px;
        }

        .car-details {
            display: flex;
            flex-direction: row;
            background-color: #ffffff;
            border-radius: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 80vw;               /* 80% of viewport width */
            max-width: 1200px;         /* don’t exceed 1200px */
            margin: 0 auto;            /* center horizontally */
            height: auto;
        }
        
        .car-details img {
            width: 65%;                /* slightly larger photo view */
            object-fit: cover;
            height: auto;
        }
        .car-info {
            padding: 30px;
            flex-grow: 1;
            width: 35%;                /* complementary to image’s 65% */
        }
        .car-info p {
            margin: 10px 0;
            color: #006A71;
            font-size: 1.1rem;
        }
        .car-info strong {
            color: #006A71;
        }

        /* Ratings Section */
        .rating-section {
            margin-top: 20px;
        }
        .rating-section h3 {
            color: #006A71;
            margin-bottom: 10px;
        }
        .rating-stars {
            display: flex;
            align-items: center;
            gap: 4px;
            margin: 10px 0;
        }
        .rating-stars i {
            color: gold;
            font-size: 1.4rem;       /* slightly larger stars */
        }
        .rating-stars span {
            margin-left: 8px;
            font-size: 1rem;
            color: #333;
        }

        /* Back Link */
        .back-link {
            display: inline-block;
            margin-top: 30px;
            text-decoration: none;
            color: #006A71;
            font-weight: 600;
            border: 2px solid #006A71;
            padding: 10px 20px;
            border-radius: 25px;
            transition: all 0.3s ease-in-out;
        }
        .back-link:hover {
            background-color: #48A6A7;
            color: white;
            border-color: #48A6A7;
        }

        footer {
            background-color: #006A71;
            color: white;
            text-align: center;
            padding: 15px 0;
            margin-top: 40px;
        }
        footer p {
            margin: 0;
            font-size: 0.9rem;
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
        <img src="<?= 'images/' . htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['name']) ?>">
        <div class="car-info">
            <p><strong>Type:</strong> <?= htmlspecialchars($car['type']) ?></p>
            <p><strong>Price:</strong> $<?= number_format($car['price_per_day'], 2) ?> per day</p>
            <p><strong>Status:</strong> <?= $car['status'] === 'available' ? 'Available' : 'Not Available' ?></p>
            <p><strong>Category:</strong> <?= ucfirst(htmlspecialchars($car['category'])) ?></p>

            <div class="rating-section">
                <h3>Ratings:</h3>
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

    <a class="back-link" href="index.php">Back to Car List</a>
</main>

<footer>
    <div class="footer-container">
        <p>&copy; 2025 Car Rental Service. All rights reserved.</p>
    </div>
</footer>
</body>
</html>

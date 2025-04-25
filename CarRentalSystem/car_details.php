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

    // التحقق من صحة التقييم
    if ($rating >= 1 && $rating <= 5) {
        $ratingQuery = "INSERT INTO rating (car_id, user_id, rating) VALUES ($carId, $userId, $rating)";
        if (mysqli_query($conn, $ratingQuery)) {
            echo "<p style='color: green;'>Rating submitted successfully!</p>";
        } else {
            echo "<p style='color: red;'>Error submitting rating: " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p style='color: red;'>Invalid rating value.</p>";
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

        <h3>Average Rating: <?= $averageRating ?>/5</h3>

        <!-- Rating Form -->
        <?php if (isset($_SESSION['user'])): ?>
            <form method="POST">
                <label for="rating">Rate this car:</label>
                <select name="rating" id="rating" required>
                    <option value="">Select Rating</option>
                    <option value="1">1 Star</option>
                    <option value="2">2 Stars</option>
                    <option value="3">3 Stars</option>
                    <option value="4">4 Stars</option>
                    <option value="5">5 Stars</option>
                </select>
                <button type="submit">Submit Rating</button>
            </form>
        <?php else: ?>
            <p>You must be logged in to rate this car.</p>
        <?php endif; ?>

        <h3>Ratings:</h3>
        <?php if ($ratingsResult && mysqli_num_rows($ratingsResult) > 0): ?>
            <ul>
                <?php while ($rating = mysqli_fetch_assoc($ratingsResult)): ?>
                    <li>User <?= htmlspecialchars($rating['user_id']) ?> rated: <?= $rating['rating'] ?>/5</li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No ratings yet.</p>
        <?php endif; ?>
    </div>

    <a href="index.php">Back to Car List</a>
</main>

<footer>
    <div class="footer-container">
        <p>&copy; 2025 Car Rental Service. All rights reserved.</p>
    </div>
</footer>
</body>
</html>
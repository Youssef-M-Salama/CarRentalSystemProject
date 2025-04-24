<?php  
session_start();  
include "includes/config.php";  
  
if (!isset($_SESSION['user'])) {  
    header("Location: Login-Signup-Logout/login.php");  
    exit;  
}  
  
$user_id = $_SESSION['user']['id'];  
  
$query = "SELECT r.*, c.name as car_name, c.model, c.type, c.price_per_day, c.image   
          FROM rental_requests r   
          JOIN cars c ON r.car_id = c.id   
          WHERE r.user_id = $user_id   
          ORDER BY r.created_at DESC";  
$rental_requests = mysqli_query($conn, $query);  
?><!DOCTYPE html><html lang="en">  <head>  
    <meta charset="UTF-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <title>My Rental Requests - Car Rental Service</title>  
    <link rel="stylesheet" href="css/general.css">  
    <link rel="stylesheet" href="css/header.css">  
    <link rel="stylesheet" href="css/sidebar.css">  
    <link rel="stylesheet" href="css/main-content.css">  
    <link rel="stylesheet" href="css/buttons.css">  
    <link rel="stylesheet" href="css/footer.css">  
    <link rel="stylesheet" href="css/forms.css">  
    <link rel="stylesheet" href="css/sort-filter.css">  
    <link rel="stylesheet" href="css/admin-dashboard.css">  
    <link rel="stylesheet" href="css/RentalPageStyle.css">  
</head>  
<body>  
<header>  
    <h1>Car Rental Service</h1>  
    <nav>  
        <ul>  
            <li><a href="index.php">Home</a></li>  
            <li><a href="my_rental.php">My Rentals</a></li>  
            <?php if (isset($_SESSION['user'])): ?>  
                <?php if ($_SESSION['user']['role'] === 'admin'): ?>  
                    <li><a href="admin/DashboardAdmin.php">Admin Dashboard</a></li>  
                <?php endif; ?>  
                <li><a href="Login-Signup-Logout/logout.php">Logout</a></li>  
            <?php else: ?>  
                <li><a href="Login-Signup-Logout/login.php">Login</a></li>  
                <li><a href="Login-Signup-Logout/signup.php">Sign Up</a></li>  
            <?php endif; ?>  
        </ul>  
    </nav>  
</header><main>  
<div class="my-rentals">  
    <h2>My Rental Requests</h2><?php if ($rental_requests && mysqli_num_rows($rental_requests) > 0): ?>  
    <?php while ($request = mysqli_fetch_assoc($rental_requests)): ?>  
        <div class="rental-request">  
            <?php   
                $statusClass = '';  
                $statusText = '';  
                switch($request['status']) {  
                    case 'pending':  
                        $statusClass = 'status-pending';  
                        $statusText = 'Pending Approval';  
                        break;  
                    case 'approved':  
                        $statusClass = 'status-approved';  
                        $statusText = 'Approved - Your Rental is Confirmed';  
                        break;  
                    case 'rejected':  
                        $statusClass = 'status-rejected';  
                        $statusText = 'Rejected - Please Contact Support';  
                        break;  
                }  
            ?>  
            <div class="rental-status <?php echo $statusClass; ?>">  
                <?php echo $statusText; ?>  
            </div>  <div class="rental-info">  
            <img src="images/<?php echo htmlspecialchars($request['image']); ?>" alt="<?php echo htmlspecialchars($request['car_name']); ?>" class="car-image">  

            <div class="car-details">  
                <h3><?php echo htmlspecialchars($request['car_name']); ?> (<?php echo htmlspecialchars($request['model']); ?>)</h3>  
                <p><strong>Type:</strong> <?php echo htmlspecialchars($request['type']); ?></p>  
                <p><strong>Price per day:</strong> $<?php echo number_format($request['price_per_day'], 2); ?></p>  

                <div class="rental-dates">  
                    <p><strong>From:</strong> <?php echo date('F d, Y', strtotime($request['start_date'])); ?></p>  
                    <p><strong>To:</strong> <?php echo date('F d, Y', strtotime($request['end_date'])); ?></p>  

                   
                    <?php
    if ($request['status'] === 'approved') {
        $rental_id = $request['id'];
        $car_id = $request['car_id'];
        $rating_query = "SELECT * FROM rating WHERE rental_id = $rental_id AND user_id = $user_id";
        $rating_result = mysqli_query($conn, $rating_query);

        if (mysqli_num_rows($rating_result) > 0) {
            $rating_row = mysqli_fetch_assoc($rating_result);
            echo "<p><strong>Your rating:</strong> " . $rating_row['rating'] . " stars</p>";
            echo "<p><strong>Comment:</strong> " . htmlspecialchars($rating_row['comment']) . "</p>";
        } else {
?>
        <form action="submit_rating.php" method="post" class="rating-form">
            <input type="hidden" name="rental_id" value="<?php echo $rental_id; ?>">
            <input type="hidden" name="car_id" value="<?php echo $car_id; ?>">
            <label for="rating">Rate this car:</label>
            <select name="rating" required>
                <option value="">Choose rating</option>
                <option value="1">1 Star</option>
                <option value="2">2 Stars</option>
                <option value="3">3 Stars</option>
                <option value="4">4 Stars</option>
                <option value="5">5 Stars</option>
            </select>
            <br>
            <label for="comment">Comment (optional):</label>
            <textarea name="comment" rows="3" cols="30"></textarea>
            <br>
            <button type="submit">Submit Rating</button>
        </form>
<?php
        }
    }
?>




    </div>  
<?php endwhile; ?>

<?php else: ?>  <div class="no-rentals">  
    <p>You haven't made any rental requests yet.</p>  
    <p><a href="index.php">Browse available cars</a> to make your first rental request.</p>  
</div>

<?php endif; ?>  </div>  
</main><footer>  
    <div class="footer-container">  
        <div class="footer-section">  
            <h3>Contact Us</h3>  
            <p>Email: info@carrentalservice.com</p>  
            <p>Phone: +1 123-456-7890</p>  
        </div>  
        <div class="footer-section">  
            <h3>Follow Us</h3>  
            <ul class="social-links">  
                <li><a href="#"><i class="fab fa-facebook"></i></a></li>  
                <li><a href="#"><i class="fab fa-twitter"></i></a></li>  
                <li><a href="#"><i class="fab fa-instagram"></i></a></li>  
                <li><a href="#"><i class="fab fa-linkedin"></i></a></li>  
            </ul>  
        </div>  
        <div class="footer-section">  
            <h3>Quick Links</h3>  
            <ul>  
                <li><a href="#">About Us</a></li>  
                <li><a href="#">Privacy Policy</a></li>  
                <li><a href="#">Terms of Service</a></li>  
                <li><a href="#">FAQs</a></li>  
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
</body>  
</html>
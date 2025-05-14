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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Rental Requests - Car Rental Service</title>
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
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/offers.css">
    <link rel="stylesheet" href="css/profile.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/admin-dashboard.css">
    <link rel="stylesheet" href="css/RentalPageStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
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
          <li class="nav-item"><a class="nav-link active" aria-current="page" href="my_rental.php">My Rentals</a></li>
          <li class="nav-item"><a class="nav-link" href="offers.php">Special Offers</a></li>
          <li class="nav-item"><a class="nav-link" href="Login-Signup-Logout/logout.php">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="Login-Signup-Logout/login.php">Login</a></li>
          <li class="nav-item"><a class="nav-link" href="Login-Signup-Logout/signup.php">Sign Up</a></li>
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

    <main>
        <div class="my-rentals">
            <h2>My Rental Requests</h2>
            <?php if ($rental_requests && mysqli_num_rows($rental_requests) > 0): ?>
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
                        </div>

                        <div class="rental-info">  
                            <img src="images/<?php echo htmlspecialchars($request['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($request['car_name']); ?>" 
                                 class="car-image">  

                            <div class="car-details">  
                                <h3><?php echo htmlspecialchars($request['car_name']); ?> 
                                    (<?php echo htmlspecialchars($request['model']); ?>)</h3>  
                                <p><strong>Type:</strong> <?php echo htmlspecialchars($request['type']); ?></p>  
                                <p><strong>Price per day:</strong> $<?php echo number_format($request['price_per_day'], 2); ?></p>  

                                <div class="rental-dates">  
                                    <p><strong>From:</strong> <?php echo date('F d, Y', strtotime($request['start_date'])); ?></p>  
                                    <p><strong>To:</strong> <?php echo date('F d, Y', strtotime($request['end_date'])); ?></p>  
                                </div>

                                <?php
                                if ($request['status'] === 'approved') {
                                    $rental_id = $request['id'];
                                    $car_id = $request['car_id'];
                                    $rating_query = "SELECT * FROM rating WHERE rental_id = $rental_id AND user_id = $user_id";
                                    $rating_result = mysqli_query($conn, $rating_query);

                                    if (mysqli_num_rows($rating_result) > 0) {
                                        $rating_row = mysqli_fetch_assoc($rating_result);
                                        echo "<div class='user-rating'>";
                                        echo "<p><strong>Your Rating:</strong> ";
                                        for ($i = 0; $i < 5; $i++) {
                                            if ($i < $rating_row['rating']) {
                                                echo "<i class='fas fa-star' style='color: gold;'></i>";
                                            } else {
                                                echo "<i class='far fa-star' style='color: gold;'></i>";
                                            }
                                        }
                                        echo "</p>";
                                        if ($rating_row['comment']) {
                                            echo "<p><strong>Your Comment:</strong> " . htmlspecialchars($rating_row['comment']) . "</p>";
                                        }
                                        echo "</div>";
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
                                            <textarea name="comment" rows="3" cols="30" placeholder="Share your experience with this car..."></textarea>
                                            <br>
                                            <button type="submit">
                                                <i class="fas fa-star"></i> Submit Rating
                                            </button>
                                        </form>
                                <?php
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-rentals">
                    <p>You haven't made any rental requests yet.</p>
                    <p><a href="index.php">Browse available cars</a> to make your first rental request.</p>
                </div>
            <?php endif; ?>
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

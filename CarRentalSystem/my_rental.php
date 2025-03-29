<?php
session_start();
include "includes/config.php";

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: Login-Signup-Logout/login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

// Fetch the user's rental requests with car details
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
    <link rel="stylesheet" href="styles.css">
    <style>
        .my-rentals {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .rental-request {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .rental-status {
            text-align: center;
            padding: 8px;
            border-radius: 4px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .rental-info {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .car-image {
            width: 200px;
            height: 150px;
            object-fit: cover;
            border-radius: 5px;
        }
        
        .car-details {
            flex: 1;
            min-width: 300px;
        }
        
        .rental-dates {
            background-color: #e9f7ff;
            padding: 10px;
            border-radius: 4px;
            margin-top: 10px;
        }
        
        .no-rentals {
            text-align: center;
            padding: 30px 0;
            font-size: 18px;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- Header Section -->
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
    </header>
    
    <!-- Main Content -->
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
                            <img src="images/<?php echo htmlspecialchars($request['image']); ?>" alt="<?php echo htmlspecialchars($request['car_name']); ?>" class="car-image">
                            
                            <div class="car-details">
                                <h3><?php echo htmlspecialchars($request['car_name']); ?> (<?php echo htmlspecialchars($request['model']); ?>)</h3>
                                <p><strong>Type:</strong> <?php echo htmlspecialchars($request['type']); ?></p>
                                <p><strong>Price per day:</strong> $<?php echo number_format($request['price_per_day'], 2); ?></p>
                                
                                <div class="rental-dates">
                                    <p><strong>From:</strong> <?php echo date('F d, Y', strtotime($request['start_date'])); ?></p>
                                    <p><strong>To:</strong> <?php echo date('F d, Y', strtotime($request['end_date'])); ?></p>
                                    
                                    <?php 
                                        // Calculate total rental days
                                        $start = new DateTime($request['start_date']);
                                        $end = new DateTime($request['end_date']);
                                        $days = $start->diff($end)->days + 1; // Including start and end days
                                        
                                        // Calculate total price
                                        $totalPrice = $days * $request['price_per_day'];
                                    ?>
                                    
                                    <p><strong>Total days:</strong> <?php echo $days; ?></p>
                                    <p><strong>Total price:</strong> $<?php echo number_format($totalPrice, 2); ?></p>
                                </div>
                                
                                <p><small>Requested on: <?php echo date('F d, Y H:i', strtotime($request['created_at'])); ?></small></p>
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
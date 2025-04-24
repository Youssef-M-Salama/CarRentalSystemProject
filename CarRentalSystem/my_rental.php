<?php
session_start();
require_once 'includes/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: Login-Signup-Logout/login.php');
    exit();
}

$user_id = $_SESSION['user']['id'];

// Get success/error messages
$success_message = isset($_GET['success']) ? $_GET['success'] : '';
$error_message = isset($_GET['error']) ? $_GET['error'] : '';

// Check if average_rating column exists
$check_column = "SHOW COLUMNS FROM cars LIKE 'average_rating'";
$column_exists = mysqli_query($conn, $check_column);

// Fetch rental requests with car details
$query = "SELECT r.*, c.name as car_name, c.model, c.type, c.price_per_day, c.image" . 
         ($column_exists && mysqli_num_rows($column_exists) > 0 ? ", c.average_rating" : "") . 
         " FROM rental_requests r 
          JOIN cars c ON r.car_id = c.id 
          WHERE r.user_id = ? 
          ORDER BY r.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$rental_requests = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .rental-request {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .rental-request:hover {
            transform: translateY(-5px);
        }
        .rental-status {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-weight: bold;
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
        .car-image {
            width: 200px;
            height: 150px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 20px;
        }
        .rental-info {
            display: flex;
            align-items: flex-start;
        }
        .car-details {
            flex-grow: 1;
        }
        .rating-form {
            margin-top: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .rating-form select, .rating-form textarea {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .rating-form button {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .rating-form button:hover {
            background: #218838;
        }
        .star-rating {
            color: #ffc107;
            font-size: 1.2em;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
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
                <?php if (isAdmin()): ?>
                    <li><a href="admin/DashboardAdmin.php">Admin Dashboard</a></li>
                <?php endif; ?>
                <li><a href="Login-Signup-Logout/logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    
    <!-- Main Content -->
    <main>
        <div class="my-rentals">
            <h2>My Rental Requests</h2>
            
            <?php if ($success_message): ?>
                <div class="message success-message">
                    <?php echo htmlspecialchars(str_replace('_', ' ', $success_message)); ?>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="message error-message">
                    <?php echo htmlspecialchars(str_replace('_', ' ', $error_message)); ?>
                </div>
            <?php endif; ?>

            <?php if ($rental_requests && $rental_requests->num_rows > 0): ?>
                <?php while ($request = $rental_requests->fetch_assoc()): ?>
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
                                <?php if (isset($request['average_rating']) && $request['average_rating'] > 0): ?>
                                    <p><strong>Car Rating:</strong> 
                                        <span class="star-rating">
                                            <?php for ($i = 0; $i < 5; $i++): ?>
                                                <?php if ($i < $request['average_rating']): ?>
                                                    <i class="fas fa-star"></i>
                                                <?php else: ?>
                                                    <i class="far fa-star"></i>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </span>
                                    </p>
                                <?php endif; ?>
                                
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

                                <?php if ($request['status'] === 'approved'): ?>
                                    <?php
                                        $rating_query = "SELECT * FROM rating WHERE rental_id = ? AND user_id = ?";
                                        $stmt = $conn->prepare($rating_query);
                                        $stmt->bind_param("ii", $request['id'], $user_id);
                                        $stmt->execute();
                                        $rating_result = $stmt->get_result();

                                        if ($rating_result->num_rows > 0):
                                            $rating_row = $rating_result->fetch_assoc();
                                    ?>
                                        <div class="user-rating">
                                            <p><strong>Your Rating:</strong> 
                                                <span class="star-rating">
                                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                                        <?php if ($i < $rating_row['rating']): ?>
                                                            <i class="fas fa-star"></i>
                                                        <?php else: ?>
                                                            <i class="far fa-star"></i>
                                                        <?php endif; ?>
                                                    <?php endfor; ?>
                                                </span>
                                            </p>
                                            <?php if ($rating_row['comment']): ?>
                                                <p><strong>Your Comment:</strong> <?php echo htmlspecialchars($rating_row['comment']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <form action="submit_rating.php" method="post" class="rating-form">
                                            <input type="hidden" name="rental_id" value="<?php echo $request['id']; ?>">
                                            <input type="hidden" name="car_id" value="<?php echo $request['car_id']; ?>">
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
                                    <?php endif; ?>
                                <?php endif; ?>
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

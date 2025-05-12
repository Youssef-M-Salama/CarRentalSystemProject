<?php
session_start();
include "includes/config.php";

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: Login-Signup-Logout/login.php");
    exit;
}

$error = '';
$success = '';
$car_id = isset($_GET['car_id']) ? intval($_GET['car_id']) : 0;

// Initialize form values for repopulation
$start_date = '';
$end_date   = '';

// Fetch car details
$car_query  = "SELECT * FROM cars WHERE id = $car_id AND status = 'available'";
$car_result = mysqli_query($conn, $car_query);
if (!$car_result || mysqli_num_rows($car_result) == 0) {
    header("Location: index.php");
    exit;
}
$car = mysqli_fetch_assoc($car_result);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Repopulate submitted values
    $start_date = mysqli_real_escape_string($conn, $_POST['start_date'] ?? '');
    $end_date   = mysqli_real_escape_string($conn, $_POST['end_date']   ?? '');
    $user_id    = $_SESSION['user']['id'];

    // ✅ Check if user exists in users table
    $user_check = mysqli_query($conn, "SELECT id FROM users WHERE id = $user_id");
    if (mysqli_num_rows($user_check) == 0) {
        die("❌ Error: User ID ($user_id) does not exist in 'users' table.");
    }

    // Validate dates
    $current_date = date('Y-m-d');
    if ($start_date < $current_date) {
        $error = "Start date cannot be in the past.";
    } else if ($end_date < $start_date) {
        $error = "End date cannot be before start date.";
    } else {
        // Submit rental request
        $query  = "INSERT INTO rental_requests (user_id, car_id, start_date, end_date) 
                   VALUES ($user_id, $car_id, '$start_date', '$end_date')";
        $result = mysqli_query($conn, $query);
        
        if ($result) {
            $success = "Your rental request has been submitted. You will be notified once it is approved.";
            // Clear form values on success
            $start_date = '';
            $end_date   = '';
        } else {
            $error = "Failed to submit request: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Rental Request - Car Rental Service</title>
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
</head>
<body>
  <header>
    <h1>Car Rental Service</h1>
    <nav>
      <ul>
        <li><a href="index.php">Home</a></li>
        <?php if (isset($_SESSION['user'])): ?>
          <li><a href="my_rental.php">My Rentals</a></li>
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

  <main>
    <div class="rental-form">
      <h2>Submit Rental Request</h2>

      <?php if ($error): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
      <?php endif; ?>

      <div class="car-details">
        <div class="car-image">
          <img src="images/<?php echo htmlspecialchars($car['image']); ?>"
               alt="<?php echo htmlspecialchars($car['name']); ?>">
        </div>
        <div class="car-info">
          <h3>
            <?php echo htmlspecialchars($car['name']); ?>
            (<?php echo htmlspecialchars($car['model']); ?>)
          </h3>
          <p><strong>Type:</strong> <?php echo htmlspecialchars($car['type']); ?></p>
          <p><strong>Category:</strong> <?php echo htmlspecialchars($car['category']); ?></p>
          <p><strong>Price/Day:</strong>
             $<?php echo number_format($car['price_per_day'], 2); ?>
          </p>
        </div>
      </div>

      <form method="POST" action="">
        <div class="date-inputs">
          <div>
            <label for="start_date">Start Date:</label>
            <input
              type="date"
              id="start_date"
              name="start_date"
              value="<?php echo htmlspecialchars($start_date); ?>"
              min="<?php echo date('Y-m-d'); ?>"
              required
              onchange="calculatePrice()"
            >
          </div>
          <div>
            <label for="end_date">End Date:</label>
            <input
              type="date"
              id="end_date"
              name="end_date"
              value="<?php echo htmlspecialchars($end_date); ?>"
              min="<?php echo date('Y-m-d'); ?>"
              required
              onchange="calculatePrice()"
            >
          </div>
        </div>

        <div
          id="price-calculation"
          class="price-calculation"
          style="display: none;"
        >
          Total Price: $<span id="total-price">0.00</span>
        </div>

        <button type="submit" class="btn btn-rent">Submit Rental Request</button>
      </form>
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

  <script>
    function calculatePrice() {
      const startDate  = document.getElementById('start_date').value;
      const endDate    = document.getElementById('end_date').value;
      const pricePerDay = <?php echo $car['price_per_day']; ?>;
      if (startDate && endDate) {
        const start = new Date(startDate);
        const end   = new Date(endDate);
        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        if (diffDays > 0) {
          document.getElementById('price-calculation').style.display = 'block';
          document.getElementById('total-price').textContent =
            (diffDays * pricePerDay).toFixed(2);
        }
      }
    }
  </script>
</body>
</html>

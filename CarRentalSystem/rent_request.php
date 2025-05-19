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

    $with_driver = mysqli_real_escape_string($conn, $_POST['with_driver']);

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
    $query = "INSERT INTO rental_requests (user_id, car_id, start_date, end_date, with_driver) 
          VALUES ($user_id, $car_id, '$start_date', '$end_date', '$with_driver')";
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
  <!-- google fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@100..900&display=swap" rel="stylesheet">
  <!-- font awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <!-- bootstrap css -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
  <!-- Include CSS stylesheets -->
  <link rel="stylesheet" href="css/rent-request.css">
  <link rel="stylesheet" href="css/AdminDashboard.css">
  <link rel="stylesheet" href="css/general.css">
  <link rel="stylesheet" href="css/header.css">
  <link rel="stylesheet" href="css/main-content.css">
  <link rel="stylesheet" href="css/sort-filter.css">
  <link rel="stylesheet" href="css/offers.css">
  <link rel="stylesheet" href="css/buttons.css">
  <link rel="stylesheet" href="css/footer.css">
  <link rel="stylesheet" href="css/index.css">
</head>

<body>
  <header class="navbar navbar-expand-lg bg-body-tertiary d-flex justify-content-center align-items-center">
    <nav class="container-fluid d-flex justify-content-center align-items-center">
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <h1 class="navbar-brand">Car Rental Service</h1>
        <ul class="navbar-nav">
          <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
          <?php if (isset($_SESSION['user'])): ?>
            <li class="nav-item"><a class="nav-link" href="my_rental.php">My Rentals</a></li>
            <?php if ($_SESSION['user']['role'] === 'admin'): ?>
              <li class="nav-item"><a class="nav-link" href="admin/DashboardAdmin.php">Admin Dashboard</a></li>
            <?php endif; ?>
            <li class="nav-item"><a class="nav-link" href="Login-Signup-Logout/logout.php">Logout</a></li>
          <?php endif; ?>
        </ul>
      </div>
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
              onchange="calculatePrice()">
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
              onchange="calculatePrice()">
          </div>
        </div>

        <div class="driver-option">
          <label class="title">Driver Option:</label>
            <input type="radio" id="with_driver" name="with_driver" value="yes" required>
            <label for="with_driver">With Driver</label>
            <input type="radio" id="without_driver" name="with_driver" value="no" required>
            <label for="without_driver">Without Driver</label>
        </div>

        <div
          id="price-calculation"
          class="price-calculation"
          style="display: none;">
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
        <a href="mailto:info@carrentalservice.com">Email: info@carrentalservice.com</a>
        <a href="01234567890">Phone: 01234567890</a>
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
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.querySelector('form'); // أو استخدم الـ ID لو فيه
      form.addEventListener('submit', function(e) {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;

        // تحقق إذا كان تاريخ النهاية قبل تاريخ البداية
        if (new Date(endDate) < new Date(startDate)) {
          e.preventDefault(); // يمنع الفورم من الإرسال
          alert('End date cannot be before start date. Please select a valid date range.');
        }
      });
    });



    document.addEventListener('DOMContentLoaded', function() {
      const form = document.querySelector('form'); // أو استخدم الـ ID لو فيه
      form.addEventListener('submit', function(e) {
        const selectedDriver = document.querySelector('input[name="with_driver"]:checked');
        if (!selectedDriver) {
          e.preventDefault(); // يمنع الفورم من الإرسال
          alert('Please select whether you want a driver or not.');
        }
      });
    });

    // نربط الفانكشن بالأحداث لما الصفحة تفتح
    document.addEventListener('DOMContentLoaded', function() {
      document.getElementById('start_date').addEventListener('change', calculatePrice);
      document.getElementById('end_date').addEventListener('change', calculatePrice);

      const driverRadios = document.querySelectorAll('input[name="with_driver"]');
      driverRadios.forEach(function(radio) {
        radio.addEventListener('change', calculatePrice);
      });
    });

    function calculatePrice() {
      const startDate = document.getElementById('start_date').value;
      const endDate = document.getElementById('end_date').value;
      const pricePerDay = <?php echo $car['price_per_day']; ?>;

      const withDriver = document.querySelector('input[name="with_driver"]:checked');

      if (startDate && endDate && withDriver) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

        if (diffDays > 0) {
          const driverFee = withDriver.value === 'yes' ? 1000 : 0;
          const totalPrice = (diffDays * (pricePerDay + driverFee)).toFixed(2);

          document.getElementById('total-price').textContent = totalPrice;
          document.getElementById('price-calculation').style.display = 'block';
        }
      } else {
        // نخفي السعر لو مش كل البيانات موجودة
        document.getElementById('price-calculation').style.display = 'none';
      }
    }
  </script>
</body>

</html>
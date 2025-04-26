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

// Fetch car details
$car_query = "SELECT * FROM cars WHERE id = $car_id AND status = 'available'";
$car_result = mysqli_query($conn, $car_query);

if (!$car_result || mysqli_num_rows($car_result) == 0) {
  header("Location: index.php");
  exit;
}

$car = mysqli_fetch_assoc($car_result);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
  $end_date = mysqli_real_escape_string($conn, $_POST['end_date']);
  $user_id = $_SESSION['user']['id'];

  $with_driver = mysqli_real_escape_string($conn, $_POST['with_driver']);

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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

  <!-- Main Content -->
  <main>
    <div class="rental-form">
      <h2>Submit Rental Request</h2>

      <?php if (!empty($error)): ?>
        <div class="error-message"><?php echo $error; ?></div>
      <?php endif; ?>

      <?php if (!empty($success)): ?>
        <div class="success-message"><?php echo $success; ?></div>
      <?php endif; ?>

      <div class="car-details">
        <div class="car-image">
          <img src="images/<?php echo htmlspecialchars($car['image']); ?>" alt="<?php echo htmlspecialchars($car['name']); ?>">
        </div>
        <div class="car-info">
          <h3><?php echo htmlspecialchars($car['name']); ?> (<?php echo htmlspecialchars($car['model']); ?>)</h3>
          <p><strong>Type:</strong> <?php echo htmlspecialchars($car['type']); ?></p>
          <p><strong>Category:</strong> <?php echo htmlspecialchars($car['category']); ?></p>
          <p><strong>Price:</strong> $<?php echo number_format($car['price_per_day'], 2); ?>/day</p>
        </div>
      </div>

      <form method="POST" action="">
        <div class="date-inputs">
          <div>
            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" min="<?php echo date('Y-m-d'); ?>" required onchange="calculatePrice()">
          </div>
          <div>
            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" min="<?php echo date('Y-m-d'); ?>" required onchange="calculatePrice()">
          </div>
        </div>

        <div class="driver-option">
          <label class="title">Driver Option:</label>
          <div class="option">
            <input type="radio" id="with_driver" name="with_driver" value="yes" required>
            <label for="with_driver">With Driver</label>
          </div>
          <div class="option">
            <input type="radio" id="without_driver" name="with_driver" value="no" required>
            <label for="without_driver">Without Driver</label>
          </div>
        </div>

        <div id="price-calculation" class="price-calculation" style="display: none;">
          Total Price: $<span id="total-price">0.00</span>
        </div>

        <button type="submit" class="btn btn-rent">Submit Rental Request</button>
      </form>
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
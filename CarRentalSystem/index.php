<?php
session_start();
include "includes/config.php";

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Get and sanitize filters
$search = mysqli_real_escape_string($conn, $_GET['search'] ?? '');
$type = mysqli_real_escape_string($conn, $_GET['type'] ?? '');
$sort = mysqli_real_escape_string($conn, $_GET['sort'] ?? 'none');
$min_price = mysqli_real_escape_string($conn, $_GET['min_price'] ?? '');
$max_price = mysqli_real_escape_string($conn, $_GET['max_price'] ?? '');
$availability = mysqli_real_escape_string($conn, $_GET['availability'] ?? '');
$name = mysqli_real_escape_string($conn, $_GET['name'] ?? '');
$model = mysqli_real_escape_string($conn, $_GET['model'] ?? '');
$class = mysqli_real_escape_string($conn, $_GET['class'] ?? '');

// Base query
$regularQuery = "SELECT * FROM cars WHERE (name LIKE '%$search%' OR model LIKE '%$search%')";

// فلتر التصنيف
if ($class == 'free') {
    $regularQuery .= " AND category = 'free'";
} elseif ($class == 'premium') {
    $regularQuery .= " AND category = 'premium'";
}

// فلتر النوع
if (!empty($type)) {
    $regularQuery .= " AND type = '$type'";
}

// فلتر السعر
if ($min_price !== '' && $max_price !== '') {
    $regularQuery .= " AND price_per_day BETWEEN $min_price AND $max_price";
}

// فلتر الاسم والموديل
if ($name) {
    $regularQuery .= " AND name LIKE '%$name%'";
}
if ($model) {
    $regularQuery .= " AND model LIKE '%$model%'";
}

// فلتر التوفر
if ($availability === '1') {
    $regularQuery .= " AND status = 'available'";
} elseif ($availability === '0') {
    $regularQuery .= " AND status != 'available'";
}

// استبعاد premium للعاديين
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['premium', 'admin'])) {
    $regularQuery .= " AND category != 'premium'";
}

// إعداد استعلام premium
$premiumQuery = '';
if (isset($_SESSION['user']) && in_array($_SESSION['user']['role'], ['premium', 'admin']) && $class != 'free') {
    $premiumQuery = "SELECT * FROM cars WHERE category = 'premium' AND (name LIKE '%$search%' OR model LIKE '%$search%')";

    if (!empty($type)) {
        $premiumQuery .= " AND type = '$type'";
    }

    if ($min_price !== '' && $max_price !== '') {
        $premiumQuery .= " AND price_per_day BETWEEN $min_price AND $max_price";
    }

    if ($availability === '1') {
        $premiumQuery .= " AND status = 'available'";
    } elseif ($availability === '0') {
        $premiumQuery .= " AND status != 'available'";
    }

    if ($name) {
        $premiumQuery .= " AND name LIKE '%$name%'";
    }

    if ($model) {
        $premiumQuery .= " AND model LIKE '%$model%'";
    }
}
$price_range = isset($_GET['price']) ? $_GET['price'] : ''; // فلتر السعر
$min = 0;
$max = 100000; // قيمة افتراضية للسعر

<<<<<<< Updated upstream
// ترتيب النتائج
switch($sort) {
    case 'price_asc':
        $orderBy = " ORDER BY price_per_day ASC";
        break;
    case 'price_desc':
        $orderBy = " ORDER BY price_per_day DESC";
        break;
    case 'year_asc':
        $orderBy = " ORDER BY CAST(SUBSTRING_INDEX(model, ' ', -1) AS UNSIGNED) ASC";
        break;
    case 'year_desc':
        $orderBy = " ORDER BY CAST(SUBSTRING_INDEX(model, ' ', -1) AS UNSIGNED) DESC";
        break;
    case 'available':
        $orderBy = " ORDER BY status ASC";
        break;
    default:
        $orderBy = "";
=======
// لو فيه فلتر للسعر، نعمل split للـ price_range
if (!empty($price_range)) {
    list($min, $max) = explode('-', $price_range); // تقسيم الـ range
}

// استعلام جلب العربيات الـ Premium
$premiumQuery = "SELECT * FROM cars WHERE category = 'premium'";

// لو فيه فلتر نوع
if (!empty($type)) {
    $premiumQuery .= " AND type = '$type'";
}

// فلتر البحث بالاسم أو الموديل
if (!empty($search)) {
    $premiumQuery .= " AND (name LIKE '%$search%' OR model LIKE '%$search%')";
}

// لو فيه فلتر للسعر، نضيفه للـ query
if (!empty($price_range)) {
    $premiumQuery .= " AND price_per_day BETWEEN $min AND $max";
}

// تنفيذ الاستعلام
$result = mysqli_query($conn, $premiumQuery);

// عرض النتيجة
while ($row = mysqli_fetch_assoc($result)) {
    // هنا هتبدأ تعرض السيارات الـ Premium
    echo "<div>" . $row['name'] . " - " . $row['price_per_day'] . "</div>";
>>>>>>> Stashed changes
}

// تنفيذ الاستعلامات
$regularResult = mysqli_query($conn, $regularQuery . $orderBy);
$premiumResult = $premiumQuery ? mysqli_query($conn, $premiumQuery . $orderBy) : false;

$error = '';
if (!$regularResult || ($premiumQuery && !$premiumResult)) {
    $error = "Database error: " . mysqli_error($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rental Service</title>
    
    <!-- Include CSS stylesheets -->
    <link rel="stylesheet" href="css/general.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/main-content.css">
    <link rel="stylesheet" href="css/buttons.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/sort-filter.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <style>
        /* Container for car grid layout */
        .car-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 80px;
            padding: 20px;
        }
        
        /* Styling for individual car cards */
        .card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        /* Space between premium and regular sections */
        .premium-section {
            margin-bottom: 40px;
        }
        
        /* Responsive car images */
        .card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        /* Status labels */
        .available { color: #4CAF50; font-weight: bold; }
        .not-available { color: #f44336; font-weight: bold; }
        .filter-box {
    display: none;
    padding: 15px;
    border: 1px solid #ccc;
    margin: 10px 0;
    background: #f9f9f9;
  }
  .toggle-btn {
    padding: 10px 20px;
    background: #007BFF;
    color: white;
    border: none;
    cursor: pointer;
    margin-bottom: 10px;
  }
  .apply-button {
    background-color: #007bff;
    color: white;
    padding: 12px 24px;
    font-size: 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.apply-button:hover {
    background-color: #0056b3;
}
.apply-button, .reset-button {
    background-color: #007bff; /* اللون الأزرق */
    color: white;
    padding: 5px 15px;
    border-radius: 5px;
    text-decoration: none;
    margin-left: 10px; /* المسافة بين الأزرار */
}

.reset-button {
    display: inline-block; /* علشان يبقى جنب الزر التاني */
    margin-left: 10px;
}
    </style>
</head>

<body>
    <!-- Website Header Section -->
    <header>
        <h1>Car Rental Service</h1>
        <nav>
            <ul>
                <!-- Home link visible to all users -->
                <li><a href="index.php">Home</a></li>
                
                <!-- Links visible only to logged-in users -->
                <?php if (isset($_SESSION['user'])): ?>
                    <li><a href="my_rental.php">My Rentals</a></li>
                    
                    <!-- Admin-only dashboard link -->
                    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                        <li><a href="admin/DashboardAdmin.php">Admin Dashboard</a></li>
                    <?php endif; ?>
                    
                    <li><a href="Login-Signup-Logout/logout.php">Logout</a></li>
                    
                    <!-- Profile icon link -->
                    <li>
                        <a href="profile.php" class="profile-link">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                        </a>
                    </li>
                <?php else: ?>
                    <!-- Links for non-logged-in users -->
                    <li><a href="Login-Signup-Logout/login.php">Login</a></li>
                    <li><a href="Login-Signup-Logout/signup.php">Sign Up</a></li>
                <?php endif; ?>
                
                <li><a href="about us.html">About Us</a></li>
            </ul>
        </nav>
    </header>

    <!-- Main Content Section -->
    <main>
        <h2>Available Cars</h2>

        <form method="GET" action="index.php">
    <a href="index.php" class="reset-button">Reset</a>
</form>
<br>
<div class="sort-filter">
            <form method="GET">
                <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
                <label>Sort By:</label>
                <select name="sort">
                    <option value="none" <?= $sort == 'none' ? 'selected' : '' ?>>Default</option>
                    <option value="price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>Price (Low-High)</option>
                    <option value="price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>Price (High-Low)</option>
                    <option value="year_asc" <?= $sort == 'year_asc' ? 'selected' : '' ?>>Year (Old-New)</option>
                    <option value="year_desc" <?= $sort == 'year_desc' ? 'selected' : '' ?>>Year (New-Old)</option>
                    <option value="available" <?= $sort == 'available' ? 'selected' : '' ?>>Availability</option>
                </select>
                <button type="submit">Apply</button>
            </form>
        </div>
       <!-- زرار فتح البوكس -->
<button class="toggle-btn" onclick="toggleFilter()">Filter Options</button>

<!-- بوكس الفلترة -->
<div class="filter-box" id="filterBox">
  <form method="GET" action="index.php">
    <label>Price Range:</label><br>
    <input type="number" name="min_price" placeholder="Min Price">
    <input type="number" name="max_price" placeholder="Max Price"><br><br>

    <label>Type:</label><br>
    <select name="type">
      <option value="">-- All Types --</option>
      <option value="SUV">SUV</option>
      <option value="Sedan">Sedan</option>
      <option value="Hatchback">Hatchback</option>
    </select><br><br>

    <label>Availability:</label><br>
    <select name="availability">
      <option value="">-- All --</option>
      <option value="1">Available</option>
      <option value="0">Not Available</option>
                </select>
      <br><br>
      <!-- فلتر باسم العربية (name) -->
       <form>
<label>Car Name:</label>
<input type="text" name="name" placeholder="e.g. BMW" value="<?= htmlspecialchars($name) ?>">
<br><br>

<!-- فلتر بسنة الموديل -->
<label>Model Year:</label>
<select name="model">
    <option value="">Select Year</option>
    <?php for ($year = 2000; $year <= date("Y"); $year++): ?>
        <option value="<?= $year ?>" <?= $model == $year ? 'selected' : '' ?>><?= $year ?></option>
    <?php endfor; ?>
</select>
    </select><br><br>
    <label for="class">car</label>
<select name="class" id="class">
    <option value="">All</option>
    <option value="free" <?= isset($_GET['class']) && $_GET['class'] == 'free' ? 'selected' : '' ?>>Free</option>
    <option value="premium" <?= isset($_GET['class']) && $_GET['class'] == 'premium' ? 'selected' : '' ?>>Premium</option>
</select> 
<br>
<br>

<button type="submit" class="apply-button">Apply</button>
  </form>
</div>



<!-- JavaScript لفتح البوكس -->
<script>
  function toggleFilter() {
    var box = document.getElementById("filterBox");
    box.style.display = (box.style.display === "none" || box.style.display === "") ? "block" : "none";
  }
</script>
           


        <!-- Show error messages if any -->
        <?php if (!empty($error)): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>

        <!-- Premium Cars Section (visible to premium/admin users) -->
        <?php if (isset($_SESSION['user']) && 
                 in_array($_SESSION['user']['role'], ['premium', 'admin'])): ?>
            <div class="premium-section">
                <h3>Premium cars</h3>
                <div class="car-grid">
                    <?php if ($premiumResult && mysqli_num_rows($premiumResult) > 0): ?>
                        <?php while ($car = mysqli_fetch_assoc($premiumResult)): ?>
                            <?= renderCarCard($car) ?>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No premium cars available</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Regular Cars Section -->
        <div class="regular-section">
            <h3><?= (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'premium') 
                    ? 'Standard Vehicles' 
                    : 'Available Cars' ?></h3>
            <div class="car-grid">
                <?php if ($regularResult && mysqli_num_rows($regularResult) > 0): ?>
                    <?php while ($car = mysqli_fetch_assoc($regularResult)): ?>
                        <?= renderCarCard($car) ?>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No cars available</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Footer Section -->
    <footer>
        <div class="footer-container">
            <!-- Contact Information -->
            <div class="footer-section">
                <h3>Contact Us</h3>
                <p>Email: info@carrentalservice.com</p>
                <p>Phone: 0000000</p>
            </div>
            
            <!-- Social Media Links -->
            <div class="footer-section">
                <h3>Follow Us</h3>
                <ul class="social-links">
                    <li><a href="#"><i class="fab fa-facebook"></i></a></li>
                    <li><a href="#"><i class="fab fa-twitter"></i></a></li>
                    <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                    <li><a href="#"><i class="fab fa-linkedin"></i></a></li>
                </ul>
            </div>
            
            <!-- Quick Links -->
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="about us.html">About Us</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms of Service</a></li>
                    <li><a href="#">FAQs</a></li>
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
</body>
</html>

<?php
/**
 * Generate HTML for individual car cards
 * This function creates the visual representation of each car
 * with all its details and rental button
 */
function renderCarCard($car) {
    // Sanitize and format car data
    $name = htmlspecialchars($car['name'] ?? 'Unknown');
    $model = htmlspecialchars($car['model'] ?? 'Unknown');
    $type = htmlspecialchars($car['type'] ?? 'N/A');
    $price = isset($car['price_per_day']) ? 
        '$' . number_format($car['price_per_day'], 2) : 
        'N/A';
    $image = !empty($car['image']) ? 
        'images/' . htmlspecialchars($car['image']) : 
        'images/default.png';
    $status = $car['status'] ?? 'available';
    $category = htmlspecialchars($car['category'] ?? 'N/A');
    
    // Determine availability status styling
    $availabilityClass = $status === 'available' ? 'available' : 'not-available';
    $availabilityText = $status === 'available' ? 'Available' : 'Not Available';

    ob_start(); ?>
    <div class="card">
        <!-- Car Image -->
        <img src="<?= $image ?>" alt="<?= $name ?>">
        
        <!-- Car Details -->
        <h3><?= "$name ($model)" ?></h3>
        <div class="car-details">
            <p><strong>Type:</strong> <?= $type ?></p>
            <p><strong>Price:</strong> <?= $price ?>/day</p>
            <p><strong>Status:</strong> 
                <span class="<?= $availabilityClass ?>"><?= $availabilityText ?></span>
            </p>
            <p><strong>Category:</strong> <?= $category ?></p>
        </div>
        
        <!-- Rental Button -->
        <?php if ($status === 'available'): ?>
            <form method="GET" action="rent_request.php">
                <input type="hidden" name="car_id" value="<?= $car['id'] ?>">
                <button type="submit" class="btn-rent">Rent Now</button>
            </form>
        <?php else: ?>
            <button class="btn-disabled" disabled>Not Available</button>
        <?php endif; ?>
    </div>
    <?php return ob_get_clean();
}
?>
<?php
// Start the session at the top to ensure session variables are available throughout the page
session_start();

// Include the database configuration file
include "includes/config.php";

// Check if the database connection is successful
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Existing search/filter code
$search = $_GET['search'] ?? '';
$type = $_GET['type'] ?? '';
$sort = $_GET['sort'] ?? 'none';



// Initialize a variable to store any error messages
$error = '';

// Fetch available cars from the database
$query = "SELECT id, name, model, type, price_per_day, image, status
 FROM cars
 WHERE (name LIKE '%$search%' OR model LIKE '%$search%') 
 AND (type = '$type' OR '$type' = '')";

// Add sorting
switch($sort) {
    case 'price_asc':
        $query .= " ORDER BY price_per_day ASC";
        break;
    case 'price_desc':
        $query .= " ORDER BY price_per_day DESC";
        break;
    case 'year_asc':
        $query .= " ORDER BY SUBSTRING(model, 1, 4)+0 ASC"; // Extract year from model
        break;
    case 'year_desc':
        $query .= " ORDER BY SUBSTRING(model, 1, 4)+0 DESC";
        break;
    case 'available':
        $query .= " ORDER BY status DESC";
        break;
    default:
        // No sorting
        break;
}


$result = mysqli_query($conn, $query);

// Check if the query execution was successful
if (!$result) {
    $error = "Failed to fetch cars: " . mysqli_error($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rental</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Header Section -->
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
        <h2>Available Cars</h2>
        
        <!-- Add this sorting dropdown above the car grid in <main> -->
        <div class="sort-filter">
               <form method="GET">
                   <label for="sort">Sort By:</label>
                   <select name="sort" id="sort">
                       <option value="none" <?= (!isset($_GET['sort']) || $_GET['sort'] == 'none') ? 'selected' : '' ?>>None</option>
                       <option value="price_asc" <?= isset($_GET['sort']) && $_GET['sort'] == 'price_asc' ? 'selected' : '' ?>>Price (Low to High)</option>
                       <option value="price_desc" <?= isset($_GET['sort']) && $_GET['sort'] == 'price_desc' ? 'selected' : '' ?>>Price (High to Low)</option>
                       <option value="year_asc" <?= isset($_GET['sort']) && $_GET['sort'] == 'year_asc' ? 'selected' : '' ?>>Model Year (Old to New)</option>
                       <option value="year_desc" <?= isset($_GET['sort']) && $_GET['sort'] == 'year_desc' ? 'selected' : '' ?>>Model Year (New to Old)</option>
                       <option value="available" <?= isset($_GET['sort']) && $_GET['sort'] == 'available' ? 'selected' : '' ?>>Availability</option>
                   </select>
                   <button type="submit">Apply</button>
               </form>
           </div>
        
        <?php if (!empty($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php elseif ($result && mysqli_num_rows($result) > 0): ?>
            <div class="container">

                <?php while ($row = mysqli_fetch_assoc($result)) { 
                    $carName = htmlspecialchars($row['name'] ?? 'Unknown Car');
                    $model = htmlspecialchars($row['model'] ?? 'Unknown Model'); 
                    $type = htmlspecialchars($row['type'] ?? 'Unknown Type');
                    $price = isset($row['price_per_day']) ? '$' . number_format($row['price_per_day'], 2) : 'N/A';
                    $image = !empty($row['image']) ? "images/" . htmlspecialchars($row['image']) : "images/default.png";
                    $status = $row['status'] ?? 'available';
                    $availability = ($status === 'available') ? "<span class='available'>Available</span>" : "<span class='not-available'>Not Available</span>";
                ?>

                    <div class="card">
                        <img src="<?php echo $image; ?>" alt="<?php echo $carName; ?>">
                        <h3><?php echo $carName; ?> (<?php echo $model; ?>)</h3>
                        <p><strong>Type:</strong> <?php echo $type; ?></p>
                        <p><strong>Price:</strong> <?php echo $price; ?>/day</p>
                        <p><strong>Status:</strong> <?php echo $availability; ?></p>
                        
                        <?php if ($status === 'available') { ?>
                            <form method="GET" action="rent_request.php">
                                <input type="hidden" name="car_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn btn-rent">Rent Now</button>
                            </form>
                        <?php } else { ?>
                            <button class="btn btn-disabled" disabled>Not Available</button>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        <?php else: ?>
            <p>No cars are currently available.</p>
        <?php endif; ?>
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

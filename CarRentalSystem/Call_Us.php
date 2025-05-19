<?php
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'car_rental_system';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // توليد رمز CSRF
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    // تنظيف البيانات المدخلة
    $name = $conn->real_escape_string(trim($_POST['name']));
    $phone = $conn->real_escape_string(trim($_POST['phone']));
    $message = $conn->real_escape_string(trim($_POST['message']));

    // التحقق من صحة البيانات
    if (empty($name) || empty($phone) || empty($message)) {
        $error = "All fields are required!";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $name)) { // تحقق من أن الاسم يحتوي على أحرف فقط
        $error = "Name must contain only letters!";
    } elseif (!preg_match("/^[0-9]{11}$/", $phone)) { // تحقق من أن رقم الهاتف مكون من 11 أرقام فقط
        $error = "Phone number must be 11 digits!";
    } else {
        // إدخال البيانات في قاعدة البيانات
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, phone, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $phone, $message);

        if ($stmt->execute()) {
            $success = "Message sent successfully!";
        } else {
            $error = "Error: " . $stmt->error;
        }
        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Contact Us - Car Rental</title>
    <!-- google fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@100..900&display=swap" rel="stylesheet">
    <!-- font awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <!-- bootstrap css -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <!-- Include CSS stylesheets -->
    <link rel="stylesheet" href="css/general.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/main-content.css">
    <link rel="stylesheet" href="css/buttons.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/call-us.css">
    <link rel="stylesheet" href="css/index.css">
</head>

<body>
        <!-- Website Header Section -->
    <header class="navbar navbar-expand-lg bg-body-tertiary d-flex justify-content-center align-items-center">
        <nav class="container-fluid d-flex justify-content-center align-items-center">
            <h1 class="navbar-brand">Car Rental Service</h1>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav">
                    <li class="nav-item"><a href="index.php" class="nav-link">Home</a></li>
                    <!-- Admin-only dashboard link -->
                    <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="admin/DashboardAdmin.php">Admin Dashboard</a></li>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['user'])): ?>
                        <li class="nav-item"><a class="nav-link" href="my_rental.php">My Rentals</a></li>
                        <li class="nav-item"><a class="nav-link" href="offers.php">Special Offers</a></li>
                        <li class="nav-item"><a class="nav-link" href="Login-Signup-Logout/logout.php">Logout</a></li>
                        <?php else: ?>
                            <li class="nav-item"><a class="nav-link" href="Login-Signup-Logout/login.php">Login</a></li>
                            <li class="nav-item"><a class="nav-link" href="Login-Signup-Logout/signup.php">Sign Up</a></li>
                            <li class="nav-item"><a class="nav-link" href="Login-Signup-Logout/logout.php">Logout</a></li>
                    <?php endif; ?>
                    
                    <li class="nav-item"><a class="nav-link" href="about us.html">About Us</a></li>
                    <li class="nav-item"><a class="nav-link active" href="Call_Us.php" aria-current="page">Contact Us</a></li>

                    
                    <li class="nav-item">
                        <a href="profile.php" class="nav-link profile-link">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
    <div class="line-messages">
    <!-- رسائل النجاح والخطأ -->
    <?php if (isset($success)) echo "<p class='success-msg'>$success</p>"; ?>
    <?php if (isset($error)) echo "<p class='error-msg' >$error</p>"; ?>
    </div>
    <section class="call-container d-flex flex-row justify-content-center">
    <div class="contact-info-cont">
        <h2 class="mt-3 d-block">Contact Information</h2>
        <div class="contact-info d-flex flex-column justify-content-center align-items-center">
        <ul>
            <li> <p class="fw-bold">Phone: <a class="p-span" href="tel:01012345678">010-123-45678</a></p></li>
            <li> <p class="fw-bold">WhatsApp: <a class="p-span" href="https://wa.me/201012345678" target="_blank">Chat with us on WhatsApp</a></p></li>
            <li> <p class="fw-bold">Email: <a class="p-span" href="mailto:info@car-rent.com">info@car-rent.com</a></p></li>
            <li> <p class="fw-bold">Address: <a class="p-span" href="#">1 Tahrir Street, Cairo</a></p></li>
        </ul>
        </div>
    </div>

    <div class="contact-form">
        <h2 class="mt-3 d-block">Contact Form</h2>
        <div class="contact-info d-flex flex-column justify-content-center align-items-center">
        <div class="f-row d-flex justify-content-between">
        <form action="" method="post" class="d-flex justify-content-center align-items-center flex-column w-100">
            <input class="form-control" type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input class="form-control" type="text" name="name" placeholder="Full Name" required>
            <input class="form-control" type="tel" name="phone" placeholder="Phone Number" required>
            <textarea  class="form-control" name="message" placeholder="Write your message here..." required></textarea>
            <button type="submit" class="btn">Send</button>
        </form>
        </div>
        </div>
    </div>
</section>
    
 <!-- Footer Section -->
    <footer>
        <div class="footer-container">
            <!-- Contact Information -->
            <div class="footer-section">
                <a href="Call_Us.php"> <h3>Contact Us</h3> </a>
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
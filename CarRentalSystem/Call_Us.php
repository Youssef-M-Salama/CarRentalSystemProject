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
    <link rel="stylesheet" href="css/CallUs.css">
</head>

<body>
    <header>
        <h1>Contact Us</h1>
        <p>We’re here to help – Reach out anytime</p>
    </header>

    <!-- رسائل النجاح والخطأ -->
    <?php if (isset($success)) echo "<p class='success-msg'>$success</p>"; ?>
    <?php if (isset($error)) echo "<p class='error-msg'>$error</p>"; ?>

    <section class="contact-info">
        <h2>Contact Information</h2>
        <ul>
            <li>Phone: <a href="tel:01012345678">010-123-45678</a></li>
            <li>WhatsApp: <a href="https://wa.me/201012345678" target="_blank">Chat with us on WhatsApp</a></li>
            <li>Email: <a href="mailto:info@car-rent.com">info@car-rent.com</a></li>
            <li>Address: 1 Tahrir Street, Cairo</li>
        </ul>
    </section>

    <section class="contact-form">
        <h2>Contact Form</h2>
        <form action="" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="tel" name="phone" placeholder="Phone Number" required>
            <textarea name="message" placeholder="Write your message here..." required></textarea>
            <button type="submit">Send</button>
        </form>
    </section>

    
    <footer>
        <p>© 2025 Car Rental Company. All rights reserved.</p>
    </footer>
</body>

</html>
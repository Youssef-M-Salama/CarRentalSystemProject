<?php  
session_start();  
include "includes/config.php";  
  
// لو المستخدم مش مسجل دخول  
if (!isset($_SESSION['user'])) {  
    header("Location: Login-Signup-Logout/login.php");  
    exit;  
}  
  
// جلب البيانات من الفورم  
$user_id = $_SESSION['user']['id'];  
$rental_id = $_POST['rental_id'];  
$car_id = $_POST['car_id'];  
$rating = $_POST['rating'];  
$comment = isset($_POST['comment']) ? mysqli_real_escape_string($conn, $_POST['comment']) : "";  
  
// التحقق إن التقييم مش مكرر  
$check_query = "SELECT * FROM rating WHERE user_id = $user_id AND rental_id = $rental_id";  
$result = mysqli_query($conn, $check_query);  
  
if (mysqli_num_rows($result) > 0) {  
    // لو قيّم قبل كده، نرجعه  
    header("Location: my_rental.php?message=You_have_already_rated_this_car");  
    exit;  
}  
  
// إدخال التقييم  
$insert_query = "INSERT INTO rating (user_id, car_id, rental_id, rating, comment, created_at)   
                 VALUES ($user_id, $car_id, $rental_id, $rating, '$comment', NOW())";  
  
if (mysqli_query($conn, $insert_query)) {  
    header("Location: my_rental.php?message=Rating_submitted_successfully");  
    exit;  
} else {  
    echo "Error: " . mysqli_error($conn);  
}  
?>
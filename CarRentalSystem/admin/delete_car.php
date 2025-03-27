<?php
session_start();
include "../includes/config.php";

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Get car ID from POST request
$car_id = $_POST['car_id'];

// Delete car from the database
$query = "DELETE FROM cars WHERE id = $car_id";
mysqli_query($conn, $query);

// Redirect back to the admin dashboard
header("Location: DashboardAdmin.php");
exit;
?>
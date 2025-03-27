<?php
session_start();
include "../includes/config.php";

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../Login-Signup-Logout/login.php");
    exit;
}

// Get the user ID from the POST request
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

// Delete the user from the database
$query = "DELETE FROM users WHERE id = $user_id";
mysqli_query($conn, $query);

// Redirect back to the admin dashboard
header("Location: DashboardAdmin.php");
exit;
?>
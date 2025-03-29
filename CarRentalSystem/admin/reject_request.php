<?php
session_start();
include "../includes/config.php";

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../Login-Signup-Logout/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'])) {
    $request_id = intval($_POST['request_id']);
    
    // Update the request status to rejected
    $query = "UPDATE rental_requests SET status = 'rejected' WHERE id = $request_id AND status = 'pending'";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        // Set error message
        $_SESSION['error'] = "Error rejecting request: " . mysqli_error($conn);
    }
    
    // Redirect back to the admin dashboard
    header("Location: DashboardAdmin.php");
    exit;
} else {
    // Invalid request, redirect back to the admin dashboard
    header("Location: DashboardAdmin.php");
    exit;
}
?>
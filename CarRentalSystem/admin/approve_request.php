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
    
    // Start a transaction to ensure data integrity
    mysqli_begin_transaction($conn);
    
    try {
        // Get the rental request details
        $query = "SELECT * FROM rental_requests WHERE id = $request_id AND status = 'pending'";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $request = mysqli_fetch_assoc($result);
            $car_id = $request['car_id'];
            $start_date = $request['start_date'];
            $end_date = $request['end_date'];
            
            // Update the request status to approved
            $update_query = "UPDATE rental_requests SET status = 'approved' WHERE id = $request_id";
            mysqli_query($conn, $update_query);
            
            // Update the car status to mark it as rented
            $update_car_query = "UPDATE cars SET status = 'rented' WHERE id = $car_id";
            mysqli_query($conn, $update_car_query);
            
            // Commit the transaction
            mysqli_commit($conn);
            
            // Redirect back to the admin dashboard
            header("Location: DashboardAdmin.php");
            exit;
        } else {
            throw new Exception("Rental request not found or already processed.");
        }
    } catch (Exception $e) {
        // Roll back the transaction in case of error
        mysqli_rollback($conn);
        
        // Set error message and redirect
        $_SESSION['error'] = "Error approving request: " . $e->getMessage();
        header("Location: DashboardAdmin.php");
        exit;
    }
} else {
    // Invalid request, redirect back to the admin dashboard
    header("Location: DashboardAdmin.php");
    exit;
}
?>
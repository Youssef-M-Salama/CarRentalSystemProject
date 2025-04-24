<?php
session_start();
require_once 'includes/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: Login-Signup-Logout/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $car_id = (int)$_POST['car_id'];
    $offer_id = (int)$_POST['offer_id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $user_id = $_SESSION['user']['id'];

    // Validate dates
    if (strtotime($start_date) > strtotime($end_date)) {
        $_SESSION['error'] = "End date must be after start date";
        header('Location: rent_car.php?car_id=' . $car_id . '&offer_id=' . $offer_id);
        exit();
    }

    // Check if car is available
    $car_check = $conn->prepare("SELECT status FROM cars WHERE id = ?");
    $car_check->bind_param("i", $car_id);
    $car_check->execute();
    $car_status = $car_check->get_result()->fetch_assoc()['status'];

    if ($car_status !== 'available') {
        $_SESSION['error'] = "This car is not available for rent";
        header('Location: rent_car.php?car_id=' . $car_id . '&offer_id=' . $offer_id);
        exit();
    }

    // Insert rental request
    $stmt = $conn->prepare("INSERT INTO rental_requests (user_id, car_id, start_date, end_date, status) VALUES (?, ?, ?, ?, 'pending')");
    $stmt->bind_param("iiss", $user_id, $car_id, $start_date, $end_date);

    if ($stmt->execute()) {
        // Update car status
        $update_car = $conn->prepare("UPDATE cars SET status = 'rented' WHERE id = ?");
        $update_car->bind_param("i", $car_id);
        $update_car->execute();

        $_SESSION['success'] = "Rental request submitted successfully!";
        header('Location: my_rental.php');
        exit();
    } else {
        $_SESSION['error'] = "Error submitting rental request";
        header('Location: rent_car.php?car_id=' . $car_id . '&offer_id=' . $offer_id);
        exit();
    }
} else {
    header('Location: index.php');
    exit();
} 
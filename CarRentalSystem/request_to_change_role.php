<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database configuration
require_once __DIR__ . '/includes/config.php';

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    $_SESSION['message'] = '
        <div class="notification-card danger">
            <div class="icon-container">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                    <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                    <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                </svg>
            </div>
            <div class="content">
                <h4>Authentication Required</h4>
                <p>Please log in to perform this action.</p>
            </div>
            <button class="close-btn">×</button>
        </div>';
    $_SESSION['msg_type'] = 'danger';
    header('Location: Login-Signup-Logout/login.php');
    exit;
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method Not Allowed');
}

// Get user information
$user_id = $_SESSION['user']['id'];
$current_role = $_SESSION['user']['role'] ?? 'client';
$requested_role = $_POST['request_type'] ?? '';

// Validate requested role
$valid_roles = ['client', 'premium'];
if (!in_array($requested_role, $valid_roles)) {
    $_SESSION['message'] = '
        <div class="notification-card warning">
            <div class="icon-container">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                    <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                    <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                </svg>
            </div>
            <div class="content">
                <h4>Invalid Request</h4>
                <p>Role change request contains invalid parameters.</p>
            </div>
            <button class="close-btn">×</button>
        </div>';
    $_SESSION['msg_type'] = 'warning';
    header('Location: Profile.php');
    exit;
}

// Check for existing pending request
$check_query = "SELECT id FROM role_change_requests 
                WHERE user_id = ? 
                AND status = 'pending'";
$stmt = $conn->prepare($check_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $_SESSION['message'] = '
        <div class="notification-card info">
            <div class="icon-container">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                    <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                    <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                </svg>
            </div>
            <div class="content">
                <h4>Pending Request</h4>
                <p>You already have a role change request under review.</p>
            </div>
            <button class="close-btn">×</button>
        </div>';
    $_SESSION['msg_type'] = 'info';
    $stmt->close();
    header('Location: Profile.php');
    exit;
}
$stmt->close();

// Insert new request
$insert_query = "INSERT INTO role_change_requests 
                (user_id, requested_role) 
                VALUES (?, ?)";
$stmt = $conn->prepare($insert_query);
$stmt->bind_param('is', $user_id, $requested_role);

if ($stmt->execute()) {
    $_SESSION['message'] = '
        <div class="notification-card success">
            <div class="icon-container">
                <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                    <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                    <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                </svg>
            </div>
            <div class="content">
                <h4>Request Received</h4>
                <p>Your role change request has been submitted for review.<br>
                We will notify you once processed.</p>
            </div>
            <button class="close-btn">×</button>
        </div>';
    $_SESSION['msg_type'] = 'success';
} else {
    $_SESSION['message'] = '
        <div class="notification-card danger">
            <div class="icon-container">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                    <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                    <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                </svg>
            </div>
            <div class="content">
                <h4>Request Failed</h4>
                <p>Error: ' . $stmt->error . '</p>
            </div>
            <button class="close-btn">×</button>
        </div>';
    $_SESSION['msg_type'] = 'danger';
}

$stmt->close();
$conn->close();

// Redirect back to profile
header('Location: Profile.php');
exit;
?>
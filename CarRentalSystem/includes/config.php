<?php
// Database configuration
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "car_rental_system";

// Create connection without selecting database
$conn = new mysqli($host, $user, $pass);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    // Select the database
    $conn->select_db($dbname);
} else {
    die("Error creating database: " . $conn->error);
}

// Set charset to utf8 for Arabic support
$conn->set_charset("utf8");

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session start if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to safely escape strings
function sanitize($conn, $input) {
    return mysqli_real_escape_string($conn, trim($input));
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user']);
}

// Function to check if user is admin
function isAdmin() {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}

// Function to check if user is premium
function isPremium() {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'premium';
}

// Function to check and fix database structure
function checkAndFixDatabase($conn) {
    // Disable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");

    // Drop all existing tables
    $tables = ['rating', 'payments', 'role_change_requests', 'rental_requests', 'offers', 'cars', 'users'];
    foreach ($tables as $table) {
        $conn->query("DROP TABLE IF EXISTS $table");
    }

    // Re-enable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");

    // Create users table
    $sql = "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'client', 'premium') NOT NULL DEFAULT 'client',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->query($sql);

    // Create cars table
    $sql = "CREATE TABLE cars (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        model VARCHAR(50) NOT NULL,
        type ENUM('Sedan', 'SUV', 'Crossover') NOT NULL,
        price_per_day DECIMAL(10, 2) NOT NULL,
        status ENUM('available', 'rented', 'maintenance') NOT NULL DEFAULT 'available',
        image VARCHAR(255),
        category ENUM('free', 'premium') NOT NULL DEFAULT 'free',
        description TEXT,
        features TEXT,
        average_rating DECIMAL(3,2) DEFAULT 0.00
    )";
    $conn->query($sql);

    // Create offers table
    $sql = "CREATE TABLE IF NOT EXISTS offers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(100) NOT NULL,
        description TEXT NOT NULL,
        discount_percentage DECIMAL(5,2) NOT NULL,
        user_type ENUM('client', 'premium', 'all') NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('active', 'inactive') DEFAULT 'active',
        car_id INT,
        CONSTRAINT fk_offer_car FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE SET NULL
    )";
    $conn->query($sql);

    // Create rental_requests table
    $sql = "CREATE TABLE rental_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        car_id INT NOT NULL,
        offer_id INT,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        total_price DECIMAL(10, 2) NOT NULL,
        status ENUM('pending', 'approved', 'rejected', 'completed') NOT NULL DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
        FOREIGN KEY (offer_id) REFERENCES offers(id) ON DELETE SET NULL
    )";
    $conn->query($sql);

    // Create role_change_requests table
    $sql = "CREATE TABLE role_change_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        requested_role ENUM('client', 'premium') NOT NULL,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $conn->query($sql);

    // Create payments table
    $sql = "CREATE TABLE payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        rental_id INT NOT NULL,
        amount DECIMAL(10, 2) NOT NULL,
        payment_method ENUM('cash', 'credit_card', 'bank_transfer') NOT NULL,
        status ENUM('pending', 'completed', 'failed') NOT NULL DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (rental_id) REFERENCES rental_requests(id) ON DELETE CASCADE
    )";
    $conn->query($sql);

    // Create rating table
    $sql = "CREATE TABLE rating (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        car_id INT NOT NULL,
        rental_id INT NOT NULL,
        rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
        comment TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
        FOREIGN KEY (rental_id) REFERENCES rental_requests(id) ON DELETE CASCADE
    )";
    $conn->query($sql);

    // Insert sample data
    // Users
    $users = [
        ['username' => 'Admin', 'email' => 'admin@carrental.com', 'password' => password_hash('password', PASSWORD_DEFAULT), 'role' => 'admin'],
        ['username' => 'Premium User', 'email' => 'premium@carrental.com', 'password' => password_hash('password', PASSWORD_DEFAULT), 'role' => 'premium'],
        ['username' => 'Regular User', 'email' => 'user@carrental.com', 'password' => password_hash('password', PASSWORD_DEFAULT), 'role' => 'client']
    ];

    foreach ($users as $user) {
        $sql = "INSERT INTO users (username, email, password, role) VALUES (
            '" . $conn->real_escape_string($user['username']) . "',
            '" . $conn->real_escape_string($user['email']) . "',
            '" . $conn->real_escape_string($user['password']) . "',
            '" . $conn->real_escape_string($user['role']) . "'
        )";
        $conn->query($sql);
    }

    // Cars
    $cars = [
        ['name' => 'Toyota', 'model' => 'Camry 2023', 'type' => 'Sedan', 'price_per_day' => 50.00, 'image' => 'toyota-camry.jpg', 'status' => 'available'],
        ['name' => 'Honda', 'model' => 'Civic 2023', 'type' => 'Sedan', 'price_per_day' => 45.00, 'image' => 'honda-civic.jpg', 'status' => 'available'],
        ['name' => 'BMW', 'model' => 'X5 2023', 'type' => 'SUV', 'price_per_day' => 100.00, 'image' => 'bmw-x5.jpg', 'status' => 'available']
    ];

    foreach ($cars as $car) {
        $sql = "INSERT INTO cars (name, model, type, price_per_day, image, status) VALUES (
            '" . $conn->real_escape_string($car['name']) . "',
            '" . $conn->real_escape_string($car['model']) . "',
            '" . $conn->real_escape_string($car['type']) . "',
            " . floatval($car['price_per_day']) . ",
            '" . $conn->real_escape_string($car['image']) . "',
            '" . $conn->real_escape_string($car['status']) . "'
        )";
        $conn->query($sql);
    }

    // Offers
    $offers = [
        ['title' => 'Summer Special', 'description' => 'Get 20% off on all sedans', 'car_id' => 1, 'discount_percentage' => 20, 'start_date' => date('Y-m-d'), 'end_date' => date('Y-m-d', strtotime('+30 days')), 'status' => 'active', 'user_type' => 'all'],
        ['title' => 'Premium Member Offer', 'description' => 'Exclusive 30% discount for premium members', 'car_id' => 3, 'discount_percentage' => 30, 'start_date' => date('Y-m-d'), 'end_date' => date('Y-m-d', strtotime('+30 days')), 'status' => 'active', 'user_type' => 'premium']
    ];

    foreach ($offers as $offer) {
        $sql = "INSERT INTO offers (title, description, car_id, discount_percentage, start_date, end_date, status, user_type) VALUES (
            '" . $conn->real_escape_string($offer['title']) . "',
            '" . $conn->real_escape_string($offer['description']) . "',
            " . intval($offer['car_id']) . ",
            " . floatval($offer['discount_percentage']) . ",
            '" . $offer['start_date'] . "',
            '" . $offer['end_date'] . "',
            '" . $conn->real_escape_string($offer['status']) . "',
            '" . $conn->real_escape_string($offer['user_type']) . "'
        )";
        $conn->query($sql);
    }
}

// Execute the check and fix
checkAndFixDatabase($conn);
?>

<?php
// Database configuration
$host = "localhost";  // XAMPP default
$user = "root";       // Default XAMPP user
$pass = "";           // No password by default
$dbname = "car_rental_system"; // Database name

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
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
    $tables = [
        'users' => [
            'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
            'username' => 'VARCHAR(50) NOT NULL UNIQUE',
            'email' => 'VARCHAR(100) NOT NULL UNIQUE',
            'password' => 'VARCHAR(255) NOT NULL',
            'role' => 'ENUM("admin", "client", "premium") NOT NULL DEFAULT "client"',
            'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
        ],
        'cars' => [
            'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
            'name' => 'VARCHAR(100) NOT NULL',
            'model' => 'VARCHAR(50) NOT NULL',
            'type' => 'ENUM("Sedan", "SUV", "Crossover") NOT NULL',
            'price_per_day' => 'DECIMAL(10, 2) NOT NULL',
            'status' => 'ENUM("available", "rented", "maintenance") NOT NULL DEFAULT "available"',
            'image' => 'VARCHAR(255)',
            'category' => 'ENUM("free", "premium") NOT NULL DEFAULT "free"',
            'description' => 'TEXT',
            'features' => 'TEXT',
            'average_rating' => 'DECIMAL(3,2) DEFAULT 0.00'
        ],
        'offers' => [
            'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
            'title' => 'VARCHAR(100) NOT NULL',
            'description' => 'TEXT NOT NULL',
            'discount_percentage' => 'DECIMAL(5,2) NOT NULL',
            'user_type' => 'ENUM("client", "premium", "all") NOT NULL',
            'start_date' => 'DATE NOT NULL',
            'end_date' => 'DATE NOT NULL',
            'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'status' => 'ENUM("active", "inactive") DEFAULT "active"',
            'car_id' => 'INT',
            'CONSTRAINT fk_offer_car FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE SET NULL'
        ],
        'rental_requests' => [
            'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
            'user_id' => 'INT NOT NULL',
            'car_id' => 'INT NOT NULL',
            'offer_id' => 'INT',
            'start_date' => 'DATE NOT NULL',
            'end_date' => 'DATE NOT NULL',
            'total_price' => 'DECIMAL(10, 2) NOT NULL',
            'status' => 'ENUM("pending", "approved", "rejected", "completed") NOT NULL DEFAULT "pending"',
            'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'CONSTRAINT fk_rental_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE',
            'CONSTRAINT fk_rental_car FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE',
            'CONSTRAINT fk_rental_offer FOREIGN KEY (offer_id) REFERENCES offers(id) ON DELETE SET NULL'
        ],
        'role_change_requests' => [
            'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
            'user_id' => 'INT NOT NULL',
            'requested_role' => 'ENUM("client", "premium") NOT NULL',
            'status' => 'ENUM("pending", "approved", "rejected") DEFAULT "pending"',
            'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'CONSTRAINT fk_role_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE'
        ],
        
        'rating' => [
            'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
            'user_id' => 'INT NOT NULL',
            'car_id' => 'INT NOT NULL',
            'rental_id' => 'INT NOT NULL',
            'rating' => 'INT NOT NULL CHECK (rating BETWEEN 1 AND 5)',
            'comment' => 'TEXT',
            'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'CONSTRAINT fk_rating_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE',
            'CONSTRAINT fk_rating_car FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE',
            'CONSTRAINT fk_rating_rental FOREIGN KEY (rental_id) REFERENCES rental_requests(id) ON DELETE CASCADE'
        ]
    ];

    foreach ($tables as $table => $columns) {
        // Check if table exists
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows == 0) {
            // Create table
            $sql = "CREATE TABLE $table (";
            foreach ($columns as $column => $definition) {
                $sql .= "$column $definition, ";
            }
            $sql = rtrim($sql, ', ') . ")";
            $conn->query($sql);
        }
    }
}

// Execute the check and fix
checkAndFixDatabase($conn);
?>

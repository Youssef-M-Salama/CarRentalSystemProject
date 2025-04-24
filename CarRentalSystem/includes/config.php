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
            'name' => 'VARCHAR(100) NOT NULL',
            'email' => 'VARCHAR(100) NOT NULL UNIQUE',
            'password' => 'VARCHAR(255) NOT NULL',
            'role' => 'ENUM("admin", "premium", "client") NOT NULL DEFAULT "client"',
            'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
        ],
        'cars' => [
            'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
            'name' => 'VARCHAR(100) NOT NULL',
            'model' => 'VARCHAR(100) NOT NULL',
            'type' => 'VARCHAR(50) NOT NULL',
            'price_per_day' => 'DECIMAL(10,2) NOT NULL',
            'image' => 'VARCHAR(255)',
            'status' => 'ENUM("available", "rented", "maintenance") NOT NULL DEFAULT "available"',
            'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
        ],
        'offers' => [
            'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
            'title' => 'VARCHAR(100) NOT NULL',
            'description' => 'TEXT',
            'car_id' => 'INT NOT NULL',
            'discount_percentage' => 'INT NOT NULL',
            'start_date' => 'DATE NOT NULL',
            'end_date' => 'DATE NOT NULL',
            'status' => 'ENUM("active", "inactive") NOT NULL DEFAULT "active"',
            'user_type' => 'ENUM("client", "premium", "all") NOT NULL DEFAULT "all"',
            'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'FOREIGN KEY (car_id) REFERENCES cars(id)'
        ],
        'rental_requests' => [
            'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
            'user_id' => 'INT NOT NULL',
            'car_id' => 'INT NOT NULL',
            'offer_id' => 'INT',
            'start_date' => 'DATE NOT NULL',
            'end_date' => 'DATE NOT NULL',
            'total_price' => 'DECIMAL(10,2) NOT NULL',
            'status' => 'ENUM("pending", "approved", "rejected", "completed") NOT NULL DEFAULT "pending"',
            'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'FOREIGN KEY (user_id) REFERENCES users(id)',
            'FOREIGN KEY (car_id) REFERENCES cars(id)',
            'FOREIGN KEY (offer_id) REFERENCES offers(id)'
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

    // Add sample data if tables are empty
    $sample_data = [
        'users' => [
            ['name' => 'Admin', 'email' => 'admin@carrental.com', 'password' => password_hash('password', PASSWORD_DEFAULT), 'role' => 'admin'],
            ['name' => 'Premium User', 'email' => 'premium@carrental.com', 'password' => password_hash('password', PASSWORD_DEFAULT), 'role' => 'premium'],
            ['name' => 'Regular User', 'email' => 'user@carrental.com', 'password' => password_hash('password', PASSWORD_DEFAULT), 'role' => 'client']
        ],
        'cars' => [
            ['name' => 'Toyota', 'model' => 'Camry 2023', 'type' => 'Sedan', 'price_per_day' => 50.00, 'image' => 'toyota-camry.jpg', 'status' => 'available'],
            ['name' => 'Honda', 'model' => 'Civic 2023', 'type' => 'Sedan', 'price_per_day' => 45.00, 'image' => 'honda-civic.jpg', 'status' => 'available'],
            ['name' => 'BMW', 'model' => 'X5 2023', 'type' => 'SUV', 'price_per_day' => 100.00, 'image' => 'bmw-x5.jpg', 'status' => 'available']
        ],
        'offers' => [
            ['title' => 'Summer Special', 'description' => 'Get 20% off on all sedans', 'car_id' => 1, 'discount_percentage' => 20, 'start_date' => date('Y-m-d'), 'end_date' => date('Y-m-d', strtotime('+30 days')), 'status' => 'active', 'user_type' => 'all'],
            ['title' => 'Premium Member Offer', 'description' => 'Exclusive 30% discount for premium members', 'car_id' => 3, 'discount_percentage' => 30, 'start_date' => date('Y-m-d'), 'end_date' => date('Y-m-d', strtotime('+30 days')), 'status' => 'active', 'user_type' => 'premium']
        ]
    ];

    foreach ($sample_data as $table => $data) {
        $result = $conn->query("SELECT COUNT(*) as count FROM $table");
        $count = $result->fetch_assoc()['count'];
        
        if ($count == 0) {
            foreach ($data as $row) {
                $columns = implode(', ', array_keys($row));
                $values = implode(', ', array_map(function($value) use ($conn) {
                    return "'" . $conn->real_escape_string($value) . "'";
                }, $row));
                
                $sql = "INSERT INTO $table ($columns) VALUES ($values)";
                $conn->query($sql);
            }
        }
    }
}

// Execute the check and fix
checkAndFixDatabase($conn);
?>

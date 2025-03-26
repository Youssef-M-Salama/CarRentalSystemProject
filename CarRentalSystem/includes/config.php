<?php
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

?>

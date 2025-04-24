<?php
require_once 'includes/db_connection.php';

// Fetch all offers
$sql = "SELECT * FROM offers";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<h2>All Offers in Database:</h2>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Title</th><th>User Type</th><th>Status</th><th>Start Date</th><th>End Date</th></tr>";
    
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['title'] . "</td>";
        echo "<td>" . $row['user_type'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . $row['start_date'] . "</td>";
        echo "<td>" . $row['end_date'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No offers found in the database.";
}
?> 
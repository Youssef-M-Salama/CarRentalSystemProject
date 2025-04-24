<?php
require_once 'includes/db_connection.php';

// Fetch all offers with detailed information
$sql = "SELECT 
    o.*, 
    c.name as car_name, 
    c.model as car_model,
    c.status as car_status,
    DATEDIFF(o.end_date, CURDATE()) as days_remaining
FROM offers o 
LEFT JOIN cars c ON o.car_id = c.id 
ORDER BY o.id DESC";

$result = $conn->query($sql);

echo "<h2>Debug Information - All Offers</h2>";
echo "<p>Current Date: " . date('Y-m-d') . "</p>";

if ($result->num_rows > 0) {
    echo "<table border='1' style='width:100%'>";
    echo "<tr>
        <th>ID</th>
        <th>Title</th>
        <th>User Type</th>
        <th>Status</th>
        <th>Start Date</th>
        <th>End Date</th>
        <th>Days Remaining</th>
        <th>Car</th>
        <th>Car Status</th>
        <th>Discount</th>
    </tr>";
    
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['title'] . "</td>";
        echo "<td>" . $row['user_type'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . $row['start_date'] . "</td>";
        echo "<td>" . $row['end_date'] . "</td>";
        echo "<td>" . $row['days_remaining'] . "</td>";
        echo "<td>" . ($row['car_name'] ? $row['car_name'] . ' ' . $row['car_model'] : 'No Car') . "</td>";
        echo "<td>" . $row['car_status'] . "</td>";
        echo "<td>" . $row['discount_percentage'] . "%</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No offers found in the database.</p>";
}

// Check active offers for regular users
$current_date = date('Y-m-d');
$sql = "SELECT COUNT(*) as count FROM offers 
        WHERE status = 'active' 
        AND start_date <= ? 
        AND end_date >= ?
        AND (user_type = 'client' OR user_type = 'all')";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $current_date, $current_date);
$stmt->execute();
$active_offers = $stmt->get_result()->fetch_assoc()['count'];

echo "<h3>Active Offers for Regular Users: " . $active_offers . "</h3>";

// Check active offers for premium users
$sql = "SELECT COUNT(*) as count FROM offers 
        WHERE status = 'active' 
        AND start_date <= ? 
        AND end_date >= ?
        AND (user_type = 'premium' OR user_type = 'all')";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $current_date, $current_date);
$stmt->execute();
$active_premium_offers = $stmt->get_result()->fetch_assoc()['count'];

echo "<h3>Active Offers for Premium Users: " . $active_premium_offers . "</h3>";
?> 
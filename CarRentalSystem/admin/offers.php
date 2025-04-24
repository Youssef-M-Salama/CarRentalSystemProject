<?php
session_start();
include "../includes/config.php";

if (isset($_GET['id'])) {
    $offer_id = intval($_GET['id']);
    
    // Use prepared statements
    $stmt = $conn->prepare("DELETE FROM offers WHERE id = ?");
    $stmt->bind_param("i", $offer_id);
    
    if ($stmt->execute()) {
        $_SESSION['offer_msg'] = "Offer deleted successfully!";
        $_SESSION['offer_msg_type'] = 'success';
    } else {
        $_SESSION['offer_msg'] = "Error deleting offer: " . $stmt->error;
        $_SESSION['offer_msg_type'] = 'error';
    }
    $stmt->close();
} else {
    $_SESSION['offer_msg'] = "Invalid offer ID";
    $_SESSION['offer_msg_type'] = 'error';
}

header("Location: DashboardAdmin.php#offers");
exit;
?>
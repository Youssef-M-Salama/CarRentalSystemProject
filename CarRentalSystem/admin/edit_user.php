<?php
session_start();
include "../includes/config.php";

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../Login-Signup-Logout/login.php");
    exit;
}

// Get the user ID from the query string
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

// Fetch the user's details from the database
$query = "SELECT * FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Handle form submission to update the user's role
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_role = mysqli_real_escape_string($conn, $_POST['role']);

    // Update the user's role in the database
    $update_query = "UPDATE users SET role = '$new_role' WHERE id = $user_id";
    mysqli_query($conn, $update_query);

    // Redirect back to the admin dashboard
    header("Location: DashboardAdmin.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="../css/general.css">
    <link rel="stylesheet" href="../css/forms.css">
    <link rel="stylesheet" href="../css/header.css">
</head>
<body>
    <!-- Header Section -->
    <header>
        <h1>Edit User</h1>
        <nav>
            <ul>
                <li><a href="DashboardAdmin.php">Back to Admin Dashboard</a></li>
                <li><a href="../Login-Signup-Logout/logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <!-- Main Content -->
    <main>
        <h2>Edit User: <?php echo htmlspecialchars($user['username']); ?></h2>
        <form method="POST" action="edit_user.php?user_id=<?php echo $user['id']; ?>">
            <label for="role">Role:</label>
            <select name="role" id="role" required>
                <option value="client" <?php echo ($user['role'] === 'client') ? 'selected' : ''; ?>>Client</option>
                <option value="admin" <?php echo ($user['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                <option value="premium" <?php echo ($user['role'] === 'premium') ? 'selected' : ''; ?>>premium</option>
            </select>
            <button type="submit">Update Role</button>
        </form>
    </main>
</body>
</html>
<?php
session_start();
include "../includes/config.php";
// Redirect if not admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}
// Handle actions with prepared statements
if (isset($_GET['action'], $_GET['id'])) {
    $request_id = intval($_GET['id']);
    $action = $_GET['action'];
    // Get request details safely
    $stmt = $conn->prepare("
        SELECT r.*, u.username, u.email 
        FROM role_change_requests r
        JOIN users u ON r.user_id = u.id
        WHERE r.id = ?
    ");
    $stmt->bind_param('i', $request_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $request = $result->fetch_assoc();
    if ($request && $request['status'] === 'pending') {
        if ($action === 'approve') {
            // Update user role
            $update_user = $conn->prepare("
                UPDATE users 
                SET role = ? 
                WHERE id = ?
            ");
            $update_user->bind_param('si', $request['requested_role'], $request['user_id']);
            $update_user->execute();
            // Update request status
            $update_request = $conn->prepare("
                UPDATE role_change_requests 
                SET status = 'approved' 
                WHERE id = ?
            ");
            $update_request->bind_param('i', $request_id);
            $update_request->execute();
            $_SESSION['message'] = 'Request approved successfully';
            $_SESSION['msg_type'] = 'success';
        } elseif ($action === 'reject') {
            $update_request = $conn->prepare("
                UPDATE role_change_requests 
                SET status = 'rejected' 
                WHERE id = ?
            ");
            $update_request->bind_param('i', $request_id);
            $update_request->execute();
            $_SESSION['message'] = 'Request rejected';
            $_SESSION['msg_type'] = 'warning';
        }
    }
}
// Get all pending requests with prepared statement
$stmt = $conn->prepare("
    SELECT r.*, u.username, u.email 
    FROM role_change_requests r
    JOIN users u ON r.user_id = u.id
    WHERE r.status = 'pending'
");
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Role Change Requests - Admin Panel</title>

    <!-- Styles -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css ">
    <link rel="stylesheet" href="../css/AdminDashboard.css">
    <link rel="stylesheet" href="../css/header.css">

    <style>
        main {
            padding: 40px;
        }

        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-family: "League Spartan", sans-serif;
            font-size: 0.95rem;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #9AA6B2;
            color: white;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            background-color: #9AA6B2;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .btn:hover {
            background-color: #BCCCDC;
            transform: translateY(-1px);
        }

        .btn-danger {
            background-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .notification {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            animation: fadeIn 0.3s ease;
        }

        .notification.success {
            background: #ecffe8;
            color: #1e7e34;
        }

        .notification.warning {
            background: #fffbe4;
            color: #7a5d00;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .header-actions h2 {
            margin: 0;
        }
    </style>
</head>
<body>

<!-- Header Section -->
<header class="navbar navbar-expand-lg bg-body-tertiary d-flex justify-content-center align-items-center">
    <nav class="container-fluid d-flex justify-content-center align-items-center">
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <h1 class="navbar-brand">Role Change Requests</h1>
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="DashboardAdmin.php">Back to Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="../index.php">Back to Home</a></li>
                <li class="nav-item"><a class="nav-link" href="../Login-Signup-Logout/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>
</header>

<main>
    <?php if (isset($_SESSION['message'])): ?>
        <div class="notification <?= $_SESSION['msg_type'] ?>">
            <?= htmlspecialchars($_SESSION['message']) ?>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['msg_type']); ?>
    <?php endif; ?>

    <div class="header-actions">
        <h2>Pending Role Change Requests</h2>
    </div>

    <div class="table-container">
        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Requested Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= ucfirst($row['requested_role']) ?></td>
                            <td class="actions">
                                <a href="?action=approve&id=<?= $row['id'] ?>" 
                                   class="btn" 
                                   onclick="return confirm('Approve this request?')">Approve</a>
                                <a href="?action=reject&id=<?= $row['id'] ?>" 
                                   class="btn btn-danger" 
                                   onclick="return confirm('Reject this request?')">Reject</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align:center; padding:20px;">No pending role change requests.</p>
        <?php endif; ?>
    </div>
</main>

</body>
</html>
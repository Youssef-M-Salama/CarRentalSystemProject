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
    <title>Role Requests Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #2d6efd;
            --success: #198754;
            --danger: #dc3545;
            --warning: #ffc107;
            --gray-100: #f8f9fa;
            --gray-300: #dee2e6;
        }

        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
            color: #343a40;
        }

        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #2c3e50;
            font-weight: 600;
            margin: 0;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
            border: none;
        }

        .btn-primary:hover {
            background-color: #1a5bd1;
        }

        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background-color: var(--gray-100);
            color: #495057;
            font-weight: 600;
            padding: 15px;
            text-align: left;
            border-bottom: 2px solid var(--gray-300);
        }

        td {
            padding: 15px;
            border-bottom: 1px solid var(--gray-300);
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .approve-btn {
            background-color: var(--success);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
        }

        .reject-btn {
            background-color: var(--danger);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
        }

        .notification {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            position: relative;
            animation: slideIn 0.3s ease;
        }

        .notification.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .notification.warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .notification.danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .notification .close {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-weight: bold;
            color: inherit;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-10px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            table, th, td {
                display: block;
            }
            
            th {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }
            
            tr {
                margin-bottom: 15px;
                padding: 10px;
                border: 1px solid var(--gray-300);
            }
            
            td {
                border: none;
                position: relative;
                padding-left: 50%;
                text-align: right;
            }
            
            td::before {
                content: attr(data-label);
                position: absolute;
                left: 10px;
                width: 45%;
                padding-right: 10px;
                font-weight: 600;
                text-align: left;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Display notifications -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="notification <?= $_SESSION['msg_type'] ?>">
                <?= $_SESSION['message'] ?>
                <span class="close">&times;</span>
            </div>
            <?php unset($_SESSION['message'], $_SESSION['msg_type']); ?>
        <?php endif; ?>

        <div class="header">
            <h1>Role Change Requests</h1>
            <a href="DashboardAdmin.php" class="btn btn-primary">
                <i class="fas fa-home"></i> Dashboard
            </a>
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
                                <td data-label="User"><?= htmlspecialchars($row['username']) ?></td>
                                <td data-label="Email"><?= htmlspecialchars($row['email']) ?></td>
                                <td data-label="Requested Role"><?= ucfirst($row['requested_role']) ?></td>
                                <td data-label="Actions" class="actions">
                                    <a href="?action=approve&id=<?= $row['id'] ?>" 
                                       class="approve-btn" 
                                       onclick="return confirm('Approve this request?')">
                                        <i class="fas fa-check"></i> Approve
                                    </a>
                                    <a href="?action=reject&id=<?= $row['id'] ?>" 
                                       class="reject-btn" 
                                       onclick="return confirm('Reject this request?')">
                                        <i class="fas fa-times"></i> Reject
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="notification info">
                    <i class="fas fa-info-circle"></i> No pending requests
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Notification close functionality
        document.querySelectorAll('.notification .close').forEach(btn => {
            btn.addEventListener('click', () => {
                const notification = btn.parentElement;
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 300);
            });
        });
    </script>
</body>
</html>
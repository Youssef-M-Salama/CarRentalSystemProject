<?php
session_start();
include "../includes/config.php";

// Redirect if not admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

// Handle mark as read action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message_id'])) {
    $message_id = intval($_POST['message_id']);
    
    $stmt = $conn->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?");
    $stmt->bind_param('i', $message_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = 'Message marked as read';
        $_SESSION['msg_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Operation failed';
        $_SESSION['msg_type'] = 'danger';
    }
    
    header("Location: admin_read_messages.php");
    exit;
}

// Get all unread messages with prepared statement
$stmt = $conn->prepare("
    SELECT * FROM contact_messages 
    WHERE status = 'unread'
    ORDER BY created_at DESC
");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unread Messages</title>
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

        .btn-success {
            background-color: var(--success);
            color: white;
            border: none;
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
            min-width: 800px;
        }

        th, td {
            padding: 15px;
            text-align: left;
            vertical-align: middle;
        }

        th {
            background-color: var(--gray-100);
            color: #495057;
            font-weight: 600;
            border-bottom: 2px solid var(--gray-300);
        }

        td {
            border-bottom: 1px solid var(--gray-300);
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .actions {
            display: flex;
            gap: 10px;
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

        
        <!-- Status Message Display -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="notification <?= $_SESSION['msg_type'] ?>">
                <?= $_SESSION['message'] ?>
                <span class="close">&times;</span>
            </div>
            <?php unset($_SESSION['message'], $_SESSION['msg_type']); ?>
        <?php endif; ?>

        <div class="header">
            <h1>Unread Messages</h1>
        </div>

        <div class="table-container">
            <?php if ($result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Message</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td data-label="Name"><?= htmlspecialchars($row['name']) ?></td>
                                <td data-label="Email"><?= htmlspecialchars($row['phone']) ?></td>
                                <td data-label="Message"><?= nl2br(htmlspecialchars($row['message'])) ?></td>
                                <td data-label="Date"><?= date('M j, Y H:i', strtotime($row['created_at'])) ?></td>
                                <td data-label="Actions" class="actions">
                                    <form method="POST" style="display: inline">
                                        <input type="hidden" name="message_id" value="<?= $row['id'] ?>">
                                        <button type="submit" class="btn btn-success" onclick="return confirm('Mark this message as read?')">
                                            <i class="fas fa-check"></i> Done
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="notification info">
                    <i class="fas fa-inbox"></i> No unread messages
                </div>
            <?php endif; ?>
        </div>
                <br><br>
        <a href= DashboardAdmin.php > Home </a>

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
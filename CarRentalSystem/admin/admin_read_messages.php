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
    <!-- google fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@100..900&display=swap" rel="stylesheet">
    <!-- font awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <!-- bootstrap css -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <!-- Include CSS stylesheets -->
    <link rel="stylesheet" href="../css/general.css">
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/main-content.css">
    <link rel="stylesheet" href="../css/buttons.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="../css/messages.css">
    <link rel="stylesheet" href="../css/index.css">

    <!-- Internal Table Styles -->
    <style>
        /* Table Styles */
        table {
            margin: 0 auto;
            width: 70%;
            border-collapse: collapse;
            border-radius: 40px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            font-family: "League Spartan", sans-serif;
            font-size: 0.95rem;
        }

        th,
        td {
            font-size: 16px;
            text-transform: capitalize;
            color: #006A71;
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        td img {
            width: 50%;
        }
        th {
            background-color: #006A71;
            color: white;
        }

        tr:hover {
            background-color: #F2F8FA;
        }
    </style>
</head>
<body>
    <!-- Website Header Section -->
    <header class="navbar navbar-expand-lg bg-body-tertiary d-flex justify-content-center align-items-center">
      <nav class="container-fluid d-flex justify-content-center align-items-center">
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <h1 class="navbar-brand">Car Rental Service</h1>
          <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" href="../index.php">Home</a></li>
            <?php if ($_SESSION['user']['role'] === 'admin'): ?>
              <li class="nav-item"><a class="nav-link" href="../admin/DashboardAdmin.php">Admin Dashboard</a></li>
              <li class="nav-item"><a class="nav-link" href="../Login-Signup-Logout/logout.php">Logout</a></li>
            <?php endif; ?>
          </ul>
        </div>
      </nav>
    </header>
    
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
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['phone']) ?></td>
                                <td><?= nl2br(htmlspecialchars($row['message'])) ?></td>
                                <td><?= date('M j, Y H:i', strtotime($row['created_at'])) ?></td>
                                <td>
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
    </div>
 <!-- Footer Section -->
    <footer>
        <div class="footer-container">
            <!-- Contact Information -->
            <div class="footer-section">
                <a href="Call_Us.php"> <h3>Contact Us</h3> </a>
                <a href="mailto:info@carrentalservice.com" >Email: info@carrentalservice.com</a>
                <a href="01234567890" >Phone: 01234567890</a>
            </div>
            
            <!-- Social Media Links -->
            <div class="footer-section">
                <h3>Follow Us</h3>
                <ul class="social-links">
                    <li><a href="#"><i class="fab fa-facebook"></i></a></li>
                    <li><a href="https://github.com/Youssef-M-Salama/CarRentalSystemProject"><i class="fa-brands fa-github"></i></a></li>
                    <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                    <li><a href="#"><i class="fab fa-linkedin"></i></a></li>
                </ul>
            </div>
            
            <!-- Newsletter Subscription -->
            <div class="footer-section">
                <h3>Subscribe</h3>
                <form>
                    <input type="email" placeholder="Enter your email" required>
                    <button type="submit">Subscribe</button>
                </form>
            </div>
        </div>
        
        <!-- Copyright Notice -->
        <div class="copyright">
            <p>&copy; 2025 Car Rental Service. All rights reserved.</p>
        </div>
    </footer>

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
    <!-- bootstrap js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
</body>
</html>

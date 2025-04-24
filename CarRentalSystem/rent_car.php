<?php
session_start();
require_once 'includes/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: Login-Signup-Logout/login.php');
    exit();
}

// Get car and offer details
$car_id = isset($_GET['car_id']) ? (int)$_GET['car_id'] : 0;
$offer_id = isset($_GET['offer_id']) ? (int)$_GET['offer_id'] : 0;

// Fetch car and offer details
$sql = "SELECT c.*, o.discount_percentage, o.title as offer_title 
        FROM cars c 
        LEFT JOIN offers o ON o.id = ? AND o.car_id = c.id 
        WHERE c.id = ? AND c.status = 'available'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $offer_id, $car_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: offers.php');
    exit();
}

$car = $result->fetch_assoc();
$has_offer = !empty($car['discount_percentage']);
$discounted_price = $has_offer ? 
    $car['price_per_day'] * (1 - ($car['discount_percentage'] / 100)) : 
    $car['price_per_day'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent Car - Car Rental System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .rental-form {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 30px;
        }
        .car-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .car-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 15px;
        }
        .price-info {
            background: #e9ecef;
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
        }
        .original-price {
            text-decoration: line-through;
            color: #dc3545;
        }
        .discounted-price {
            color: #28a745;
            font-weight: bold;
            font-size: 1.2em;
        }
        .offer-badge {
            background: #28a745;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
            margin: 10px 0;
        }
        .date-picker {
            border: 1px solid #ced4da;
            border-radius: 5px;
            padding: 8px;
            width: 100%;
        }
        .submit-button {
            background: linear-gradient(135deg, #28a745 0%, #218838 100%);
            border: none;
            padding: 12px 25px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .submit-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Car Rental System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="my_rental.php">My Rentals</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="offers.php">Special Offers</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about us.html">About Us</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="Login-Signup-Logout/logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="rental-form">
                    <h2 class="text-center mb-4">Rent <?php echo htmlspecialchars($car['name'] . ' ' . $car['model']); ?></h2>
                    
                    <div class="car-info">
                        <img src="images/<?php echo htmlspecialchars($car['image']); ?>" 
                             alt="<?php echo htmlspecialchars($car['name']); ?>" 
                             class="car-image">
                        
                        <h4><?php echo htmlspecialchars($car['name'] . ' ' . $car['model']); ?></h4>
                        <p><strong>Type:</strong> <?php echo htmlspecialchars($car['type']); ?></p>
                        
                        <?php if ($has_offer): ?>
                            <div class="offer-badge">
                                <i class="fas fa-percentage"></i> <?php echo $car['discount_percentage']; ?>% OFF
                                <small class="d-block"><?php echo htmlspecialchars($car['offer_title']); ?></small>
                            </div>
                        <?php endif; ?>
                        
                        <div class="price-info">
                            <?php if ($has_offer): ?>
                                <p class="mb-1">
                                    <span class="original-price">$<?php echo number_format($car['price_per_day'], 2); ?>/day</span>
                                    <span class="discounted-price">$<?php echo number_format($discounted_price, 2); ?>/day</span>
                                </p>
                            <?php else: ?>
                                <p class="mb-1">
                                    <span class="discounted-price">$<?php echo number_format($car['price_per_day'], 2); ?>/day</span>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <form action="process_rental.php" method="POST">
                        <input type="hidden" name="car_id" value="<?php echo $car_id; ?>">
                        <input type="hidden" name="offer_id" value="<?php echo $offer_id; ?>">
                        
                        <div class="mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control date-picker" id="start_date" name="start_date" 
                                   min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control date-picker" id="end_date" name="end_date" 
                                   min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="total_price" class="form-label">Estimated Total Price</label>
                            <input type="text" class="form-control" id="total_price" readonly>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-success submit-button">
                                <i class="fas fa-check"></i> Confirm Rental
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Calculate total price when dates change
        document.getElementById('start_date').addEventListener('change', calculateTotal);
        document.getElementById('end_date').addEventListener('change', calculateTotal);

        function calculateTotal() {
            const startDate = new Date(document.getElementById('start_date').value);
            const endDate = new Date(document.getElementById('end_date').value);
            
            if (startDate && endDate && startDate <= endDate) {
                const days = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
                const pricePerDay = <?php echo $discounted_price; ?>;
                const total = days * pricePerDay;
                
                document.getElementById('total_price').value = '$' + total.toFixed(2);
            } else {
                document.getElementById('total_price').value = '';
            }
        }
    </script>
</body>
</html> 
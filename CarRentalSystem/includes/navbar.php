<?php
$current_page = basename($_SERVER['PHP_SELF']);

// Debug information
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug session data
echo "<!-- Debug Info: ";
print_r($_SESSION);
echo " -->";

// Debug user role
if (isset($_SESSION['user'])) {
    echo "<!-- Debug Info: User Role = " . $_SESSION['user']['role'] . " -->";
}
?>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="../index.php">Car Rental Service</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" href="../index.php">Home</a>
                </li>
                <?php if (isset($_SESSION['user'])): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'my_rentals.php' ? 'active' : ''; ?>" href="../my_rentals.php">My Rentals</a>
                    </li>
                    <?php if ($_SESSION['user']['role'] === 'premium'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'offers.php' ? 'active' : ''; ?>" href="premium/offers.php">Special Offers</a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'about.php' ? 'active' : ''; ?>" href="../about.php">About Us</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <?php if (isset($_SESSION['user'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../Login-Signup-Logout/logout.php">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../Login-Signup-Logout/login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../Login-Signup-Logout/signup.php">Sign Up</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav> 
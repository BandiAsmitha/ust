
<?php
session_start();

if (!isset($_SESSION['mobile'])) {
    header("Location: user_login.php");
    exit();
}

$mobile = $_SESSION['mobile'];
$flask_url = "http://localhost:5000/?mobile=" . urlencode($mobile);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unified Shopping and Tracking</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Body */
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background: url('http://localhost/UST/ust/bg1.jpg') no-repeat center center/cover; /* Use a single background image */
            color: #333;
            min-height: 100vh;
            text-align: center;
        }

        /* Header */
        header {
            background-color: rgba(44, 62, 80, 0.5); /* Properly formatted background property */
    color: white;
    padding: 1rem 0;
}

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .logo h1 {
            font-size: 1.8rem;
        }

        .logo p {
            font-size: 1rem;
            margin-top: 5px;
        }

        .nav-links {
            list-style-type: none;
            display: flex;
            gap: 15px;
        }

        .nav-links li {
            display: inline;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 1.1rem;
            padding: 5px 10px;
            transition: background-color 0.3s ease;
        }

        .nav-links a:hover {
            background-color: #16a085;
            border-radius: 5px;
        }

        /* Hero Section */
        .hero {
            padding: 5rem 0;
            color: white;
        }

        .hero h2 {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .hero p {
            font-size: 1.5rem;
            margin-bottom: 2rem;
        }

        /* Shopping Apps Section */
        .shopping-apps {
            padding: 3rem 20px;
        }

        .shopping-apps h2 {
            font-size: 2.5rem;
            margin-bottom: 2rem;
            color: white;
        }

        .app-logos {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .app-logo {
            color: #000;;
            text-align: center;
            width: 150px;
            margin: 0 auto;
        }

        .app-logo img {
            width: 100%;
            height: 100px;
            object-fit: contain;
        }

        .app-logo h3 {
            margin-top: 10px;
            font-size: 1.2rem;
            color: white;
        }

        /* User Account Section */
        .user-account {
            padding: 3rem 20px;
        }

        .user-account h2 {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            color: white;
        }

        .user-account p {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            color: white;
        }

        .user-account a {
            padding: 10px 20px;
            background-color: #e74c3c;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1.1rem;
            transition: background-color 0.3s ease;
            margin: 5px;
        }

        .user-account a:hover {
            background-color: #c0392b;
        }

        /* Footer */
        footer {
            background-color: rgba(44, 62, 80, 0.8); /* Semi-transparent footer background */
            color: white;
            padding: 2rem 4rem;
        }

        .footer-links {
            list-style-type: none;
            margin-top: 20px;
        }

        .footer-links li {
            display: inline;
            margin: 0 10px;
        }

        .footer-links a {
            color: white;
            text-decoration: none;
            font-size: 1rem;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

    </style>
</head>
<body>
    <!-- Header Section -->
    <header>
        <nav>
            <div class="logo">
                <h1>Unified Shopping and Tracking System</h1>
                <p>Your One-Stop Shop & Tracker</p>
            </div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="wishlist.php">Wishlist</a></li>
                <li><a href="orders.php">My Orders</a></li>
                <li><a href="<?= $flask_url ?>" target="_blank">
                Account Dashboard
                </a></li>
                <li><a href="cart.php">Cart</a></li>
                <li><a href="logout.php">Logout</a></li>

            </ul>
        </nav>
    </header>
    <!-- Shopping Apps Section -->
    <section class="shopping-apps">
        <h2>Shop on Your Favorite Platforms</h2>
        <div class="app-logos">
            <!-- Amazon -->
            <div class="app-logo">
                <a href="checkAccount.php?platform=amazon" target="_blank">
                    <img src="amazon_logo.png" alt="Amazon">
                    <h3>Amazon</h3>
                </a>
            </div>
            <!-- Flipkart -->
            <div class="app-logo">
                <a href="checkAccount.php?platform=flipkart" target="_blank">
                    <img src="flipkart_logo.png" alt="Flipkart">
                    <h3>Flipkart</h3>
                </a>
            </div>
            <!-- Meesho -->
            <div class="app-logo">
                <a href="checkAccount.php?platform=meesho" target="_blank">
                    <img src="meesho_logo.webp" alt="Meesho">
                    <h3>Meesho</h3>
                </a>
            </div>
            <!-- Myntra -->
            <div class="app-logo">
                <a href="checkAccount.php?platform=myntra" target="_blank">
                    <img src="myntra_logo.jpg" alt="Myntra">
                    <h3>Myntra</h3>
                </a>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact" style="background-color: rgba(33, 34, 35, 0.5); padding: 4rem 20px; color: white;">
        <h2>Contact Us</h2>
        <p>Have questions? Get in touch with our support team.</p>
        <a href="mailto:support@unifiedshopping.com" class="btn-cta">Email Support</a>
    </section>

    <!-- Footer -->
     <br>
     <br>
    <footer>
        <p>&copy; 2025 Unified Shopping and Tracking | All Rights Reserved</p>
        <ul class="footer-links">
            <li><a href="/terms">Terms & Conditions</a></li>
            <li><a href="/privacy">Privacy Policy</a></li>
        </ul>
    </footer>
</body>
</html>

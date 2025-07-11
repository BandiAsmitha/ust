<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Get session and query parameters
$mobile = $_SESSION['mobile'] ?? null;
$platform = $_GET['platform'] ?? '';

// Validate inputs
if (!$mobile || !$platform) {
    die("Missing mobile or platform.");
}

// Define platform settings
$platforms = [
    'amazon' => [
        'db_name' => 'amazon',
        'redirect' => 'http://localhost/Amazon/home.php',
        'signup' => 'http://localhost/Amazon/user_signup.php'
    ],
    'flipkart' => [
        'db_name' => 'flipkart',
        'redirect' => 'http://localhost/Flipkart/home.php',
        'signup' => 'http://localhost/Flipkart/user_signup.php'
    ],
    'meesho' => [
        'db_name' => 'meesho',
        'redirect' => 'http://localhost/Meesho-Clone-main/index.html',
        'signup' => 'http://localhost/Meesho-Clone-main/signup.html'
    ],
    'myntra' => [
        'db_name' => 'myntra',
        'redirect' => 'https://www.myntra.com',
        'signup' => 'https://www.myntra.com/signup' // assumed for example
    ]
];

// Validate platform
if (!isset($platforms[$platform])) {
    die("Invalid platform selected.");
}

$db_name = $platforms[$platform]['db_name'];
$redirect_url = $platforms[$platform]['redirect'];
$signup_url = $platforms[$platform]['signup'];

// Connect to platform's database
$conn = new mysqli("localhost", "root", "", $db_name);
if ($conn->connect_error) {
    die("Failed to connect to $db_name: " . $conn->connect_error);
}

// Check if mobile exists in the platform's database
$stmt = $conn->prepare("SELECT id FROM login WHERE phone_number = ?");
$stmt->bind_param("s", $mobile);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Mobile found — store this platform in the UST database (user_platforms)
    
    // Connect to UST database to store the platform
    $ust_conn = new mysqli("localhost", "root", "", "ust");
    if ($ust_conn->connect_error) {
        die("Failed to connect to UST DB: " . $ust_conn->connect_error);
    }

    // Insert platform into user_platforms table
    $stmt_ust = $ust_conn->prepare("INSERT IGNORE INTO user_platforms (mobile, platform) VALUES (?, ?)");
    $stmt_ust->bind_param("ss", $mobile, $platform);

    if ($stmt_ust->execute()) {
        echo "Successfully connected to $platform for mobile: $mobile.<br>";
    } else {
        echo "Error storing platform info: " . $stmt_ust->error;
    }

    // Close UST DB connection
    $stmt_ust->close();
    $ust_conn->close();

    // Redirect to platform's main page
    header("Location: $redirect_url");
    exit();
}

// Mobile not found — show signup prompt
$conn->close();
$platformName = ucfirst($platform);
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $platformName; ?> Account Check</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 80px;
        }
        .btn {
            display: inline-block;
            background-color: #007BFF;
            color: #fff;
            font-size: 18px;
            font-weight: bold;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .message {
            color: red;
            font-size: 22px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>

    <div class="message">
        No account found for mobile <b><?php echo htmlspecialchars($mobile); ?></b> on <b><?php echo $platformName; ?></b>.
    </div>

    <!-- Display the signup link and the URL to debug -->
    <a class="btn" href="<?php echo htmlspecialchars($signup_url); ?>">
        👉 Click here to sign up on <?php echo $platformName; ?>
    </a>

    <p style="margin-top: 40px;">
        <a href="index.php">⬅ Go Back</a>
    </p>

    <!-- Debug: Display the signup URL -->
    <div>
        <p><strong>Debugging Information:</strong></p>
        <p>Signup URL: <?php echo htmlspecialchars($signup_url); ?></p>
    </div>

</body>
</html>

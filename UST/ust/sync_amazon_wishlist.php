<?php
session_start();
if (!isset($_SESSION['mobile'])) {
    header("Location: user_login.php");
    exit();
}

$mobile = $_SESSION['mobile'];

// Connect to UST DB
$ust_conn = new mysqli("localhost", "root", "", "ust");
if ($ust_conn->connect_error) {
    die("UST DB connection failed: " . $ust_conn->connect_error);
}

// Check if user is connected to Amazon
$stmt = $ust_conn->prepare("SELECT 1 FROM user_platforms WHERE mobile = ? AND platform = 'amazon'");
$stmt->bind_param("s", $mobile);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("You are not connected to Amazon platform.");
}
$stmt->close();

// Simulated API/JSON source (can be replaced with real fetch from amazon_wishlist.php)
$wishlistJson = file_get_contents("https://raw.githubusercontent.com/doitlikejustin/amazon-wish-lister/master/sample.json"); // replace with real URL or local JSON file
$wishlist = json_decode($wishlistJson, true);

if (!$wishlist || !is_array($wishlist)) {
    die("Failed to fetch wishlist items.");
}

// Insert items into wishlist_items table
foreach ($wishlist as $item) {
    $product_id = $item['num'] ?? '';
    $product_name = $item['name'] ?? '';
    $product_link = $item['link'] ?? '';
    $product_image = $item['large-ssl-image'] ?? '';
    $platform = 'Amazon';

    if (!$product_id) continue;

    $stmt = $ust_conn->prepare("INSERT IGNORE INTO wishlist_items 
        (mobile, platform, product_id, product_name, product_link, product_image) 
        VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $mobile, $platform, $product_id, $product_name, $product_link, $product_image);
    $stmt->execute();
    $stmt->close();
}

$ust_conn->close();
echo "✅ Amazon wishlist synced successfully!";
?>

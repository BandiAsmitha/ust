<?php
session_start();
if (!isset($_SESSION['mobile'])) {
    header("Location: user_login.php");
    exit();
}

$mobile = $_SESSION['mobile'];
$wishlistItems = [];

// Connect to UST DB
$ust_conn = new mysqli("localhost", "root", "", "ust");
if ($ust_conn->connect_error) {
    die("UST DB connection failed: " . $ust_conn->connect_error);
}

// Get connected platforms
$platforms = [];
$stmt = $ust_conn->prepare("SELECT platform FROM user_platforms WHERE mobile = ?");
$stmt->bind_param("s", $mobile);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $platforms[] = $row['platform'];
}
$stmt->close();

/* ==== PLATFORM FETCH FUNCTIONS ==== */

// Amazon
function fetchAmazonWishlist($mobile) {
    $conn = new mysqli("localhost", "root", "", "amazon");
    if ($conn->connect_error) return [];

    $stmt = $conn->prepare("SELECT w.product_id, p.product_name, p.image_path AS product_image
                            FROM wishlist w 
                            JOIN products p ON w.product_id = p.id 
                            WHERE w.user_id = ?");
    $stmt->bind_param("s", $mobile);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $row['platform'] = 'Amazon';
        $row['product_link'] = ''; // Add actual product link if available
        $items[] = $row;
    }

    $stmt->close();
    $conn->close();
    return $items;
}

/* ==== Fetch + Store ==== */

// Loop through connected platforms
foreach ($platforms as $platform) {
    $platform = strtolower($platform);
    $items = [];

    switch ($platform) {
        case 'amazon':
            $items = fetchAmazonWishlist($mobile);
            break;

        // Add more: flipkart, meesho, myntra...
    }

    // Store into ust.wishlist_items
    foreach ($items as $item) {
        $stmt = $ust_conn->prepare("
            INSERT IGNORE INTO wishlist_items 
            (mobile, platform, product_id, product_name, product_image, product_link)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "ssssss",
            $mobile,
            $item['platform'],
            $item['product_id'],
            $item['product_name'],
            $item['product_image'],
            $item['product_link']
        );
        $stmt->execute();
        $stmt->close();
    }
}

// Now fetch from UST wishlist_items table
$stmt = $ust_conn->prepare("SELECT * FROM wishlist_items WHERE mobile = ?");
$stmt->bind_param("s", $mobile);
$stmt->execute();
$result = $stmt->get_result();

$wishlistItems = [];
while ($row = $result->fetch_assoc()) {
    $wishlistItems[] = $row;
}

$stmt->close();
$ust_conn->close();
?>

<!-- HTML Display Wishlist -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>UST | My Wishlist</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #ecf0f1;
        }
        h1 {
            background: #2c3e50;
            color: white;
            text-align: center;
            padding: 1rem;
        }
        .wishlist-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 1rem;
        }
        .wishlist-item {
            background: white;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            text-align: center;
        }
        .wishlist-item img {
            max-width: 120px;
            height: auto;
        }
        .wishlist-item h3 {
            margin: 10px 0;
        }
        .wishlist-item a {
            color: #2980b9;
            text-decoration: none;
            font-weight: bold;
        }
        .wishlist-item a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<h1>My Unified Wishlist</h1>

<div class="wishlist-container">
    <?php if (!empty($wishlistItems)): ?>
        <?php foreach ($wishlistItems as $item): ?>
            <div class="wishlist-item">
                <h3><?php echo htmlspecialchars($item['product_name']); ?> 
                    <small>(<?php echo $item['platform']; ?>)</small></h3>
                <?php if (!empty($item['product_image'])): ?>
                    <img src="<?php echo htmlspecialchars($item['product_image']); ?>" alt="Product Image"><br><br>
                <?php endif; ?>
                <?php if (!empty($item['product_link'])): ?>
                    <a href="<?php echo htmlspecialchars($item['product_link']); ?>" target="_blank">View Product</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="text-align:center;">You have no items in your wishlist yet.</p>
    <?php endif; ?>
</div>

</body>
</html>

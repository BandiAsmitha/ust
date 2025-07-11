<?php
// Start the session
session_start();

// Check if the seller is logged in by checking session data
if (!isset($_SESSION['seller_id'])) {
    header("Location: seller_login.php");
    exit();
}

// Database connection details
$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "Flipkart";

// Create a connection to the database
$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the seller's information
$seller_id = $_SESSION['seller_id'];
$stmt = $conn->prepare("SELECT * FROM products WHERE seller_id = ?");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if the seller has any products
if ($result->num_rows > 0) {
    $products = [];
    while ($row = $result->fetch_assoc()) {
        // Fetch the sizes (assuming it's stored as a comma-separated list)
        $sizes = isset($row['sizes']) ? $row['sizes'] : '';
        $sizesArray = explode(',', $sizes);  // Convert the comma-separated list into an array
        $row['sizes'] = $sizesArray; // Store the sizes as an array in the product data
        $products[] = $row;
    }
} else {
    $products = [];
}

// Close the database connection
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Products</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f1f1f1;
            padding: 50px;
        }
        .products-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .products-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .product-item {
            margin-bottom: 20px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 15px;
        }
        .product-item:last-child {
            border-bottom: none;
        }
        .product-item img {
            max-width: 100px;
            height: auto;
            margin-right: 20px;
        }
        .product-item .product-details {
            display: inline-block;
            vertical-align: top;
            max-width: 650px;
        }
        .product-item .product-details h4 {
            margin: 0;
        }
        .product-item .product-details p {
            margin: 5px 0;
        }
        .product-item .product-details .sizes {
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <div class="products-container">
        <h2>Your Products</h2>

        <?php if (count($products) > 0): ?>
            <?php foreach ($products as $product): ?>
                <div class="product-item">
                    <img src="<?php echo $product['image_path']; ?>" alt="Product Image">
                    <div class="product-details">
                        <h4><?php echo $product['product_name']; ?></h4>
                        <p><strong>Category:</strong> <?php echo $product['category']; ?></p>
                        <p><strong>Price:</strong> $<?php echo number_format($product['price'], 2); ?></p>
                        <p><strong>Description:</strong> <?php echo $product['description']; ?></p>
                        <!-- Display the sizes -->
                        <div class="sizes">
                            <strong>Sizes:</strong>
                            <?php if (!empty($product['sizes'])): ?>
                                <ul>
                                    <?php foreach ($product['sizes'] as $size): ?>
                                        <li><?php echo htmlspecialchars($size); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p>No sizes available.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>You have no products listed.</p>
        <?php endif; ?>

    </div>

</body>
</html>

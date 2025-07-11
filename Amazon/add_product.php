<?php
session_start();

if (!isset($_SESSION['seller_id'])) {
    header("Location: seller_login.php");
    exit();
}

$productNameErr = $descriptionErr = $priceErr = $categoryErr = $subcategoryErr = $imageErr = $sizeErr = "";
$productName = $description = $price = $category = $subcategory = $imagePath = "";
$sizes = [];

$conn = new mysqli("localhost", "root", "", "AMAZON");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $productName = sanitize_input($_POST["product_name"]);
    $description = sanitize_input($_POST["description"]);
    $price = sanitize_input($_POST["price"]);
    $category = sanitize_input($_POST["category"]);
    $subcategory = isset($_POST["subcategory"]) ? sanitize_input($_POST["subcategory"]) : "";

    if (empty($productName)) $productNameErr = "Product name is required";
    if (empty($description)) $descriptionErr = "Description is required";
    if (empty($price)) $priceErr = "Price is required";
    if (empty($category)) $categoryErr = "Category is required";
    if (($category == "Fashion" || $category == "Home") && empty($subcategory)) $subcategoryErr = "Subcategory is required";
    if ($category == "Fashion" && empty($_POST["sizes"])) $sizeErr = "At least one size is required";
    else if (isset($_POST["sizes"])) $sizes = $_POST["sizes"];

    if ($_FILES["product_image"]["error"] == 0) {
        $image = $_FILES["product_image"];
        $imageName = basename($image["name"]);
        $imageTmp = $image["tmp_name"];
        $imageSize = $image["size"];
        $imageType = pathinfo($imageName, PATHINFO_EXTENSION);

        $allowedTypes = ["jpg", "jpeg", "png", "gif", "webp"];
        $maxSize = 5 * 1024 * 1024;

        if (in_array(strtolower($imageType), $allowedTypes) && $imageSize <= $maxSize) {
            $imageNewName = uniqid("product_", true) . "." . strtolower($imageType);
            $uploadDir = "uploads/products/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $imagePath = $uploadDir . $imageNewName;
            if (!move_uploaded_file($imageTmp, $imagePath)) {
                $imageErr = "Error uploading image.";
            }
        } else {
            $imageErr = "Invalid image type or size.";
        }
    } else {
        $imageErr = "Product image is required.";
    }

    if (!$productNameErr && !$descriptionErr && !$priceErr && !$categoryErr && !$subcategoryErr && !$sizeErr && !$imageErr) {
        $sizesString = implode(",", $sizes);
        $stmt = $conn->prepare("INSERT INTO products (seller_id, product_name, description, price, category, subcategory, image_path, sizes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issdssss", $_SESSION['seller_id'], $productName, $description, $price, $category, $subcategory, $imagePath, $sizesString);

        if ($stmt->execute()) {
            $successMessage = "Product added successfully!";
            // Reset values
            $productName = $description = $price = $category = $subcategory = "";
            $sizes = [];
        } else {
            $errorMessage = "Error: Could not add product.";
        }
        $stmt->close();
    }
}

function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f1f1f1;
            padding: 50px;
            margin: 0;
        }

        .product-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .product-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #131921;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            font-weight: bold;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        .form-group input[type="submit"] {
            background-color: #131921;
            color: #fff;
            border: none;
            cursor: pointer;
            transition: 0.3s;
        }

        .form-group input[type="submit"]:hover {
            background-color: #1e90ff;
        }

        .error {
            color: red;
            font-size: 14px;
        }

        .success {
            color: green;
            font-size: 16px;
            text-align: center;
        }

        .message {
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
    <script>
        const categoryToSubcategories = {
            Fashion: ["Men", "Women", "Kids"],
            Home: ["Kitchen", "Hall", "Bedroom", "Decorating Items"]
        };

        function handleCategoryChange() {
            const category = document.getElementById("category").value;
            const subcategoryGroup = document.getElementById("subcategory-group");
            const subcategorySelect = document.getElementById("subcategory");
            const sizesGroup = document.getElementById("sizes-group");

            // Reset and repopulate subcategories
            subcategorySelect.innerHTML = '<option value="">-- Select Subcategory --</option>';
            if (categoryToSubcategories[category]) {
                categoryToSubcategories[category].forEach(sub => {
                    const option = document.createElement("option");
                    option.value = sub;
                    option.textContent = sub;
                    subcategorySelect.appendChild(option);
                });
                subcategoryGroup.style.display = "block";
            } else {
                subcategoryGroup.style.display = "none";
            }

            sizesGroup.style.display = category === "Fashion" ? "block" : "none";
        }

        window.onload = handleCategoryChange;
    </script>
</head>
<body>

<div class="product-container">
    <h2>Add Product</h2>

    <div class="message">
        <?php 
        if (isset($successMessage)) echo "<p class='success'>$successMessage</p>";
        if (isset($errorMessage)) echo "<p class='error'>$errorMessage</p>";
        ?>
    </div>

    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label>Product Name</label>
            <input type="text" name="product_name" value="<?= $productName ?>" required>
            <span class="error"><?= $productNameErr ?></span>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description" required><?= $description ?></textarea>
            <span class="error"><?= $descriptionErr ?></span>
        </div>

        <div class="form-group">
            <label>Price</label>
            <input type="number" step="0.01" name="price" value="<?= $price ?>" required>
            <span class="error"><?= $priceErr ?></span>
        </div>

        <div class="form-group">
            <label>Category</label>
            <select id="category" name="category" onchange="handleCategoryChange()" required>
                <option value="">-- Select Category --</option>
                <option value="Electronics" <?= ($category == "Electronics") ? "selected" : "" ?>>Electronics</option>
                <option value="Fashion" <?= ($category == "Fashion") ? "selected" : "" ?>>Fashion</option>
                <option value="Home" <?= ($category == "Home") ? "selected" : "" ?>>Home</option>
                <option value="Beauty" <?= ($category == "Beauty") ? "selected" : "" ?>>Beauty</option>
                <option value="Health" <?= ($category == "Health") ? "selected" : "" ?>>Health</option>
                <option value="Toys" <?= ($category == "Toys") ? "selected" : "" ?>>Toys</option>
            </select>
            <span class="error"><?= $categoryErr ?></span>
        </div>

        <div class="form-group" id="subcategory-group" style="display: none;">
            <label>Subcategory</label>
            <select name="subcategory" id="subcategory">
                <option value="">-- Select Subcategory --</option>
            </select>
            <span class="error"><?= $subcategoryErr ?></span>
        </div>

        <div class="form-group" id="sizes-group" style="display: none;">
            <label>Select Sizes (for Fashion)</label><br>
            <?php
            $sizeOptions = ["Small", "Medium", "Large", "X-Large", "XX-Large"];
            foreach ($sizeOptions as $size) {
                $checked = in_array($size, $sizes) ? "checked" : "";
                echo "<label><input type='checkbox' name='sizes[]' value='$size' $checked> $size</label><br>";
            }
            ?>
            <span class="error"><?= $sizeErr ?></span>
        </div>

        <div class="form-group">
            <label>Product Image</label>
            <input type="file" name="product_image" required>
            <span class="error"><?= $imageErr ?></span>
        </div>

        <div class="form-group">
            <input type="submit" value="Add Product">
        </div>
    </form>
</div>

</body>
</html>

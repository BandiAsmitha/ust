<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['mobile'])) {
    die("Session expired. Please log in again.");
}

$phone_number = $_SESSION['mobile'];

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "AMAZON";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user ID from phone number
$stmt = $conn->prepare("SELECT id FROM login WHERE phone_number = ?");
$stmt->bind_param("s", $phone_number);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->bind_result($user_id);
    $stmt->fetch();
} else {
    die("User not found.");
}

// Get selected category and subcategory
$category = $_GET['category'] ?? '';
$subcategory = $_GET['subcategory'] ?? '';

// Get wishlist product IDs
$wishlist_product_ids = [];
$stmt = $conn->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $wishlist_product_ids[] = $row['product_id'];
}

// Get distinct subcategories for the selected category
$subcategories = [];
if ($category) {
    $stmt = $conn->prepare("SELECT DISTINCT subcategory FROM products WHERE category = ?");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $subcategories[] = $row['subcategory'];
    }
}

// Get products based on category and subcategory
$query = "SELECT id, product_name, description, price, image_path, sizes FROM products WHERE category = ?";
$params = [$category];

if ($subcategory) {
    $query .= " AND subcategory = ?";
    $params[] = $subcategory;
}

$stmt = $conn->prepare($query);
$stmt->bind_param(str_repeat("s", count($params)), ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Amazon Clone</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
  font-family: Arial, sans-serif;
  margin: 0;
  padding: 0;
  background-color: #f1f1f1;
}

header {
  background-color: #131921;
  color: white;
  padding: 15px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

header h1 {
  margin: 0;
  font-size: 28px;
  color: #1e90ff;
}

.search-bar {
  display: flex;
  align-items: center;
}

.search-bar input {
  padding: 8px;
  border: none;
  border-radius: 4px;
  width: 200px;
}

.wishlist-link {
  color: white;
  background-color: #1e90ff;
  padding: 8px 12px;
  border-radius: 5px;
  margin-left: 10px;
  text-decoration: none;
  font-weight: bold;
  transition: background-color 0.3s;
}

.wishlist-link:hover {
  background-color: #63b3ed;
}

.categories {
  background-color: #232f3e;
  padding: 15px;
  color: white;
  position: relative;
}

.categories-list-desktop {
  display: flex;
  list-style: none;
  justify-content: space-around;
  margin: 0;
  padding: 0;
}

.categories-list-desktop li {
  margin: 0 10px;
  cursor: pointer;
  padding: 8px;
  border-radius: 5px;
  transition: background-color 0.3s;
}

.categories-list-desktop li:hover {
  background-color: #1e90ff;
}

.submenu {
  display: none;
  background-color: #2e3b4e;
  padding: 10px;
  position: absolute;
  z-index: 10;
  border-radius: 5px;
}

.submenu li {
  margin: 5px 0;
  padding: 5px;
}

.submenu li a {
  color: white;
  text-decoration: none;
}

.submenu li:hover {
  background-color: #1e90ff;
}

.dots-button {
  position: absolute;
  top: 15px;
  left: 15px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  height: 20px;
  cursor: pointer;
}

.dots-button div {
  width: 25px;
  height: 5px;
  background-color: white;
  border-radius: 2px;
}

.categories-list {
  display: none;
  flex-direction: column;
  position: absolute;
  top: 50px;
  left: 0;
  width: 100%;
  background-color: #232f3e;
  padding: 10px 0;
}

.categories-list li {
  text-align: center;
  margin: 10px;
  padding: 10px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.categories-list li:hover {
  background-color: #1e90ff;
}

.all-products {
  padding: 20px;
}

.category-section {
  margin-bottom: 40px;
}

.category-section h2 {
  margin: 10px 0;
  font-size: 22px;
  color: #333;
}

/* Horizontal product scroll for each category */
.products {
  display: flex;
  flex-direction: row;
  flex-wrap: nowrap;
  overflow-x: auto;
  gap: 20px;
  padding-bottom: 10px;
  scrollbar-color: #aaa transparent;
  scrollbar-width: thin;
}

/* Scrollbar styling (optional) */
.products::-webkit-scrollbar {
  height: 8px;
}
.products::-webkit-scrollbar-track {
  background: transparent;
}
.products::-webkit-scrollbar-thumb {
  background-color: #bbb;
  border-radius: 10px;
}

/* Product card styling */
.product {
  min-width: 250px;
  background-color: white;
  padding: 15px;
  border-radius: 8px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
  flex-shrink: 0;
  position: relative;
}

.product img {
  width: 100%;
  height: 200px;
  object-fit: cover;
  border-radius: 8px;
}

.wishlist-icon {
  font-size: 24px;
  color: gray;
  cursor: pointer;
  position: absolute;
  top: 15px;
  right: 15px;
}

.wishlist-icon.active {
  color: red;
}

.product-buttons {
  margin-top: 10px;
  display: flex;
  justify-content: space-between;
}

.buy-now-btn,
.add-cart-btn {
  background-color: #ff9900;
  color: white;
  border: none;
  padding: 8px 12px;
  border-radius: 5px;
  font-weight: bold;
  cursor: pointer;
  transition: background-color 0.3s;
}

.add-cart-btn {
  background-color: #007185;
}

.buy-now-btn:hover {
  background-color: #e68a00;
}

.add-cart-btn:hover {
  background-color: #005f6b;
}


.wishlist-icon {
  font-size: 24px;
  color: gray;
  cursor: pointer;
  position: absolute;
  top: 15px;
  right: 15px;
}

.wishlist-icon.active {
  color: red;
}

footer {
  background-color: #131921;
  color: white;
  text-align: center;
  padding: 20px;
  position: fixed;
  bottom: 0;
  width: 100%;
}

footer ul {
  display: flex;
  justify-content: center;
  list-style: none;
  padding: 0;
  margin: 0;
}

footer li {
  margin: 0 15px;
}

footer img {
  width: 40px;
  height: 40px;
  border-radius: 50%;
}

a {
  text-decoration: none;
  color: inherit;
}

/* Product buttons */
.product-buttons {
  margin-top: 10px;
  display: flex;
  justify-content: center;
  gap: 10px;
}

.buy-now-btn,
.add-cart-btn {
  background-color: #ff9900;
  color: white;
  border: none;
  padding: 8px 12px;
  border-radius: 5px;
  font-weight: bold;
  cursor: pointer;
  transition: background-color 0.3s;
}

.add-cart-btn {
  background-color: #007185;
}

.buy-now-btn:hover {
  background-color: #e68a00;
}

.add-cart-btn:hover {
  background-color: #005f6b;
}

@media (max-width: 768px) {
  .categories-list-desktop {
    display: none;
  }
}

  </style>
</head>
<body>

<!-- Header -->
<header>
  <h1>Amazon Clone</h1>
  <div class="search-bar">
    <input type="text" placeholder="Search products">
    <a href="wishlist_view.php" class="wishlist-link">❤️ Wishlist</a>
  </div>
</header>
<section class="categories">
  <div class="dots-button" onclick="toggleCategories()">
    <div></div><div></div><div></div>
  </div>
  <ul class="categories-list-desktop">
    <li>
      <a href="home.php?category=Home" onclick="event.preventDefault(); toggleSubmenu('home-sub')">
        <img src="http://localhost/Amazon/images/home.jpeg" alt="Home" style="width: 50px; height: 50px; border-radius: 8px;"><br>Home
      </a>
      <ul class="submenu" id="home-sub">
        <li><a href="home.php?category=Home&subcategory=Kitchen">Kitchen</a></li>
        <li><a href="home.php?category=Home&subcategory=Hall">Hall</a></li>
        <li><a href="home.php?category=Home&subcategory=Bedroom">Bedroom</a></li>
        <li><a href="home.php?category=Home&subcategory=Decorating Items">Decorating Items</a></li>
      </ul>
    </li>

    <li>
      <a href="home.php?category=Fashion" onclick="event.preventDefault(); toggleSubmenu('fashion-sub')">
        <img src="http://localhost/Amazon/images/fashion.jpeg" alt="Fashion" style="width: 50px; height: 50px; border-radius: 8px;"><br>Fashion
      </a>
      <ul class="submenu" id="fashion-sub">
        <li><a href="home.php?category=Fashion&subcategory=Women">Women</a></li>
        <li><a href="home.php?category=Fashion&subcategory=Men">Men</a></li>
        <li><a href="home.php?category=Fashion&subcategory=Kids">Kids</a></li>
        <li><a href="home.php?category=Fashion&subcategory=Girls">Girls</a></li>
        <li><a href="home.php?category=Fashion&subcategory=Boys">Boys</a></li>
      </ul>
    </li>

    <li>
      <a href="home.php?category=Electronics" onclick="event.preventDefault(); toggleSubmenu('electronics-sub')">
        <img src="http://localhost/Amazon/images/electronics.jpeg" alt="Electronics" style="width: 50px; height: 50px; border-radius: 8px;"><br>Electronics
      </a>
      <ul class="submenu" id="electronics-sub">
        <li><a href="home.php?category=Electronics&subcategory=Mobile">Mobile</a></li>
        <li><a href="home.php?category=Electronics&subcategory=Laptop">Laptop</a></li>
        <li><a href="home.php?category=Electronics&subcategory=Tab">Tab</a></li>
        <li><a href="home.php?category=Electronics&subcategory=Smart Watches">Smart Watches</a></li>
        <li><a href="home.php?category=Electronics&subcategory=TV">TV</a></li>
        <li><a href="home.php?category=Electronics&subcategory=Refrigerator">Refrigerator</a></li>
      </ul>
    </li>
    <li>
      <a href="home.php?category=Beauty">
        <img src="http://localhost/Amazon/images/beauty.jpeg" alt="Beauty" style="width: 50px; height: 50px; border-radius: 8px;"><br>Beauty
      </a>
  </li>
  <li>
      <a href="home.php?category=Health" >
        <img src="http://localhost/Amazon/images/health.jpeg" alt="health" style="width: 50px; height: 50px; border-radius: 8px;"><br>Health
      </a>
  </li>
  </ul>
</section>
<section class="all-products">
  <?php
  $categories = ['Fashion', 'Home', 'Electronics', 'Beauty', 'Health'];

  foreach ($categories as $category) {
    // Get distinct subcategories for this category
    $subStmt = $conn->prepare("SELECT DISTINCT subcategory FROM products WHERE category = ?");
    $subStmt->bind_param("s", $category);
    $subStmt->execute();
    $subResult = $subStmt->get_result();

    while ($subRow = $subResult->fetch_assoc()) {
      $sub = $subRow['subcategory'];

      // Get products for this category and subcategory
      $stmt = $conn->prepare("SELECT id, product_name, description, price, image_path FROM products WHERE category = ? AND subcategory = ?");
      $stmt->bind_param("ss", $category, $sub);
      $stmt->execute();
      $result = $stmt->get_result();

      if ($result->num_rows > 0) {
        echo "<div class='category-section'>";
        echo "<h2>Products in $category - $sub</h2>";
        echo "<div class='products'>";

        while ($row = $result->fetch_assoc()) {
          $isInWishlist = in_array($row['id'], $wishlist_product_ids);
          $heartClass = $isInWishlist ? 'wishlist-icon active' : 'wishlist-icon';

          echo "<div class='product'>";
          echo "<img src='" . $row['image_path'] . "' alt='" . $row['product_name'] . "'>";
          echo "<h3>" . $row['product_name'] . "</h3>";
          echo "<p>" . $row['description'] . "</p>";
          echo "<p>Price: $" . $row['price'] . "</p>";
          echo "<span class='$heartClass' data-product-id='" . $row['id'] . "'>♡</span>";

          echo "<div class='product-buttons'>";
          echo "<form action='add_address.php' method='POST'>";
          echo "<input type='hidden' name='product_id' value='" . $row['id'] . "'>";
          echo "<button type='submit' class='buy-now-btn'>Buy Now</button>";
          echo "</form>";

          echo "<form action='add_to_cart.php' method='POST'>";
          echo "<input type='hidden' name='product_id' value='" . $row['id'] . "'>";
          echo "<button type='submit' class='add-cart-btn'>Add to Cart</button>";
          echo "</form>";
          echo "</div>"; // product-buttons

          echo "</div>"; // product
        }

        echo "</div>"; // .products
        echo "</div>"; // .category-section
      }
    }
  }
  ?>
</section>

<!-- Footer -->
<footer>
  <ul>
    <li><a href="home.php" title="Home">🏠<br>Home</a></li>
    <li><a href="login.php" title="Account">👤<br>Account</a></li>
    <li><a href="orders.php" title="Orders">📦<br>Orders</a></li>
    <li><a href="view_cart.php" title="Cart">🛒<br>Cart</a></li>
    
  </ul>
</footer>


<!-- Wishlist Script -->
<script>
function toggleCategories() {
  const categoriesList = document.querySelector('.categories-list');
  categoriesList.classList.toggle('show');
}

document.querySelectorAll('.wishlist-icon').forEach(icon => {
  icon.addEventListener('click', function () {
    const productId = this.getAttribute('data-product-id');
    const isActive = this.classList.contains('active');
    fetch('wishlist.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `product_id=${productId}&action=${isActive ? 'remove' : 'add'}`
    })
    .then(response => response.text())
    .then(data => {
      if (data === 'added') this.classList.add('active');
      else if (data === 'removed') this.classList.remove('active');
    });
  });
});

function toggleSubmenu(id) {
  // Hide all submenus first
  document.querySelectorAll('.submenu').forEach(menu => {
    if (menu.id !== id) {
      menu.style.display = 'none';
    }
  });

  // Toggle the one clicked
  const submenu = document.getElementById(id);
  submenu.style.display = submenu.style.display === 'block' ? 'none' : 'block';
}
</script>

</body>
</html>

<?php $conn->close(); ?>

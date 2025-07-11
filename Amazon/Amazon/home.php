<?php
session_start();

// Check if the user is logged in via UST (phone number set in session)
if (!isset($_SESSION['phone_number'])) {
    header("Location: login.php");
    exit();
}

$phone_number = $_SESSION['phone_number'];

// Database details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "AMAZON";

// Connect to database
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user exists in Amazon DB
$sql = "SELECT user_id FROM users WHERE phone_number = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $phone_number);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($user_id);
    $stmt->fetch();
} else {
    // User not found — auto-create user
    $insert_stmt = $conn->prepare("INSERT INTO users (phone_number) VALUES (?)");
    $insert_stmt->bind_param("s", $phone_number);
    if ($insert_stmt->execute()) {
        $user_id = $insert_stmt->insert_id;
    } else {
        die("Error creating user in Amazon DB.");
    }
}

// Get category from query
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Fetch wishlist items
$wishlist_product_ids = [];
$wishlist_result = $conn->query("SELECT product_id FROM wishlist WHERE user_id = $user_id");
if ($wishlist_result && $wishlist_result->num_rows > 0) {
    while ($row = $wishlist_result->fetch_assoc()) {
        $wishlist_product_ids[] = $row['product_id'];
    }
}

// Fetch products for selected category
$sql = "SELECT * FROM products WHERE category = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $category);
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
    .products {
      margin: 20px;
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
    }
    .product {
      background-color: white;
      padding: 20px;
      margin: 10px 0;
      width: 20%;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
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

<!-- Categories -->
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
      <li><a href="home.php?category=Kitchen">Kitchen</a></li>
      <li><a href="home.php?category=Hall">Hall</a></li>
      <li><a href="home.php?category=Bedroom">Bedroom</a></li>
      <li><a href="home.php?category=Decorating Items">Decorating Items</a></li>
    </ul>
  </li>

  <li>
    <a href="home.php?category=Fashion" onclick="event.preventDefault(); toggleSubmenu('fashion-sub')">
      <img src="http://localhost/Amazon/images/fashion.jpeg" alt="Fashion" style="width: 50px; height: 50px; border-radius: 8px;"><br>Fashion
    </a>
    <ul class="submenu" id="fashion-sub">
      <li><a href="home.php?category=Women">Women</a></li>
      <li><a href="home.php?category=Men">Men</a></li>
      <li><a href="home.php?category=Kids">Kids</a></li>
      <li><a href="home.php?category=Girls">Girls</a></li>
      <li><a href="home.php?category=Boys">Boys</a></li>
    </ul>
  </li>

  <li>
    <a href="home.php?category=Beauty">
      <img src="http://localhost/Amazon/images/beauty.jpeg" alt="Beauty" style="width: 50px; height: 50px; border-radius: 8px;"><br>Beauty
    </a>
  </li>

  <li>
    <a href="home.php?category=Electronics" onclick="event.preventDefault(); toggleSubmenu('electronics-sub')">
      <img src="http://localhost/Amazon/images/electronics.jpeg" alt="Electronics" style="width: 50px; height: 50px; border-radius: 8px;"><br>Electronics
    </a>
    <ul class="submenu" id="electronics-sub">
      <li><a href="home.php?category=Mobile">Mobile</a></li>
      <li><a href="home.php?category=Laptop">Laptop</a></li>
      <li><a href="home.php?category=Tab">Tab</a></li>
      <li><a href="home.php?category=Smart Watches">Smart Watches</a></li>
      <li><a href="home.php?category=TV">TV</a></li>
      <li><a href="home.php?category=Refrigerator">Refrigerator</a></li>
    </ul>
  </li>

  <li>
    <a href="home.php?category=Health">
      <img src="http://localhost/Amazon/images/health.jpeg" alt="Health" style="width: 50px; height: 50px; border-radius: 8px;"><br>Health
    </a>
  </li>
</ul>

</section>


<!-- Product Section -->
<section class="products">
  <?php
  if ($result->num_rows > 0) {
    echo "<h2>Products in category: $category</h2>";
    while ($row = $result->fetch_assoc()) {
      $isInWishlist = in_array($row['id'], $wishlist_product_ids);
      $heartClass = $isInWishlist ? 'wishlist-icon active' : 'wishlist-icon';
      echo "<div class='product'>";
      echo "<img src='" . $row['image_path'] . "' alt='" . $row['product_name'] . "'>";
      echo "<h3>" . $row['product_name'] . "</h3>";
      echo "<p>" . $row['description'] . "</p>";
      echo "<p>Price: $" . $row['price'] . "</p>";
      echo "<span class='$heartClass' data-product-id='" . $row['id'] . "'>♡</span>";
      echo "</div>";
    }
  } else {
    echo "<p>No products found in this category.</p>";
  }
  ?>
</section>

<!-- Footer -->
<footer>
  <ul>
    <li><img src="https://via.placeholder.com/40" alt="Home"></li>
    <li><a href="login.php">Account</a></li>
    <li><a href="orders.php">orders</li>
    <li><img src="https://via.placeholder.com/40" alt="Cart"></li>
    <li><img src="https://via.placeholder.com/40" alt="More"></li>
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

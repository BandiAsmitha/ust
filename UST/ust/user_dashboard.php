<?php
session_start();

if (!isset($_SESSION['mobile'])) {
    die("Please log in first.");
}

$mobile = $_SESSION['mobile'];

// DB Connections
$conn_amazon = new mysqli("localhost", "root", "", "AMAZON");
$conn_flipkart = new mysqli("localhost", "root", "", "FLIPKART");
$conn_ust = new mysqli("localhost", "root", "", "UST");

if ($conn_amazon->connect_error) die("Connection to AMAZON failed: " . $conn_amazon->connect_error);
if ($conn_flipkart->connect_error) die("Connection to FLIPKART failed: " . $conn_flipkart->connect_error);
if ($conn_ust->connect_error) die("Connection to UST failed: " . $conn_ust->connect_error);

// Get user ID
function getUserId($conn, $mobile) {
    $stmt = $conn->prepare("SELECT id FROM login WHERE phone_number = ?");
    $stmt->bind_param("s", $mobile);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0 ? $result->fetch_assoc()['id'] : null;
}

$amazon_user_id = getUserId($conn_amazon, $mobile);
$flipkart_user_id = getUserId($conn_flipkart, $mobile);

// Fetch orders
function fetchOrders($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT p.*, o.order_date, o.status, o.approved_at, o.shipped_at,
               o.out_for_delivery_at, o.delivered_at, o.cancelled_at
        FROM orders o
        JOIN products p ON o.product_id = p.id
        WHERE o.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$amazon_orders = ($amazon_user_id !== null) ? fetchOrders($conn_amazon, $amazon_user_id) : [];
$flipkart_orders = ($flipkart_user_id !== null) ? fetchOrders($conn_flipkart, $flipkart_user_id) : [];

$platforms = ['AMAZON' => $amazon_orders, 'FLIPKART' => $flipkart_orders];

foreach ($platforms as $platform => $orders) {
    foreach ($orders as $row) {
        $product_id = $row['id'];
        $product_name = $row['product_name'];
        $description = $row['description'];
        $price = $row['price'];
        $category = $row['category'];
        $subcategory = $row['subcategory'];
        $image_path = $row['image_path'];
        $sizes = 'Small, Medium, Large';
        $order_date = $row['order_date'];
        $status = $row['status'] ?? 'Placed';
        $approved_at = $row['approved_at'] ?? null;
        $shipped_at = $row['shipped_at'] ?? null;
        $out_for_delivery_at = $row['out_for_delivery_at'] ?? null;
        $delivered_at = $row['delivered_at'] ?? null;
        $cancelled_at = $row['cancelled_at'] ?? null;

        $check_stmt = $conn_ust->prepare("
            SELECT id FROM order_items 
            WHERE product_id = ? AND mobile = ? AND platform = ?");
        $check_stmt->bind_param("iss", $product_id, $mobile, $platform);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows === 0) {
            $stmt_ust = $conn_ust->prepare("
                INSERT INTO order_items (
                    product_id, product_name, description, price, category, subcategory,
                    image_path, order_date, sizes, mobile, platform,
                    status, approved_at, shipped_at, out_for_delivery_at, delivered_at, cancelled_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt_ust->bind_param(
                "issdsssssssssssss",
                $product_id,
                $product_name,
                $description,
                $price,
                $category,
                $subcategory,
                $image_path,
                $order_date,
                $sizes,
                $mobile,
                $platform,
                $status,
                $approved_at,
                $shipped_at,
                $out_for_delivery_at,
                $delivered_at,
                $cancelled_at
            );

            $stmt_ust->execute();
        }
    }
}

// Display Orders
$stmt_display = $conn_ust->prepare("
    SELECT * FROM order_items 
    WHERE mobile = ? AND platform IN ('AMAZON', 'FLIPKART') 
    ORDER BY order_date DESC");
$stmt_display->bind_param("s", $mobile);
$stmt_display->execute();
$display_result = $stmt_display->get_result();

// Monthly Spending
$stmt_spending = $conn_ust->prepare("
    SELECT DATE_FORMAT(order_date, '%Y-%m') AS order_month, SUM(price) AS total_spent
    FROM order_items
    WHERE mobile = ? AND status NOT IN ('Cancelled')
    GROUP BY order_month
    ORDER BY order_month DESC
");
$stmt_spending->bind_param("s", $mobile);
$stmt_spending->execute();
$result_spending = $stmt_spending->get_result();

$labels = [];
$spending = [];
$prediction = 0;

while ($row = $result_spending->fetch_assoc()) {
    $labels[] = $row['order_month'];
    $spending[] = $row['total_spent'];
}

// Predict next month using last 3 months average
if (count($spending) >= 3) {
    $prediction = round(array_sum(array_slice($spending, 0, 3)) / 3, 2);
    $labels = array_reverse($labels);
    $spending = array_reverse($spending);
    $next_month = date("Y-m", strtotime("+1 month"));
    $labels[] = $next_month . " (Predicted)";
    $spending[] = $prediction;
}

// Category-wise Spending for Pie Chart
$stmt_pie = $conn_ust->prepare("
    SELECT category, SUM(price) AS total
    FROM order_items
    WHERE mobile = ? AND status NOT IN ('Cancelled')
    GROUP BY category
");
$stmt_pie->bind_param("s", $mobile);
$stmt_pie->execute();
$result_pie = $stmt_pie->get_result();

$category_labels = [];
$category_values = [];

while ($row = $result_pie->fetch_assoc()) {
    $category_labels[] = $row['category'];
    $category_values[] = $row['total'];
}

$conn_amazon->close();
$conn_flipkart->close();
$conn_ust->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Dashboard</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { font-family: Arial, sans-serif; background: #f0f2f5; margin: 0; padding: 0; }
    header { background: #131921; color: white; padding: 20px; text-align: center; }
    .chart-container, .pie-container { width: 25%; margin: 30px auto; background: white; padding: 20px; border-radius: 10px; }
    .orders { display: flex; flex-wrap: wrap; gap: 30px; justify-content: center; padding: 20px; }
    .order { background: white; padding: 15px; width: 22%; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .order img { width: 100%; height: 200px; object-fit: cover; border-radius: 8px; }
    .track-link { display: inline-block; margin-top: 10px; background: #28a745; color: white; padding: 8px 12px; border-radius: 5px; text-decoration: none; }
    .track-link:hover { background: #218838; }
  </style>
</head>
<body>

<header><h1>Welcome to Your Dashboard</h1></header>

<div class="chart-container">
  <h2>Monthly Spending (with Prediction)</h2>
  <canvas id="spendingChart"></canvas>
</div>

<div class="pie-container">
  <h2>Spending by Category</h2>
  <canvas id="categoryPieChart"></canvas>
</div>

<script>
const spendingCtx = document.getElementById('spendingChart').getContext('2d');
const spendingChart = new Chart(spendingCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Amount Spent ($)',
            data: <?= json_encode($spending) ?>,
            backgroundColor: <?= json_encode((function($labels) {
    $baseColors = ['#1e90ff', '#28a745', '#ffc107', '#dc3545', '#6f42c1', '#17a2b8', '#ff69b4', '#20c997', '#fd7e14', '#6c757d'];
    $colors = [];
    foreach ($labels as $index => $label) {
        if (strpos($label, 'Predicted') !== false) {
            $colors[] = '#ffa500'; // orange for predicted
        } else {
            $colors[] = $baseColors[$index % count($baseColors)];
        }
    }
    return $colors;
})($labels)) ?>

        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: value => '$' + value
                }
            }
        }
    }
});

const pieCtx = document.getElementById('categoryPieChart').getContext('2d');
const pieChart = new Chart(pieCtx, {
    type: 'pie',
    data: {
        labels: <?= json_encode($category_labels) ?>,
        datasets: [{
            data: <?= json_encode($category_values) ?>,
            backgroundColor: ['#1e90ff', '#28a745', '#ffc107', '#dc3545', '#6f42c1', '#17a2b8']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});
</script>

<section class="orders">
<?php
if ($display_result->num_rows > 0) {
    while ($row = $display_result->fetch_assoc()) {
        $image_path = "http://localhost/" . ($row['platform'] === 'AMAZON' ? 'Amazon' : 'Flipkart') . "/" . $row['image_path'];
        echo "<div class='order'>";
        echo "<img src='" . $image_path . "' alt='" . htmlspecialchars($row['product_name']) . "'>";
        echo "<h3>" . htmlspecialchars($row['product_name']) . "</h3>";
        echo "<p>" . htmlspecialchars($row['description']) . "</p>";
        echo "<p>Price: $" . number_format($row['price'], 2) . "</p>";
        echo "<p><strong>Order Date:</strong> " . $row['order_date'] . "</p>";
        echo "<p><strong>Status:</strong> " . htmlspecialchars($row['status']) . "</p>";
        echo "<p><strong>Platform:</strong> " . $row['platform'] . "</p>";
        echo "<a href='track_order.php?order_id=" . $row['id'] . "' class='track-link'>Track Order</a>";
        echo "</div>";
    }
} else {
    echo "<p style='margin: 20px;'>You have no orders.</p>";
}
?>
</section>

</body>
</html>

<?php
session_start();

if (!isset($_SESSION['mobile'])) {
    die('Please log in first.');
}

$mobile = preg_replace('/\D/', '', $_SESSION['mobile']);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// ───────── 1. Connections ─────────
$host = 'localhost';
$user = 'root';
$pass = '';
$conn_amazon   = new mysqli($host, $user, $pass, 'AMAZON');
$conn_flipkart = new mysqli($host, $user, $pass, 'FLIPKART');
$conn_ust      = new mysqli($host, $user, $pass, 'UST');

// ───────── 2. Helpers ─────────
function getUid(mysqli $c, string $m): ?int {
    $q = 'SELECT id FROM login WHERE REGEXP_REPLACE(phone_number, "[^0-9]", "") = ? LIMIT 1';
    $s = $c->prepare($q);
    $s->bind_param('s', $m);
    $s->execute();
    return $s->get_result()->fetch_column() ?: null;
}

function fetchOrders(mysqli $c, int $uid): array {
    $sql = 'SELECT p.*, o.order_date, COALESCE(o.status, "Placed") AS status,
                   o.approved_at, o.shipped_at, o.out_for_delivery_at,
                   o.delivered_at, o.cancelled_at
            FROM orders o
            JOIN products p ON p.id = o.product_id
            WHERE o.user_id = ?';
    $s = $c->prepare($sql);
    $s->bind_param('i', $uid);
    $s->execute();
    return $s->get_result()->fetch_all(MYSQLI_ASSOC);
}

// ───────── 3. Pull orders ─────────
$ordersByPlatform = [
    'AMAZON'   => ($uid = getUid($conn_amazon, $mobile))   ? fetchOrders($conn_amazon,   $uid) : [],
    'FLIPKART' => ($uid = getUid($conn_flipkart, $mobile)) ? fetchOrders($conn_flipkart, $uid) : [],
];

// ───────── 4. Sync into UST.order_items ─────────
$chk = $conn_ust->prepare('SELECT * FROM order_items WHERE product_id = ? AND order_date = ? AND mobile = ? AND platform = ?');
$ins = $conn_ust->prepare('INSERT INTO order_items (
    product_id, product_name, description, price, category, subcategory,
    image_path, order_date, sizes, mobile, platform, status,
    approved_at, shipped_at, out_for_delivery_at, delivered_at, cancelled_at
) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');

foreach ($ordersByPlatform as $platform => $orders) {
    foreach ($orders as $o) {
        $chk->bind_param('isss', $o['id'], $o['order_date'], $mobile, $platform);
        $chk->execute();
        if ($chk->get_result()->num_rows) continue; // already stored

        $sizes = 'N/A';
        $ins->bind_param('issdsssssssssssss',
            $o['id'],
            $o['product_name'],
            $o['description'],
            $o['price'],
            $o['category'],
            $o['subcategory'],
            $o['image_path'],
            $o['order_date'],
            $sizes,
            $mobile,
            $platform,
            $o['status'],
            $o['approved_at'],
            $o['shipped_at'],
            $o['out_for_delivery_at'],
            $o['delivered_at'],
            $o['cancelled_at']
        );
        $ins->execute();
    }
}

// ───────── 5. Retrieve merged list ─────────
$sel = $conn_ust->prepare('SELECT * FROM order_items WHERE mobile = ? AND platform IN ("AMAZON","FLIPKART") ORDER BY order_date DESC');
$sel->bind_param('s', $mobile);
$sel->execute();
$orders = $sel->get_result();

$conn_amazon->close();
$conn_flipkart->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Orders</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f1f1f1;
      margin: 0;
    }
    header {
      background: #131921;
      color: #fff;
      padding: 15px;
      text-align: center;
    }
    h1 {
      margin: 0;
      color: #1e90ff;
    }
    .orders {
      margin: 20px;
      display: flex;
      flex-wrap: wrap;
      gap: 40px;
    }
    .order {
      background: #fff;
      padding: 20px;
      width: 22%;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,.1);
    }
    .order img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: 8px;
    }
    .back-link,
    .track {
      display: inline-block;
      padding: 10px 15px;
      border-radius: 5px;
      color: #fff;
      text-decoration: none;
    }
    .back-link {
      background: #1e90ff;
      margin: 20px;
    }
    .back-link:hover {
      background: #63b3ed;
    }
    .track {
      background: #28a745;
      margin-top: 10px;
    }
    .track:hover {
      background: #218838;
    }
  </style>
</head>
<body>

<header><h1>My Orders</h1></header>
<a class="back-link" href="index.php">← Back to Home</a>

<section class="orders">
<?php if ($orders->num_rows): while ($r = $orders->fetch_assoc()):
    $base = $r['platform'] === 'AMAZON' ? 'Amazon' : 'Flipkart';
    $src  = "http://localhost/{$base}/" . htmlspecialchars($r['image_path']);
?>
  <div class="order">
    <img src="<?= $src ?>" alt="<?= htmlspecialchars($r['product_name']) ?>">
    <h3><?= htmlspecialchars($r['product_name']) ?> (<?= $r['platform'] ?>)</h3>
    <p><?= htmlspecialchars($r['description']) ?></p>
    <p>Price: ₹<?= number_format($r['price'], 2) ?></p>
    <p><strong>Order Date:</strong> <?= $r['order_date'] ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars($r['status']) ?></p>
    <a class="track" href="track_order.php?order_id=<?= $r['id'] ?>">Track Order</a>
  </div>
<?php endwhile; else: ?>
  <p style="margin:20px;">You have no orders.</p>
<?php endif; ?>
</section>

<?php $conn_ust->close(); ?>
</body>
</html>

<?php
session_start();
if (!isset($_POST['product_id'])) {
    die("Product not specified.");
}
$_SESSION['product_id'] = $_POST['product_id'];
?>

<form action="payment.php" method="POST">
  <label>Full Name:</label><input type="text" name="full_name" required><br>
  <label>Address:</label><textarea name="address" required></textarea><br>
  <label>City:</label><input type="text" name="city" required><br>
  <label>Pincode:</label><input type="text" name="pincode" required><br>
  <label>Phone:</label><input type="text" name="phone" required><br>
  <button type="submit">Proceed to Payment</button>
</form>

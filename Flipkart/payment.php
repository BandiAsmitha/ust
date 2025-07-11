<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_SESSION['address'] = [
        'full_name' => $_POST['full_name'],
        'address'   => $_POST['address'],
        'city'      => $_POST['city'],
        'pincode'   => $_POST['pincode'],
        'phone'     => $_POST['phone']
    ];
}
?>

<form action="place_order.php" method="POST">
  <label><input type="radio" name="payment_method" value="COD" required> Cash on Delivery</label><br>
  <label><input type="radio" name="payment_method" value="PhonePe" required> PhonePe</label><br>
  <label><input type="radio" name="payment_method" value="GPay" required> GPay</label><br>
  <label><input type="radio" name="payment_method" value="Paytm" required> Paytm</label><br>
  <button type="submit">Place Order</button>
</form>

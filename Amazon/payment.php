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

<!DOCTYPE html>
<html>
<head>
  <title>Select Payment Method</title>
  <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color:rgb(53, 90, 126);
        margin: 0;
        padding: 20px;
    }

    h2 {
        text-align: center;
        color: #333;
    }

    form {
        background-color: #ffffff;
        max-width: 500px;
        margin: 40px auto;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    label {
        display: block;
        padding: 12px;
        background-color: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 5px;
        margin-bottom: 15px;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }

    label:hover {
        background-color: #eef3ff;
    }

    input[type="radio"] {
        margin-right: 10px;
        transform: scale(1.2);
        vertical-align: middle;
    }

    button {
        background-color: #28a745;
        color: white;
        border: none;
        padding: 12px 20px;
        font-size: 16px;
        border-radius: 5px;
        cursor: pointer;
        width: 100%;
        transition: background-color 0.3s ease;
    }

    button:hover {
        background-color: #218838;
    }
  </style>
</head>
<body>

  <h2>Select Payment Method</h2>

  <form action="place_order.php" method="POST">
    <label><input type="radio" name="payment_method" value="COD" required> Cash on Delivery</label>
    <label><input type="radio" name="payment_method" value="PhonePe" required> PhonePe</label>
    <label><input type="radio" name="payment_method" value="GPay" required> GPay</label>
    <label><input type="radio" name="payment_method" value="Paytm" required> Paytm</label>
    
    <button type="submit">Place Order</button>
  </form>

</body>
</html>

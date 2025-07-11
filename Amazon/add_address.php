<?php
session_start();
if (!isset($_POST['product_id'])) {
    die("Product not specified.");
}
$_SESSION['product_id'] = $_POST['product_id'];
?>

<!DOCTYPE html>
<html>
<head>
  <title>Add Address</title>
  <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 20px;
    }

    h2 {
        text-align: center;
        color: #333;
    }

    form {
        background-color: white;
        max-width: 500px;
        margin: 20px auto;
        padding: 30px;
        border-radius: 5px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
    }

    input, textarea {
        width: 100%;
        padding: 10px;
        margin-bottom: 20px;
        border-radius: 5px;
        border: 1px solid #ccc;
        font-size: 14px;
    }

    button {
        background-color: #1e90ff;
        color: white;
        padding: 12px 20px;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
        transition: 0.3s;
        width: 100%;
    }

    button:hover {
        background-color: #006ad1;
    }
  </style>
</head>
<body>
  <h2>Add Address</h2>
  <form action="payment.php" method="POST">
    <label>Full Name:</label>
    <input type="text" name="full_name" required>

    <label>Address:</label>
    <textarea name="address" required></textarea>

    <label>City:</label>
    <input type="text" name="city" required>

    <label>Pincode:</label>
    <input type="text" name="pincode" required>

    <label>Phone:</label>
    <input type="text" name="phone" required>

    <button type="submit">Proceed to Payment</button>
  </form>
</body>
</html>

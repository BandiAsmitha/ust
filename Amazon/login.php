<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login Options</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background-color:rgb(53, 90, 126);;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }

    .login-container {
      background-color: white;
      padding: 40px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
      text-align: center;
      width: 300px;
    }

    h2 {
      color: #1e90ff;
      margin-bottom: 30px;
    }

    .login-option {
      margin: 15px 0;
    }

    .login-option a {
      display: block;
      padding: 12px;
      background-color: #1e90ff;
      color: white;
      text-decoration: none;
      border-radius: 5px;
      transition: background-color 0.3s;
    }

    .login-option a:hover {
      background-color: #0d6efd;
    }
  </style>
</head>
<body>

<div class="login-container">
  <h2>Login As</h2>
  
  <div class="login-option">
    <a href="user_dashboard.php">User</a>
  </div>

  <div class="login-option">
    <a href="seller_login.php">Seller</a>
  </div>
</div>

</body>
</html>

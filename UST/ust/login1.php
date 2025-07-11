<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to UST</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-image: url('http://localhost/UST/ust/bg.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .container {
            background-image: url('http://localhost/UST/ust/bg1.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            opacity: 0.9;
        }

        h1 {
            text-align: center;
        }

        .role-selection {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
        }

        .role-btn {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            text-align: center;
            display: inline-block;
            text-decoration: none;
        }

        .role-btn:hover {
            background-color: #0056b3;
        }

        .login-form, .signup-form {
            display: none;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-top: 10px;
        }

        input {
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid rgba(255, 255, 255, 0.5);
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            transition: all 0.3s ease;
        }

        input:focus {
            background-color: rgba(255, 255, 255, 0.4);
            border-color: #007bff;
            outline: none;
        }

        button {
            padding: 10px;
            margin-top: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }

        p {
            text-align: center;
            margin-top: 10px;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .error {
            color: red;
            font-size: 14px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to UST!</h1>

        <div class="role-selection" id="roleSelection">
            <a href="admin_login.php" class="role-btn" id="adminBtn">Admin</a>
            <a href="user_login.php" class="role-btn" id="userBtn">User</a>
            <a href="seller_login.php" class="role-btn" id="sellerBtn">Seller</a>
        </div>

    </div>
</body>
</html>

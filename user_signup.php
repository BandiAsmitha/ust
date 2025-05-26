<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
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
            padding: 30px;
            border-radius: 10px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
            opacity: 0.95;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
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
        }

        input:focus {
            background-color: rgba(255, 255, 255, 0.4);
            border-color: #007bff;
            outline: none;
        }

        button {
            padding: 10px;
            margin-top: 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover {
            background-color: #218838;
        }

        p {
            text-align: center;
            margin-top: 15px;
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
    <h2>Register</h2>
    <form action="user_register.php" method="POST" onsubmit="return validateForm()">
        <label for="username">Username:</label>
        <input type="text" name="username" required>

        <label for="phone_number">Phone Number:</label>
        <input type="text" name="phone_number" maxlength="10" pattern="\d{10}" title="Enter 10-digit phone number" required>

        <label for="email">Email:</label>
        <input type="email" name="email" required>

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required>

        <label for="confirm_password">Confirm Password:</label>
        <input type="password" name="confirm_password" id="confirm_password" required>

        <button type="submit" name="register">Register</button>
    </form>

    <p>Already have an account? <a href="user_login.php">Login here</a></p>
</div>

<script>
function validateForm() {
    const password = document.getElementById("password").value;
    const confirm = document.getElementById("confirm_password").value;
    const phone = document.querySelector('input[name="phone_number"]').value;

    if (!/^\d{10}$/.test(phone)) {
        alert("Phone number must be exactly 10 digits.");
        return false;
    }

    if (password !== confirm) {
        alert("Passwords do not match.");
        return false;
    }

    return true;
}
</script>

</body>
</html>

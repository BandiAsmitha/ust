<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>UST</title>
  <style>
    /* Importing Font */
    @import url('https://fonts.googleapis.com/css?family=Exo:400,700');

    /* Reset margin and padding */
    * {
      margin: 0;
      padding: 0;
    }

    /* Body Styling */
    body {
      font-family: 'Exo', sans-serif;
      overflow: hidden;
    }

    /* Main Area Styling */
    .area {
      background: #4e54c8;  
      background: -webkit-linear-gradient(to left, #8f94fb, #4e54c8);  
      width: 100%;
      height: 100vh;
      position: relative; 
      display: flex;
      justify-content: center;
      align-items: center;
    }

    /* Context for title text */
    .context {
      text-align: center;
      color: #fff;
      font-size: 50px;
      opacity: 0;
      animation: fadeIn 3s forwards;
      animation-delay: 2s; /* Delay for 2 seconds */
    }

    .context img {
      height: 200px;
      width: 200px;
      margin-bottom: 20px;
    }

    .context h1 {
      font-size: 50px;
    }

    .context h2 {
      font-size: 25px;
    }

    /* Circles Animation */
    .circles {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      overflow: hidden;
    }

    .circles li {
      position: absolute;
      display: block;
      list-style: none;
      width: 20px;
      height: 20px;
      background: rgba(255, 255, 255, 0.2);
      animation: animate 25s linear infinite;
      bottom: -150px;
    }

    /* Circle Animations */
    .circles li:nth-child(1) {
      left: 25%;
      width: 80px;
      height: 80px;
      animation-delay: 0s;
    }

    .circles li:nth-child(2) {
      left: 10%;
      width: 20px;
      height: 20px;
      animation-delay: 2s;
      animation-duration: 12s;
    }

    .circles li:nth-child(3) {
      left: 70%;
      width: 20px;
      height: 20px;
      animation-delay: 4s;
    }

    .circles li:nth-child(4) {
      left: 40%;
      width: 60px;
      height: 60px;
      animation-delay: 0s;
      animation-duration: 18s;
    }

    .circles li:nth-child(5) {
      left: 65%;
      width: 20px;
      height: 20px;
      animation-delay: 0s;
    }

    .circles li:nth-child(6) {
      left: 75%;
      width: 110px;
      height: 110px;
      animation-delay: 3s;
    }

    .circles li:nth-child(7) {
      left: 35%;
      width: 150px;
      height: 150px;
      animation-delay: 7s;
    }

    .circles li:nth-child(8) {
      left: 50%;
      width: 25px;
      height: 25px;
      animation-delay: 15s;
      animation-duration: 45s;
    }

    .circles li:nth-child(9) {
      left: 20%;
      width: 15px;
      height: 15px;
      animation-delay: 2s;
      animation-duration: 35s;
    }

    .circles li:nth-child(10) {
      left: 85%;
      width: 150px;
      height: 150px;
      animation-delay: 0s;
      animation-duration: 11s;
    }

    /* Keyframes for Circles Animation */
    @keyframes animate {
      0% {
        transform: translateY(0) rotate(0deg);
        opacity: 1;
        border-radius: 0;
      }
      100% {
        transform: translateY(-1000px) rotate(720deg);
        opacity: 0;
        border-radius: 50%;
      }
    }

    /* Keyframes for Fade-in Animation */
    @keyframes fadeIn {
      0% {
        opacity: 0;
      }
      100% {
        opacity: 1;
      }
    }

  </style>
</head>
<body>
  <!-- Main Area with Animated Circles -->
  <div class="area">
    <ul class="circles">
      <li></li>
      <li></li>
      <li></li>
      <li></li>
      <li></li>
      <li></li>
      <li></li>
      <li></li>
      <li></li>
      <li></li>
    </ul>

    <!-- Context Title -->
    <div class="context">
      <img src="ust-logo.jpg" alt="UST Logo">
      <h1>UST</h1>
      <h2>Unified Shopping and Tracking</h2>
    </div>
  </div>

  <!-- Script to redirect to home.html after delay -->
  <script>
    // Redirect to home.html after 6 seconds (6000ms)
    setTimeout(function() {
      window.location.href = 'login1.php';
    }, 6000); 
  </script>
</body>
</html>

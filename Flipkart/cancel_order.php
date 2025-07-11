<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['mobile'])) {
    die("Session expired. Please log in again.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['order_id']) || !is_numeric($_POST['order_id']) || empty($_POST['reason'])) {
        die("Invalid request.");
    }

    $order_id = intval($_POST['order_id']);
    $reason = $_POST['reason'];
    $mobile = $_SESSION['mobile'];

    // DB connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "Flipkart";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get user_id
    $sql_user = "SELECT id FROM login WHERE phone_number = ?";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("s", $mobile);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();

    if ($result_user->num_rows === 0) {
        die("User not found.");
    }
    $user_id = $result_user->fetch_assoc()['id'];

    // Update order status to cancelled
    $cancel_time = date('Y-m-d H:i:s');
    $sql = "UPDATE orders SET status = 'Cancelled', cancellation_reason = ?, cancelled_at = ? WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $reason, $cancel_time, $order_id, $user_id);

    if ($stmt->execute()) {
        header("Location: orders.php?cancelled=success");
        exit();
    } else {
        echo "Error cancelling order: " . $conn->error;
    }

    $conn->close();
} else {
    die("Invalid request method.");
}
?>

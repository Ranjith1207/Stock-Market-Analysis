<?php
// register.php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password before storing

    // Check if username already exists
    $checkUser = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $checkUser->bind_param('s', $username);
    $checkUser->execute();
    $result = $checkUser->get_result();

    if ($result->num_rows > 0) {
        // Username already exists, show error and stay on the registration page
        echo "<script>alert('Username already taken. Please choose another one.'); window.location.href='register.html';</script>";
    } else {
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param('ss', $username, $password);
        if ($stmt->execute()) {
            // Redirect to login page after successful registration
            echo "<script>window.location.href='login.html';</script>";
        } else {
            // Handle database errors
            echo "<script>alert('Error: " . $stmt->error . "'); window.location.href='register.html';</script>";
        }
    }
}
?>

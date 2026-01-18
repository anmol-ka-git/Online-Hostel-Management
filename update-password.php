<?php
session_start();
include("connection.php"); // Ensure connection is established

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['token']) || empty($_POST['token'])) {
        die("Error: Token is required.");
    }
    if (!isset($_POST['password']) || empty($_POST['password'])) {
        die("Error: New password is required.");
    }
    if (!isset($_POST['confirm_password']) || empty($_POST['confirm_password'])) {
        die("Error: Confirm password is required.");
    }
    if ($_POST['password'] !== $_POST['confirm_password']) {
        die("Error: Passwords do not match.");
    }

    $token = trim($_POST['token']);
    $new_password = trim($_POST['password']);

    // ✅ Fetch email from the student table using token
    $sql = "SELECT email FROM student WHERE reset_token = '$token' AND token_expiry > " . time();
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $email = $row['email'];

        // ✅ Hash the new password using SHA-256
        $hashed_password = hash('sha256', $new_password);

        // ✅ Update password in student table
        $update_sql = "UPDATE student SET password = '$hashed_password', reset_token = NULL, token_expiry = NULL WHERE email = '$email'";
        
        if ($conn->query($update_sql) === TRUE) {
            echo "alert('Password updated successfully!')";
             header("Location: login.html");
        } else {
            echo "Error updating password.";
        }
    } else {
        die("Error: Invalid or expired token.");
    }

    $conn->close();
}
?>

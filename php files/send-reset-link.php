<?php
include 'connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    if ($email == '') {
        die("Error: Email is required.");
    }

    // Check if email exists in student table
    $email_query = "SELECT email FROM student WHERE email = '$email'";
    $result = $conn->query($email_query);

    if ($result->num_rows == 0) {
        die("Error: Email not found.");
    }

    // Generate a unique token (SHA1 for compatibility with PHP 5.3.5)
    $token = sha1(uniqid(mt_rand(), true));
    $expiry = time() + 3600; // Token expires in 1 hour

    // Store token in student table
    $token_query = "UPDATE student SET reset_token='$token', token_expiry='$expiry' WHERE email='$email'";
    
    if ($conn->query($token_query) === TRUE) {
        // Generate reset link
        $resetLink = "http://localhost/Online%20Hostel%20Management%20System/reset-password.php?token=" . $token;
        $subject = "Password Reset Request";
        $message = "Click the link to reset your password: $resetLink";
        $headers = "From: anmolnanwani0811@gmail.com\r\n";
        
        if(mail($email,$subject,$message,$headers)){
        echo "A password reset link sent to your email";
    } 
}else {
        die("Error saving token: " . $conn->error);
    }

    $conn->close();
}
?>



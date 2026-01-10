<?php
$to = "suraj03mehta01@gmail.com";
$subject = "Test Email from WAMP";
$message = "This is a test email.";
$headers = "From: anmolnanwani0811@gmail.com";

if (mail($to, $subject, $message, $headers)) {
    echo "Email sent successfully!";
} else {
    echo "Failed to send email. Check WAMP mail settings.";
}
?>

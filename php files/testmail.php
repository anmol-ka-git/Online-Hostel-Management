<?php
if (mail("suraj03mehta01@gmail.com", "Test Email", "This is a test message", "From: anmolnanwani0811@gmail.com")) {
    echo "Mail sent successfully!";
} else {
    echo "Mail sending failed!";
}
?>

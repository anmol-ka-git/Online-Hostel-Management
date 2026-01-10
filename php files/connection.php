<?php
$conn = mysqli_connect("localhost", "root", "", "hostel_management"); //  database name

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>

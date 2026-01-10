<?php
session_start();
include('connection.php');

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_SESSION['student_id'];
    $selected_room_type = $_POST['selected_room_type'];
    $selected_room_price = $_POST['selected_room_price'];

    $_SESSION['selected_room_type'] = $selected_room_type;
    $_SESSION['selected_room_price'] = $selected_room_price;
    $_SESSION['room_number'] = $_POST['room_number'];  // Ensure room number is stored

    header("Location: room-details.php");
    exit();
}
?>

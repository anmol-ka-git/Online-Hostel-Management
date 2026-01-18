<?php
session_start();
include 'connection.php';

// Ensure student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $room_cost = $_POST['room_cost'];
    $mess_cost = $_POST['mess_cost'];
    $total_cost = $_POST['total_cost'];
    $payment_status = $_POST['payment_status'];
    $date = date("Y-m-d");

    // Insert booking record
    $insert_booking = "INSERT INTO booking (Room_number, Student_id, start_date, end_date) 
                       VALUES ('$room_number', '$student_id', NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR))";

    if (mysqli_query($conn, $insert_booking)) {
        $booking_id = mysqli_insert_id($conn);

        // Mark room as occupied
        $update_room = "UPDATE room SET status = 'Occupied' WHERE room_number = '$room_number'";
        mysqli_query($conn, $update_room);

        // Insert payment record
        $payment_status_value = ($payment_status == 'Pay Now') ? 'Paid' : 'Pending';
        $insert_payment = "INSERT INTO payment (bid, date, amount, method, status) 
                           VALUES ('$booking_id', '$date', '$total_cost', 'Online', '$payment_status_value')";

        if (mysqli_query($conn, $insert_payment)) {
            if ($payment_status == "Pay Now") {
                $_SESSION['booking_id'] = $booking_id;
                header("Location: payment.php");
                exit();
            } else {
                header("Location: student-dashboard.php?msg=Room booked! Please pay within 5 days.");
                exit();
            }
        } else {
            echo "Error inserting payment record: " . mysqli_error($conn);
        }
    } else {
        echo "Error inserting booking record: " . mysqli_error($conn);
    }
} else {
    echo "Invalid request!";
}
?>

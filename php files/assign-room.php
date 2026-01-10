<?php
include 'connection.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $student_id = $_POST['student_id'];

    if (isset($_POST['assign_room']) || isset($_POST['edit_room'])) {
        $room_number = $_POST['room_number'];

        // Check if the room exists
        $checkRoom = mysqli_query($conn, "SELECT * FROM room WHERE room_number = '$room_number'");
        if (mysqli_num_rows($checkRoom) == 0) {
            echo "<script>alert('Invalid or unavailable room.'); window.location.href='manage-rooms.php';</script>";
            exit;
        }

        // Check if the student already has a room assigned
        $checkStudent = mysqli_query($conn, "SELECT Room_number FROM student WHERE Sid = '$student_id'");
        $studentData = mysqli_fetch_assoc($checkStudent);

        if (!empty($studentData['Room_number'])) {
            echo "<script>alert('Student already has a room assigned. Edit or remove first.'); window.location.href='manage-rooms.php';</script>";
            exit;
        }

        // Step 1: Assign Room to Student (Fill NULL values)
        $updateStudent = mysqli_query($conn, "UPDATE student SET Room_number = '$room_number' WHERE Sid = '$student_id' AND Room_number IS NULL");

        // Step 2: Update existing entry in Booking Table
        $updateBooking = mysqli_query($conn, "UPDATE booking SET Room_number = '$room_number' WHERE Student_id = '$student_id' AND Room_number IS NULL");

        // Step 3: Update Room Table (if needed)
        $updateRoom = mysqli_query($conn, "UPDATE room SET status = 'Occupied' WHERE room_number = '$room_number'");

        if ($updateStudent && $updateBooking && $updateRoom) {
            echo "<script>alert('Room assigned successfully.'); window.location.href='manage-rooms.php';</script>";
        } else {
            echo "<script>alert('Failed to assign room.'); window.location.href='manage-rooms.php';</script>";
        }
    }

    // Delete Room Assignment
    if (isset($_POST['delete_room'])) {
        // Step 1: Remove Room from Student Table
        $deleteStudent = mysqli_query($conn, "UPDATE student SET Room_number = NULL WHERE Sid = '$student_id'");

        // Step 2: Update Booking Table (Set Room_number to NULL)
        $updateBooking = mysqli_query($conn, "UPDATE booking SET Room_number = NULL WHERE Student_id = '$student_id'");

        if ($deleteStudent && $updateBooking) {
            echo "<script>alert('Room assignment removed.'); window.location.href='manage-rooms.php';</script>";
        } else {
            echo "<script>alert('Failed to remove room assignment.'); window.location.href='manage-rooms.php';</script>";
        }
    }
}
?>

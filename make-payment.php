<?php
$conn = mysqli_connect("localhost", "root", "", "hostel_management");
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $amount = $_POST['amount'];

    // Fetch student name
    $student_query = "SELECT name FROM students WHERE id = $student_id";
    $result = mysqli_query($conn, $student_query);
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $student_name = $row['name'];

        // Insert payment record
        $insert_payment = "INSERT INTO payments (student_id, student_name, amount) VALUES ($student_id, '$student_name', $amount)";
        if (mysqli_query($conn, $insert_payment)) {
            // Log the activity
            $log_query = "INSERT INTO activities (description) VALUES ('Payment received: $student_name - ₹$amount')";
            mysqli_query($conn, $log_query);

            echo "<script>alert('Payment recorded successfully!');</script>";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    } else {
        echo "<script>alert('Invalid Student ID');</script>";
    }
}
?>
<?php
session_start();
include('connection.php'); // Ensure correct file inclusion

// Check database connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Check if the user is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: login.html");
    exit();
}

$student_id = $_SESSION['student_id'];

// Step 1: Check if the student already has a booking
$check_query = "SELECT bid, start_date, end_date, mess_type, mess_cost FROM booking WHERE Student_id = ?";
$stmt = $conn->prepare($check_query);

if (!$stmt) {
    die("Error in SQL prepare: " . $conn->error); // Debugging output
}

$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$existing_booking = $result->fetch_assoc();

if ($existing_booking) {
    // Store details in session for auto-fill
    $_SESSION['bid'] = $existing_booking['bid'];
    $_SESSION['start_date'] = $existing_booking['start_date'];
    $_SESSION['end_date'] = $existing_booking['end_date'];
    $_SESSION['mess_service'] = $existing_booking['mess_type']; // Fixed variable name
    $_SESSION['mess_cost'] = $existing_booking['mess_cost'];

    // Do not redirect here. Let book-hostel.php or form submission handle it.
}

// Step 2: Insert new booking if none exists
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];
$mess_service = isset($_POST['mess_service']) ? $_POST['mess_service'] : 'None';
$mess_cost = ($mess_service == 'Veg') ? 2000 : (($mess_service == 'Non-Veg') ? 2500 : 0);

$default_room = NULL; // No room assigned initially

$insert_query = "INSERT INTO booking (Room_number, Student_id, start_date, end_date, mess_type, mess_cost) 
                 VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($insert_query);

if (!$stmt) {
    die("Error in SQL prepare (insert): " . $conn->error); // Debugging output
}

$stmt->bind_param("iisssi", $default_room, $student_id, $start_date, $end_date, $mess_service, $mess_cost);

if ($stmt->execute()) {
    // Store new booking details in session
    $_SESSION['bid'] = $stmt->insert_id;
    $_SESSION['start_date'] = $start_date;
    $_SESSION['end_date'] = $end_date;
    $_SESSION['mess_service'] = $mess_service;
    $_SESSION['mess_cost'] = $mess_cost;

    // Do not redirect here. Let book-hostel.php handle it.
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>

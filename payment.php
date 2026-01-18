<?php
session_start();
include 'connection.php';

// ✅ Step 1: Ensure the database connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// ✅ Step 2: Fetch Student ID from session
if (!isset($_SESSION['student_id'])) {
    die("❌ Error: Session 'student_id' is not set. Please log in again.");
}
$student_id = $_SESSION['student_id'];
//var_dump($student_id); // Debugging: Check if Student ID is available

// ✅ Step 3: Fetch Booking ID from the database
$query = "SELECT bid FROM booking WHERE Student_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();
$booking_id = isset($booking['bid']) ? $booking['bid'] : NULL;

//var_dump($booking_id); // Debugging: Check if Booking ID is fetched

if ($booking_id === NULL) {
    die("❌ Error: No matching booking record found for Student ID = $student_id.");
}

// ✅ Step 4: Fetch payment details using Student ID and Booking ID
$query = "SELECT 
    s.Student_name, 
    s.Email,
    COALESCE(r.price, 0) AS room_cost,  
    COALESCE(r.room_type, 'Not Assigned') AS room_type,
    COALESCE(b.mess_cost, 0) AS mess_cost, 
    COALESCE(b.mess_type, 'Not Selected') AS mess_type,
    (COALESCE(r.price, 0) + COALESCE(b.mess_cost, 0)) AS total_cost
FROM student s 
JOIN booking b ON b.Student_id = s.Sid 
LEFT JOIN room r ON b.bid = r.bid  
WHERE s.Sid = ? AND b.bid = ?;";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("❌ Query preparation failed: " . $conn->error);
}

$stmt->bind_param("ii", $student_id, $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("❌ Error: No matching records found for Student ID = $student_id and Booking ID = $booking_id.");
}

$row = $result->fetch_assoc();
$student_name = $row['Student_name'];
$student_email = $row['Email']; 
$room_cost = $row['room_cost'];
$room_type = $row['room_type'];
$mess_cost = $row['mess_cost'];
$mess_type = $row['mess_type'];
$total_cost = $room_cost + $mess_cost;

$result->free(); // Free memory

// ✅ Step 5: Process Payment
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $payment_method = $_POST['payment_method'];
    $payment_status = ($_POST['action'] == "Pay Now") ? "Successful" : "Pending";
    $date = date("Y-m-d");

    // Check if payment entry exists
    $check_payment = "SELECT * FROM payment WHERE bid = ?";
    $stmt = $conn->prepare($check_payment);
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $payment_result = $stmt->get_result();

    if ($payment_result->num_rows > 0) {
        $update_payment = "UPDATE payment SET method = ?, status = ?, date = ?, amount = ? WHERE bid = ?";
        $stmt = $conn->prepare($update_payment);
        $stmt->bind_param("sssdi", $payment_method, $payment_status, $date, $total_cost, $booking_id);
    } else {
        $update_payment = "INSERT INTO payment (bid, date, amount, method, status) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($update_payment);
        $stmt->bind_param("isdss", $booking_id, $date, $total_cost, $payment_method, $payment_status);
    }

    if ($stmt->execute()) {
        if ($payment_status == "Successful") {
            // Send confirmation email only if payment is successful
            $subject = "Hostel Booking Confirmation";
            $message = "Dear $student_name,\n\n";
            $message .= "Your payment of ₹$total_cost has been received. \n\nRoom Type: $room_type (₹$room_cost) \nMess: $mess_type (₹$mess_cost)\n\nThank you!";
            
            $headers = "From: anmolnanwani0811@gmail.com\r\n";
            $headers .= "Reply-To: anmolnanwani0811@gmail.com\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

            mail($student_email, $subject, $message, $headers);
            $confirmation_msg = " Payment successful! Please check your email for confirmation.";
        } else {
            $confirmation_msg = "Room booked! Pay within 15 days.";
        }

        echo "<script>
    alert('$confirmation_msg');
    window.location.href = 'student-dashboard.php';
</script>";
exit();

    } else {
        echo "❌ Error updating payment: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
</head>
<body>
    <h2>Payment</h2>
    <p><strong>Student Name:</strong> <?php echo htmlspecialchars($student_name); ?></p>
    <p><strong>Room Type:</strong> <?php echo htmlspecialchars($room_type); ?></p>
    <p><strong>Room Cost:</strong> ₹<?php echo htmlspecialchars($room_cost); ?></p>
    <p><strong>Mess Type:</strong> <?php echo htmlspecialchars($mess_type); ?></p>
    <p><strong>Mess Cost:</strong> ₹<?php echo htmlspecialchars($mess_cost); ?></p>
    <p><strong>Total Cost:</strong> ₹<?php echo htmlspecialchars($total_cost); ?></p>

    <form method="post">
        <label for="payment_method">Select Payment Method:</label>
        <select name="payment_method" required>
            <option value="Debit Card">Debit Card</option>
            <option value="UPI Payment">UPI Payment</option>
        </select>

        <button type="submit" name="action" value="Pay Now">Pay Now (Successful)</button>
        <button type="submit" name="action" value="Pay Later">Save & Pay Later (Pending)</button>
    </form>
</body>
</html>

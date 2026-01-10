<?php
session_start();
include("connection.php");

// Ensure user is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$stmt = $conn->prepare("SELECT Student_name FROM student WHERE Sid = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->bind_result($student_name);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2 class="mb-3">Welcome, <?php echo htmlspecialchars($student_name); ?>! 🎉</h2>
        <p>Your dashboard is ready.</p>
        <div class="list-group">
            <a href="book_hostel.php" class="list-group-item list-group-item-action">📅 Book Hostel</a>
            <a href="room_details.php" class="list-group-item list-group-item-action">🛏 Room Details</a>
            <a href="feedback.php" class="list-group-item list-group-item-action">✍ Feedback</a>
            <a href="my-profile.php" class="list-group-item list-group-item-action">👤 My Profile</a>
        </div>
    </div>
</body>
</html>

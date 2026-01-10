<?php
session_start();

include 'connection.php';

// Check if session has student_id
if (!isset($_SESSION['student_id'])) {
    die("Error: Student ID not found in session. <a href='register.php'>Go back</a>");
}

$student_id = $_SESSION['student_id'];

// Fetch student details using MySQLi procedural approach for PHP 5.3.5 compatibility
$query = "SELECT Age, College, Course, Address, Contact, Father_name, Mother_name, Blood_group, Father_number FROM student WHERE Sid = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->bind_result($age, $college, $course, $address, $contact, $father_name, $mother_name, $blood_group, $father_number);

$student = array();
if ($stmt->fetch()) {
    $student = array(
        'Age' => $age,
        'College' => $college,
        'Course' => $course,
        'Address' => $address,
        'Contact' => $contact,
        'Father_name' => $father_name,
        'Mother_name' => $mother_name,
        'Blood_group' => $blood_group,
        'Father_number' => $father_number
    );
}
$stmt->close();

// If no student is found, show error
if (empty($student)) {
    die("Error: Student not found in database. <a href='register.php'>Go back</a>");
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Complete Your Profile</h2>
        <form action="update-profile.php" method="POST">
            <div class="mb-3">
                <label class="form-label">Age:</label>
                <input type="number" class="form-control" name="age" value="<?php echo htmlspecialchars($student['Age']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">College:</label>
                <input type="text" class="form-control" name="college" value="<?php echo htmlspecialchars($student['College']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Course:</label>
                <input type="text" class="form-control" name="course" value="<?php echo htmlspecialchars($student['Course']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Address:</label>
                <input type="text" class="form-control" name="address" value="<?php echo htmlspecialchars($student['Address']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Contact:</label>
                <input type="text" class="form-control" name="contact" value="<?php echo htmlspecialchars($student['Contact']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Father's Name:</label>
                <input type="text" class="form-control" name="father_name" value="<?php echo htmlspecialchars($student['Father_name']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Mother's Name:</label>
                <input type="text" class="form-control" name="mother_name" value="<?php echo htmlspecialchars($student['Mother_name']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Blood Group:</label>
                <select class="form-control" name="blood_group" required>
                    <option value="">Select Blood Group</option>
                    <option value="A+" <?php if ($student['Blood_group'] == 'A+') echo 'selected'; ?>>A+</option>
                    <option value="A-" <?php if ($student['Blood_group'] == 'A-') echo 'selected'; ?>>A-</option>
                    <option value="B+" <?php if ($student['Blood_group'] == 'B+') echo 'selected'; ?>>B+</option>
                    <option value="B-" <?php if ($student['Blood_group'] == 'B-') echo 'selected'; ?>>B-</option>
                    <option value="O+" <?php if ($student['Blood_group'] == 'O+') echo 'selected'; ?>>O+</option>
                    <option value="O-" <?php if ($student['Blood_group'] == 'O-') echo 'selected'; ?>>O-</option>
                    <option value="AB+" <?php if ($student['Blood_group'] == 'AB+') echo 'selected'; ?>>AB+</option>
                    <option value="AB-" <?php if ($student['Blood_group'] == 'AB-') echo 'selected'; ?>>AB-</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Father's Contact Number:</label>
                <input type="text" class="form-control" name="father_number" value="<?php echo htmlspecialchars($student['Father_number']); ?>" required>
            </div>

            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
    </div>
</body>
</html>

<?php
session_start();
include 'connection.php';

// ✅ Ensure user is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// ✅ Check if form data is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {  // ✅ Ensure the POST method check is enclosed
   
    
    //$required_fields = ['age', 'college', 'course', 'address', 'contact', 'father_name', 'mother_name', 'blood_group', 'father_number'];
    $required_fields = array('age', 'college', 'course', 'address', 'contact', 'father_name', 'mother_name', 'blood_group', 'father_number');

    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            die("Error: All fields are required.");
        }
    }

    
    // ✅ Sanitize & validate inputs
    $age = filter_var($_POST['age'], FILTER_VALIDATE_INT);
    $college = trim($_POST['college']);
    $course = trim($_POST['course']);
    $address = trim($_POST['address']);
    $contact = trim($_POST['contact']);
    $father_name = trim($_POST['father_name']);
    $mother_name = trim($_POST['mother_name']);
    $blood_group = trim($_POST['blood_group']);
    $father_number = trim($_POST['father_number']);

    if ($age === false) {
        die("Error: Invalid age.");
    }

    // ✅ Prepare & execute the update query
    $query = "UPDATE student 
              SET Age = ?, College = ?, Course = ?, Address = ?, Contact = ?, Father_name = ?, Mother_name = ?, Blood_group = ?, Father_number = ? 
              WHERE Sid = ?";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("issssssssi", $age, $college, $course, $address, $contact, $father_name, $mother_name, $blood_group, $father_number, $student_id);

        if ($stmt->execute()) {
            // ✅ Instead of logging out, redirect to student dashboard
            header("Location: student-dashboard.php");
            exit();
        } else {
            die("Error updating profile: " . $stmt->error);
        }

        $stmt->close();
    } else {
        die("Error preparing statement: " . $conn->error);
    }

    $conn->close();
} // ✅ Closing bracket correctly placed

else {
    die("Error: Invalid request.");
}
?>

<?php
session_start();
include 'connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Debugging: Print POST data (remove this after testing)
    // print_r($_POST); die();

    // Validate required fields
    if (!isset($_POST['Name'], $_POST['email'], $_POST['password'], $_POST['confirm_password'], $_POST['gender'])) {
        die("Error: Missing required fields. <a href='register.html'>Go back</a>");
    }

    // Get form data
    $name = trim($_POST['Name']);
    $surname = isset($_POST['Surname']) ? trim($_POST['Surname']) : NULL;
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $gender = trim($_POST['gender']);

    // Check if gender is valid
    if ($gender !== 'Male' && $gender !== 'Female') {
        die("Error: Invalid gender selected. <a href='register.html'>Go back</a>");
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        die("Passwords do not match. <a href='register.html'>Go back</a>");
    }

    // Hash the password for security
    $hashed_password = hash('sha256', $password);

    // Assign hostel based on gender
    $hostel_id = ($gender === 'Male') ? 1 : 2;

    // Find an available room based on gender
    $room_query = "SELECT room_number FROM room WHERE hostel_id = ? AND status = 'available' 
                   AND ((? = 'Male' AND room_number BETWEEN 500 AND 599) 
                   OR (? = 'Female' AND room_number >= 800)) 
                   LIMIT 1";

    if ($stmt = $conn->prepare($room_query)) {
        $stmt->bind_param("iss", $hostel_id, $gender, $gender);
        $stmt->execute();
        $room_result = $stmt->get_result();
        $room = $room_result->fetch_assoc();
        $room_number = $room ? $room['room_number'] : NULL;
        $stmt->close();
    } else {
        die("Error finding available room.");
    }

    // Insert student into the database
    $sql = "INSERT INTO student (Student_name, surname, Email, password, gender, user_role, Room_number) VALUES (?, ?, ?, ?, ?, 'student', ?)";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sssssi", $name, $surname, $email, $hashed_password, $gender, $room_number);
        if ($stmt->execute()) {
            $student_id = $conn->insert_id;
            $_SESSION['student_id'] = $student_id;
            $_SESSION['student_name'] = $name;

            // Update room status if assigned
            if ($room_number) {
                $update_room = "UPDATE room SET status = 'occupied' WHERE room_number = ?";
                if ($update_stmt = $conn->prepare($update_room)) {
                    $update_stmt->bind_param("i", $room_number);
                    $update_stmt->execute();
                    $update_stmt->close();
                }
            }

            // Redirect to complete profile page
            header("Location: complete-profile.php");
            exit();
        } else {
            die("Error inserting student: " . $stmt->error);
        }
        $stmt->close();
    } else {
        die("Error preparing SQL statement.");
    }

    // Close database connection
    $conn->close();
}
?>

<?php
session_start();
include("connection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST['email']) || empty($_POST['password'])) {
        die("Error: Email and Password are required.");
    }

    $email = $_POST['email'];
    $password = hash('sha256', $_POST['password']);

    $stmt = $conn->prepare("SELECT Sid, Student_name, password, user_role FROM student WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($sid, $student_name, $stored_password, $user_role);

    if ($stmt->fetch()) {
        if ($password === $stored_password) {
            $_SESSION['student_id'] = $sid;
            $_SESSION['student_name'] = $student_name;
            $_SESSION['role'] = $user_role;

            if ($user_role == 'admin') {
                header("Location: admin-dashboard.php");
            } else {
                header("Location: student-dashboard.php");
            }
            exit();
        } else {
            echo "Wrong Password!";
        }
    } else {
        echo "Email not found!";
    }

    $stmt->close();
    $conn->close();
}
?>

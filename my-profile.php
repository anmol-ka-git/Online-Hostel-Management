<?php
session_start();
include("connection.php");

// Ensure user is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Fetch student data including Blood_group
//$query = "SELECT Student_name, surname, gender, Age, College, Course, Address, Email, Contact, Blood_group FROM student WHERE Sid = '$student_id'";
$query = "SELECT * FROM student WHERE Sid = '$student_id'";

$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $student_name = $row['Student_name'];
    $surname = $row['surname'];
    $gender = $row['gender'];
    $age = $row['Age'];
    $college = $row['College'];
    $course = $row['Course'];
    $address = $row['Address'];
    $email = $row['Email'];
    $contact = $row['Contact'];
    $blood_group = $row['Blood_group'];
    $father_name = $row['Father_name'];
    $mother_name = $row['Mother_name'];
    $father_number = $row['Father_number'];
    $room_number = $row['Room_number'];
} else {
    die("Error fetching student data: " . mysqli_error($conn));
}

$response_message = "";

// Handle Suggestion Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['suggestion_text'])) {
    $suggestion = mysqli_real_escape_string($conn, $_POST['suggestion_text']);
    $sql = "INSERT INTO suggestions (student_id, suggestion, created_at) VALUES ('$student_id', '$suggestion', NOW())";

    if (mysqli_query($conn, $sql)) {
        $response_message = "Suggestion submitted successfully!";
    } else {
        $response_message = "Error submitting suggestion: " . mysqli_error($conn);
    }
}

// Handle Feedback Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['feedback_text'])) {
    $feedback = mysqli_real_escape_string($conn, $_POST['feedback_text']);
    $sql = "INSERT INTO feedback (student_id, feedback, created_at) VALUES ('$student_id', '$feedback', NOW())";

    if (mysqli_query($conn, $sql)) {
        $response_message = "Feedback submitted successfully!";
    } else {
        $response_message = "Error submitting feedback: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
               /* General Reset */
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Navigation Bar */
header {
            width: 100%;
            position: fixed;
            top: 0;
            left: 0;
            height: 80px;
            background-color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #f1f1f1;
            z-index: 1000;
        }

        .logo img {
            max-height: 60px;
        }

        nav ul {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        nav ul li {
            margin: 0 10px;
        }

        nav ul li a {
            text-decoration: none;
            color: black;
            font-weight: bold;
            font-size:18px;
            font-family:times new roman;
        }
        nav ul li a:hover
        {
            text-decoration:underline;
        }

        .cta {
            background-color:rgb(145, 116, 63);
            color: white;
            padding: 0.5rem 1rem;
            text-decoration: none;
            border-radius: 5px;
        }

    .sidebar {
    width: 220px;
    height: 100vh;
    background: #212529;
    color: white;
    padding-top: 20px;
    position: fixed; /* Keeps it in place */
    top: 80px; /* Below header */
    left: 0;
    overflow-y: auto;
    padding-bottom: 20px;
    z-index: 999; /* Ensures sidebar stays below nav */
}

.sidebar a {
    padding: 12px 20px;
    display: block;
    color: white;
    text-decoration: none;
    font-size: 1rem;
    border-radius: 5px;
    white-space: nowrap;
}

.sidebar a:hover {
    background: #495057;
}
 
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            padding: 15px;
        }

        .card:hover {
            transform: scale(1.02);
        }

        .recent-activities {
            max-height: 200px;
            overflow-y: auto;
            background: white;
            padding: 10px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        body {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }
        .content {
            margin-left: 260px;
            margin-top: 90px;
            padding: 20px;
            width: calc(100% - 260px);
        }
        .table-responsive {
            overflow-x: auto;
            width: 100%;
        }
    </style>

    <script>
function loadContent(page) {
    $("#content-panel").html("<h2>Loading...</h2>"); // Show loading text

    $.ajax({
        url: page, 
        method: "GET",
        success: function(data) {
            $("#content-panel").html(data); // Load new content into the panel
        },
        error: function(xhr, status, error) {
            $("#content-panel").html("<h2>Error loading content.</h2>");
            console.error("Error: ", status, error);
            console.error("Response: ", xhr.responseText);
        }
    });
}
</script>
</head>
<body>
<!-- Navigation Bar -->
<header>
    <div class="logo">
        <img src="images/logo1.jpg" alt="Logo">
    </div>
    <nav>
        <ul>
        <li><a href="home.html" onclick="loadContent('home.html')">Home</a></li>
            <li><a href="about.html" onclick="loadContent('about.html')">Know Us More</a></li>
            <li><a href="OurHostels.html" onclick="loadContent('OurHostels.html')">Our Hostels</a></li>
            <li><a href="Location.html" onclick="loadContent('Location.html')">Location</a></li>
            <li><a href="login.html">Log in</a></li>
                
    </ul>
    </nav>
    <a href="tel:7555342289" class="cta">Call: 7555342289</a>
</header>

<!-- Sidebar (Remains Fixed) -->
<div class="sidebar">
    <h4><center>Student Panel</center></h4>
    <a href="student-dashboard.php" onclick="loadContent('student-dashboard.php')">🏠 Dashboard</a>
    <a href="book-hostel.php" onclick="loadContent('book-hostel.php')">📅 Book Hostel</a>
    <a href="room-details.php" onclick="loadContent('room-details.php')">🛏 Room Details</a>
    <a href="my-profile.php" onclick="loadContent('my-profile.php')">👤 My Profile</a>
    <a href="logout.php">🚪 Logout</a> <!-- Logout should redirect completely -->
</div>


<!-- Main Content -->
<div class="content">
    <h2 class="text-center">My Profile</h2>
    
    <?php if (!empty($response_message)): ?>
        <div class="alert alert-info text-center"> <?php echo $response_message; ?> </div>
    <?php endif; ?>

    <div class="card p-4 mt-3">
       
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <tr><th>First Name</th><td><?php echo htmlspecialchars($student_name); ?></td></tr>
                <tr><th>Surname</th><td><?php echo htmlspecialchars($surname); ?></td></tr>
                <tr><th>Gender</th><td><?php echo htmlspecialchars($gender); ?></td></tr>
                <tr><th>Age</th><td><?php echo htmlspecialchars($age); ?></td></tr>
                <tr><th>College</th><td><?php echo htmlspecialchars($college); ?></td></tr>
                <tr><th>Course</th><td><?php echo htmlspecialchars($course); ?></td></tr>
                <tr><th>Address</th><td><?php echo htmlspecialchars($address); ?></td></tr>
                <tr><th>Email</th><td><?php echo htmlspecialchars($email); ?></td></tr>
                <tr><th>Contact</th><td><?php echo htmlspecialchars($contact); ?></td></tr>
                <tr><th>Blood Group</th><td><?php echo htmlspecialchars($blood_group); ?></td></tr>
                <tr><th>Father's Name</th><td><?php echo htmlspecialchars($father_name); ?></td></tr>
                <tr><th>Mother's Name</th><td><?php echo htmlspecialchars($mother_name); ?></td></tr>
                <tr><th>Father's Contact</th><td><?php echo htmlspecialchars($father_number); ?></td></tr>
                <tr><th>Room Number</th><td><?php echo htmlspecialchars($room_number); ?></td></tr>
            </table>
        </div>

        <h4 class="mt-4">Suggestions</h4>
        <form method="POST">
            <textarea class="form-control mb-2" name="suggestion_text" placeholder="Write your suggestion..." required></textarea>
            <button type="submit" class="btn btn-info">Submit Suggestion</button>
        </form>

        <h4 class="mt-4">Feedback</h4>
        <form method="POST">
            <textarea class="form-control mb-2" name="feedback_text" placeholder="Write your feedback..." required></textarea>
            <button type="submit" class="btn btn-warning">Submit Feedback</button>
        </form>
    </div>
</div>
</body>
</html>

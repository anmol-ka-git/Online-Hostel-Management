<?php
session_start();
include('connection.php'); // Ensure correct database connection

$student_id = $_SESSION['student_id'];

// Fetch student details (name, email, gender)
$query_student = "SELECT Student_name, Email, gender FROM student WHERE Sid = ?";
$stmt_student = $conn->prepare($query_student);
$stmt_student->bind_param("i", $student_id);
$stmt_student->execute();
$result_student = $stmt_student->get_result();
$student = $result_student->fetch_assoc();

$student_name = $student['Student_name'];
$email = $student['Email'];
$gender = $student['gender'];

// Assign hostel based on gender
$assigned_hostel = ($gender == "Male") ? "The Phoenix Wing" : "The Grace Wing";

// Fetch existing booking details (if any)
$query_booking = "SELECT start_date, end_date, mess_type, mess_cost FROM booking WHERE Student_id = ?";
$stmt_booking = $conn->prepare($query_booking);
$stmt_booking->bind_param("i", $student_id);
$stmt_booking->execute();
$result_booking = $stmt_booking->get_result();
$booking = $result_booking->fetch_assoc();

// Set default values if no previous booking exists
if ($booking) {
    $start_date = $booking['start_date'];
    $end_date = $booking['end_date'];
    $mess_type = $booking['mess_type'];
    $mess_cost = $booking['mess_cost'];
} else {
    $start_date = date('Y-m-d');
    $end_date = date('Y-m-d', strtotime('+6 months'));
    $mess_type = "None";
    $mess_cost = 0;
}

// Handle form submission for booking
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $mess_type = $_POST['mess_type'];

    // Assign mess cost based on selection
    $mess_cost = ($mess_type === "Veg") ? 8000 : (($mess_type === "Non-Veg") ? 10000 : 0);

    // Check if booking exists for this student
    $checkQuery = "SELECT bid FROM booking WHERE Student_id = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("i", $student_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        // Update existing booking
        $updateQuery = "UPDATE booking SET start_date = ?, end_date = ?, mess_type = ?, mess_cost = ? WHERE Student_id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("sssii", $start_date, $end_date, $mess_type, $mess_cost, $student_id);
        $updateStmt->execute();
    } else {
        // Insert new booking
        $insertQuery = "INSERT INTO booking (Student_id, start_date, end_date, mess_type, mess_cost) VALUES (?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("isssi", $student_id, $start_date, $end_date, $mess_type, $mess_cost);
        $insertStmt->execute();
    }
    if (!isset($_SESSION['mess_type'])) {
        $_SESSION['mess_type'] = isset($_POST['mess_type']) ? $_POST['mess_type'] : 'Not Selected';
    }
    if (!isset($_SESSION['mess_cost'])) {
        $_SESSION['mess_cost'] = isset($_POST['mess_cost']) ? $_POST['mess_cost'] : 0.00;
    }
    
    // Redirect to confirmation page or reload the page
    header("Location: room-details.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Hostel</title>
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
       
        .content {
            margin-left: 260px;
            margin-top: 90px;
            padding: 20px;
            width: calc(100% - 260px);
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .container {
            width: 50%;
            margin: 120px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px gray;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background: #333;
            color: white;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #28a745;
            color: white;
            border: none;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
        }
        button:hover {
         background: #218838;
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
    <h3><center>Student Panel</center></h3>
    <a href="student-dashboard.php" onclick="loadContent('student-dashboard.php')">🏠 Dashboard</a>
    <a href="book-hostel.php" onclick="loadContent('book-hostel.php')">📅 Book Hostel</a>
    <a href="room-details.php" onclick="loadContent('room-details.php')">🛏 Room Details</a>
    <a href="my-profile.php" onclick="loadContent('my-profile.php')">👤 My Profile</a>
    <a href="logout.php">🚪 Logout</a> <!-- Logout should redirect completely -->
</div>



<div class="container">
    <h2>Book Your Hostel</h2>
    <form method="POST" action="">
        <table>
            <tr>
                <th>Field</th>
                <th>Details</th>
            </tr>
            <tr>
                <td>Name:</td>
                <td><input type="text" name="student_name" value="<?php echo htmlspecialchars($student_name); ?>" readonly></td>
            </tr>
            <tr>
                <td>Email:</td>
                <td><input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" readonly></td>
            </tr>
            <tr>
                <td>Gender:</td>
                <td><input type="text" name="gender" value="<?php echo htmlspecialchars($gender); ?>" readonly></td>
            </tr>
            <tr>
                <td>Assigned Hostel:</td>
                <td><input type="text" name="hostel" value="<?php echo htmlspecialchars($assigned_hostel); ?>" readonly></td>
            </tr>
            <tr> 
                <td>Start Date:</td>
                <td><input type="date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>" required></td>
            </tr>
            <tr>
                <td>End Date:</td>
                <td><input type="date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>" required></td>
            </tr>
            <tr>
                <td>Mess Type:</td>
                <td>
                    <select name="mess_type" id="mess_type" onchange="updateMessCost()">
                        <option value="None" <?php if ($mess_type == "None") echo "selected"; ?>>None</option>
                        <option value="Veg" <?php if ($mess_type == "Veg") echo "selected"; ?>>Veg (₹8000)</option>
                        <option value="Non-Veg" <?php if ($mess_type == "Non-Veg") echo "selected"; ?>>Non-Veg (₹10000)</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Mess Cost:</td>
                <td><input type="text" name="mess_cost" id="mess_cost" value="<?php echo htmlspecialchars($mess_cost); ?>" readonly></td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center;">
                    <button type="submit">Save and Proceed Further</button>
                </td>
            </tr>
        </table>
    </form>
</div>

<script>
    function updateMessCost() {
        var messType = document.getElementById('mess_type').value;
        var messCost = 0;
        if (messType === 'Veg') {
            messCost = 8000;
        } else if (messType === 'Non-Veg') {
            messCost = 10000;
        }
        document.getElementById('mess_cost').value = messCost;
    }

    window.onload = function () {
        updateMessCost();
    };
</script>

</body>
</html>
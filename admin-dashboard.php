<?php
session_start();
include("connection.php"); // Database connection

// Fetch number of new feedback
$result = mysqli_query($conn, "SELECT COUNT(*) AS count FROM feedback");
$row = mysqli_fetch_assoc($result);
$new_feedback = isset($row['count']) ? $row['count'] : 0;

// Fetch number of new suggestions
$result = mysqli_query($conn, "SELECT COUNT(*) AS count FROM suggestions");
$row = mysqli_fetch_assoc($result);
$new_suggestions = isset($row['count']) ? $row['count'] : 0;

// Fetch total students
$result = mysqli_query($conn, "SELECT COUNT(*) AS count FROM student");
$row = mysqli_fetch_assoc($result);
$total_students = isset($row['count']) ? $row['count'] : 0;

// Fetch mess type distribution
$mess_query = mysqli_query($conn, "SELECT mess_type, COUNT(*) AS count FROM booking GROUP BY mess_type");
$mess_data = array();
while ($row = mysqli_fetch_assoc($mess_query)) {
    $mess_data[] = array($row['mess_type'], (int)$row['count']);
}

// Fetch room type distribution
$room_query = mysqli_query($conn, "SELECT room_type, COUNT(*) AS count FROM room GROUP BY room_type");
$room_data = array();
while ($row = mysqli_fetch_assoc($room_query)) {
    $room_data[] = array($row['room_type'], (int)$row['count']);
}

// Fetch students for dropdown
$students_query = mysqli_query($conn, "SELECT Sid, Student_name, surname FROM student");

// Student details fetching logic
$selected_student = null;

if (isset($_POST['view_student']) && !empty($_POST['student'])) {
    $student_id = mysqli_real_escape_string($conn, $_POST['student']);

    // Fetch student details
    $student_result = mysqli_query($conn, "SELECT * FROM student WHERE Sid = '$student_id'");
    if (mysqli_num_rows($student_result) > 0) {
        $selected_student = mysqli_fetch_assoc($student_result);
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!--<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
-->
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

        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
            overflow-x: hidden;
        }

        .content {
            margin-left: 240px;
            padding: 20px;
            width: calc(100% - 240px);
            margin-top: 90px;
            position: relative;
            z-index: 1;
        }
        
        
        .dashboard-cards {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        .card {
            width: 200px;
            padding: 15px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            text-align: center;
            border-radius: 10px;
            box-shadow: 2px 2px 10px rgba(0,0,0,0.1);
        }
        .card h3 { margin: 0; font-size: 18px; color: #333; }
        .card p { font-size: 22px; font-weight: bold; color: #007bff; }
        canvas { max-width: 400px; max-height: 800px; margin: 20px auto; }
        #messChart {
            height:600px;
            width:100%;
        }
        #roomChart{
            height:600px;
            width:100%;   
        }
 </style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
<div>
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
    <h3><center>Admin Panel</center></h3>
    <a href="admin-dashboard.php" onclick="loadContent('admin-dashboard.php')">🏠 Dashboard</a>
    <a href="manage-payments.php" onclick="loadContent('manage-payments.php')">💰 Manage Payments</a>
    <a href="manage-rooms.php" onclick="loadContent('manage-rooms.php')">🛏 Manage Rooms</a>
    <a href="reviews.php" onclick="loadContent('reviews.php')">📝 Reviews</a>
    <a href="logout.php">🚪 Logout</a> <!-- Logout should redirect completely -->
</div>

<!-- Main Content -->
<div class="content">
    <h3><center>Welcome Admin</center></h3>
    <br>
    <br>
    <!-- Cards Section -->
    <div class="dashboard-cards">
        <div class="card">
            <h3>Total Students</h3>
            <p><?php echo $total_students; ?></p>
        </div>
        <div class="card">
            <h3>New Feedbacks</h3>
            <p><?php echo $new_feedback; ?></p>
        </div>
        <div class="card">
            <h3>New Suggestions</h3>
            <p><?php echo $new_suggestions; ?></p>
        </div>
    </div>
<br>
<br>
    <h2>View Student Details</h2>
    <form method="POST">
        <select name="student">
            <option value="">Select Student</option>
            <?php while ($row = mysqli_fetch_assoc($students_query)) { ?>
                <option value="<?php echo $row['Sid']; ?>"><?php echo htmlspecialchars($row['Student_name'] . ' ' . $row['surname']); ?></option>
            <?php } ?>
        </select>
        <button type="submit" name="view_student">View Details</button>
    </form>

    <?php if ($selected_student) { ?>
        <h3>Student Information</h3>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($selected_student['Student_name'] . ' ' . $selected_student['surname']); ?></p>
        <p><strong>Gender:</strong> <?php echo htmlspecialchars($selected_student['gender']); ?></p>
        <p><strong>Age:</strong> <?php echo htmlspecialchars($selected_student['Age']); ?></p>
        <p><strong>College:</strong> <?php echo htmlspecialchars($selected_student['College']); ?></p>
        <p><strong>Course:</strong> <?php echo htmlspecialchars($selected_student['Course']); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($selected_student['Address']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($selected_student['Email']); ?></p>
        <p><strong>Contact:</strong> <?php echo htmlspecialchars($selected_student['Contact']); ?></p>
        <p><strong>Father's Name:</strong> <?php echo htmlspecialchars($selected_student['Father_name']); ?></p>
        <p><strong>Mother's Name:</strong> <?php echo htmlspecialchars($selected_student['Mother_name']); ?></p>
        <p><strong>Blood Group:</strong> <?php echo htmlspecialchars($selected_student['Blood_group']); ?></p>
        <p><strong>Father's Contact:</strong> <?php echo htmlspecialchars($selected_student['Father_number']); ?></p>
    <?php } ?>
<br>
<br>
    <!-- Charts Section -->
    <div class="chart-container">
            <canvas id="messChart"></canvas>
        </div>
        <div class="chart-container">
            <canvas id="roomChart"></canvas>
        </div>
    </div>

    <canvas id="messChart"></canvas>
<canvas id="roomChart"></canvas>

<script>
    var messData = {
        labels: <?php echo json_encode(array_map(function($data) { return $data[0]; }, $mess_data)); ?>,
        datasets: [{ 
            data: <?php echo json_encode(array_map(function($data) { return $data[1]; }, $mess_data)); ?>, 
            backgroundColor: ['#4CAF50', '#FF9800'] 
        }]
    };

    var roomData = {
        labels: <?php echo json_encode(array_map(function($data) { return $data[0]; }, $room_data)); ?>,
        datasets: [{ 
            label: 'Room Type Distribution', 
            data: <?php echo json_encode(array_map(function($data) { return $data[1]; }, $room_data)); ?>, 
            backgroundColor: ['#3F51B5', '#FFEB3B', '#E91E63'] 
        }]
    };

    new Chart(document.getElementById('messChart'), { type: 'pie', data: messData });
    new Chart(document.getElementById('roomChart'), { type: 'bar', data: roomData });
</script>

</body>
</html>

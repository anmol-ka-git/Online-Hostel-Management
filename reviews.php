<?php
include("connection.php");

// Fetch suggestions (Fixed column name)
$suggestions = mysqli_query($conn, "SELECT s.Student_name, sug.suggestion, sug.created_at FROM suggestions sug JOIN student s ON sug.student_id = s.Sid ORDER BY sug.created_at DESC");

// Fetch feedback (Fixed column name)
$feedback = mysqli_query($conn, "SELECT s.Student_name, f.feedback, f.created_at FROM feedback f JOIN student s ON f.student_id = s.Sid ORDER BY f.created_at DESC");

// Count feedback for graph
$feedbackCount = mysqli_num_rows($feedback);

if (!$suggestions) {
    die("Query Failed (Suggestions): " . mysqli_error($conn)); // Debugging
}
if (!$feedback) {
    die("Query Failed (Feedback): " . mysqli_error($conn)); // Debugging
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Reviews</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Chart.js for Graph -->

    
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

        
/* Sidebar */
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

/* Main Content Area */
.content {
    margin-left: 230px;/* Adjust based on sidebar width */
    padding: 20px;
    width: calc(100% - 230px);
    margin-top: 160px; /* Adjust for header */
}

/* Marquee Text Fix */
marquee {
    font-size: 20px;
    font-weight: bold;
    color: #007bff;
    display: block;
    padding: 10px;
    background: #f8f9fa;
    margin-left: 240px; /* Prevents overlapping sidebar */
    width: calc(100% - 240px);
    white-space: nowrap;
    margin-bottom:30px;
}

/* Table Styling */
.container {
    width: 95%;
    margin: 20px auto;
    overflow-x: auto;
    padding: 15px;
    background: white;
    border-radius: 8px;
    box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
}

table {
    width: 50%;
    border-collapse: collapse;
    margin:20px auto;
    font-size:14px;
    min-width: 800px;
    background: white;
    box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
}

th, td {
    border: 1px solid #ddd;
    padding: 15px;
    text-align: center;
    white-space: nowrap;
}

th {
    background-color: #f4f4f4;
}

/* Suggestion Table Fix */
.suggestion-table {
    width: 100%;
    margin-top: 20px;
    background: white;
    padding: 10px;
    border-radius: 8px;
    box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
}

/* Feedback & Graph Section */
.feedback-container {
    margin-top: 40px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.chart-container {
    margin-top: 30px;
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
    max-width: 700px; /* Reduce width */
    max-height: 400px; /* Restrict height */
    overflow: hidden; /* Prevents overflow */
    margin-left: auto;
    margin-right: auto;
}

canvas {
    max-width: 300px; /* Limits the width */
    max-height: 300px; /* Limits the height */
    width: 80% !important;  /* Responsive width */
    height: auto !important; /* Keeps aspect ratio */
    display: block; /* Avoids extra spacing */
    margin: 0 auto; /* Centers the chart */
}

/* Responsive Design */
@media (max-width: 1024px) {
    .sidebar {
        width: 180px;
    }
    .content, marquee {
        margin-left: 200px;
        width: calc(100% - 200px);
    }
}

@media (max-width: 768px) {
    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
    }
    .content, marquee {
        margin-left: 0;
        width: 100%;
    }
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

<!-- Navigation Bar -->
<header>
    <div class="logo">
        <img src="images/logo1.jpg" alt="Logo">
    </div>
    <nav>
        <ul>
            <li><a href="home.html" onclick="loadContent('home.html')">Home</a></li>
            <li><a href="about.html" onclick="loadContent('about.html')">About Us</a></li>
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


   <!-- Marquee for Suggestions -->
    <br>
    <br>
    <br>
    <br>
    <br>
<marquee>Suggestions from Students</marquee>
<table>
    <tr>
        <th>Student Name</th>
        <th>Suggestion</th>
        <th>Date</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($suggestions)) { ?>
        <tr>
            <td><?php echo htmlspecialchars($row['Student_name']); ?></td>
            <td><?php echo htmlspecialchars($row['suggestion']); ?></td>
            <td><?php echo $row['created_at']; ?></td>
        </tr>
    <?php } ?>
</table>

<br>

<!-- Marquee for Feedback -->
<marquee>Feedback from Students</marquee>
<table border="1">
    <tr>
        <th>Student Name</th>
        <th>Feedback</th>
        <th>Date</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($feedback)) { ?>
        <tr>
            <td><?php echo htmlspecialchars($row['Student_name']); ?></td>
            <td><?php echo htmlspecialchars($row['feedback']); ?></td>
            <td><?php echo $row['created_at']; ?></td>
        </tr>
    <?php } ?>
</table>

<br>

<!-- Graph for Feedback Analysis -->
<canvas id="feedbackRadialChart"></canvas>

<script>
    var ctx = document.getElementById('feedbackRadialChart').getContext('2d');
    var feedbackRadialChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [<?php echo $feedbackCount; ?>, 100 - <?php echo $feedbackCount; ?>], 
                backgroundColor: ['#36A2EB', '#E0E0E0'],
                borderWidth: 5
            }]
        },
        options: {
            responsive: true,
            cutout: '80%', // Makes it look like a progress circle
            plugins: {
                legend: { display: false },
                tooltip: { enabled: false }
            }
        }
    });
</script>


</body>
</html>

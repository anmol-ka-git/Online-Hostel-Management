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
        .sidebar h4 {
            text-align: center;
            padding-bottom: 15px;
            border-bottom: 1px solid #495057;
        }
        
        body {
            display: flex;
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
        }
        
        .content {
            margin-left: 250px;
            padding: 90px;
            width: 100%;
            margin-top:20px;
            font-size:18px;
            font-family:Calibri;
        }


.slider-container {
    overflow: hidden;
    position: relative;
    margin-top: 300px; /* Adds space below text */
    margin-right:280px;
    width: 350%; /* Adjust width as needed */
    max-width: 1500px; /* Increase max width */
    height: 400px; /* Increase height */
    margin-left: -140px;
}

.slider {
    display: flex;
    transition: transform 1s ease-in-out;
    height: 100%; /* Ensures the images scale properly */
    width:100%;
}

.slide {
    min-width: 100%;
    height: 100%;
}

.slide img {
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    object-fit: cover; /* Ensures images scale nicely */
    border-radius: 10px;
    /*width: 100%;
    height: 100%;
    object-fit: cover;*/
}

        .prev, .next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
        }
        .prev { left: 10px; }
        .next { right: 10px; }
        
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
        <h2 class="mb-3">Welcome,<?php echo htmlspecialchars($student_name); ?>!</h2>
        <p>To your second home.</p>
        <marquee behavior="scroll" direction="right" style="font-size: 1.2rem; font-weight: bold; margin-bottom: 20px; width:550%; font-family:times new roman;">
    Welcome to The Rest Nest! The place which makes your hostel life even more Comfortable.
</marquee>
    </div>
    <div class="slider-container">
        <div class="slider">
            <div class="slide"><img src="images/background2.jpg" alt="Hostel Room"></div>
            <div class="slide"><img src="images/abou4.jpg" alt="Dormitory"></div>
            <div class="slide"><img src="images/reception.jpeg" alt="Study Area"></div>
            <div class="slide"><img src="images/about1.jpg" alt="Study Area"></div>
            <div class="slide"><img src="images/studyroom.jpg" alt="Study Area"></div>
            <div class="slide"><img src="images/cateeen1.jpg" alt="Study Area"></div>
            <div class="slide"><img src="images/mess1.jpg" alt="Study Area"></div>
        </div>
        <button class="prev" onclick="moveSlide(-1)">&#10094;</button>
        <button class="next" onclick="moveSlide(1)">&#10095;</button>
    </div>

    <script>
        let index = 0;
        function moveSlide(step) {
            const slides = document.querySelectorAll('.slide');
            index = (index + step + slides.length) % slides.length;
            document.querySelector('.slider').style.transform = `translateX(${-index * 100}%)`;
        }
        setInterval(() => moveSlide(1), 3000);
    </script>
</body>
</html>

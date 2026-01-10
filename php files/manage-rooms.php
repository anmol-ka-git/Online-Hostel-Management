<?php
include("connection.php");

// Assign Room
if (isset($_POST['assign_room'])) {
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $room_number = mysqli_real_escape_string($conn, $_POST['room_number']);
    
    // Update room_number in all relevant tables
    //$updateRoom = mysqli_query($conn, "UPDATE room SET room_number='$room_number' WHERE rid IN (SELECT Room_number FROM student WHERE Sid='$student_id')");
    $updateRoom = mysqli_query($conn, 
    "UPDATE room 
    SET room_number = (SELECT Room_number FROM student WHERE Sid = '$student_id') 
    WHERE rid = (SELECT rid FROM room WHERE room_number IS NULL LIMIT 1)"
);

    $updateBooking = mysqli_query($conn, "UPDATE booking SET Room_number='$room_number' WHERE Student_id='$student_id'");
    $updateStudent = mysqli_query($conn, "UPDATE student SET Room_number='$room_number' WHERE Sid='$student_id'");
    
    if ($updateRoom && $updateBooking && $updateStudent) {
        echo "<script>alert('Room Number Assigned Successfully!'); window.location.href='manage-rooms.php';</script>";
    } else {
        echo "<script>alert('Error Assigning Room!');</script>";
    }
}

// Edit Room
if (isset($_POST['edit_room'])) {
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $room_number = mysqli_real_escape_string($conn, $_POST['room_number']);
    
    $updateRoom = mysqli_query($conn, "UPDATE room SET room_number='$room_number' WHERE rid IN (SELECT Room_number FROM student WHERE Sid='$student_id')");
    $updateBooking = mysqli_query($conn, "UPDATE booking SET Room_number='$room_number' WHERE Student_id='$student_id'");
    $updateStudent = mysqli_query($conn, "UPDATE student SET Room_number='$room_number' WHERE Sid='$student_id'");
    
    if ($updateRoom && $updateBooking && $updateStudent) {
        echo "<script>alert('Room Number Updated Successfully!'); window.location.href='manage-rooms.php';</script>";
    } else {
        echo "<script>alert('Error Updating Room!');</script>";
    }
}

// Delete Room Assignment
if (isset($_GET['delete_room'])) {
    $student_id = mysqli_real_escape_string($conn, $_GET['delete_room']);
    
    $deleteBooking = mysqli_query($conn, "UPDATE booking SET Room_number=NULL WHERE Student_id='$student_id'");
    $deleteStudent = mysqli_query($conn, "UPDATE student SET Room_number=NULL WHERE Sid='$student_id'");
    
    if ($deleteBooking && $deleteStudent) {
        echo "<script>alert('Room Assignment Removed!'); window.location.href='manage-rooms.php';</script>";
    } else {
        echo "<script>alert('Error Removing Room!');</script>";
    }
}

// Fetch Students and Room Assignments
$students = mysqli_query($conn, "SELECT Sid, Student_name, Room_number FROM student");
?>



<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rooms</title>
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

/* Main Content Area */
.content {
    margin-left: 230px;/* Adjust based on sidebar width */
    padding: 20px;
    width: calc(100% - 230px);
    margin-top: 160px; /* Adjust for header */
}


        /* Content Area */
        .content {
            margin-left: 260px;
            padding: 20px;
            width: calc(100% - 260px);
            margin-top: 100px; /* Push below navbar */
            justify-content: center;
            align-items: center;
            text-decoration: center;
        }

        table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        border: 1px solid black;
        padding: 10px;
        text-align: center;
    }
    th {
        background-color: #f2f2f2;
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

<!-- Sidebar -->
<!--<div class="sidebar">
<a href="admin-dashboard.php">🏠 Dashboard</a>
<a href="manage-payments.php">💰 Manage Payments</a>
<a href="manage-rooms.php">🛏 Manage Rooms</a>
<a href="reviews.php">📝 Reviews</a>
<a href="logout.php">🚪 Logout</a>
-->

<!-- Sidebar (Remains Fixed) -->
<div class="sidebar">
    <h3><center>Admin Panel</center></h3>
    <a href="admin-dashboard.php" onclick="loadContent('admin-dashboard.php')">🏠 Dashboard</a>
    <a href="manage-payments.php" onclick="loadContent('manage-payments.php')">💰 Manage Payments</a>
    <a href="manage-rooms.php" onclick="loadContent('manage-rooms.php')">🛏 Manage Rooms</a>
    <a href="reviews.php" onclick="loadContent('reviews.php')">📝 Reviews</a>
    <a href="logout.php">🚪 Logout</a> <!-- Logout should redirect completely -->
</div>

</div>
<div class="content">
<h2>Manage Room Assignments</h2>
<h2><center>Assigned Room</center></h2>
<table border="1">
        <tr>
            <th>Student ID</th>
            <th>Student Name</th>
            <th>Room Number</th>
            <th>Action</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($students)) { ?>
            <tr>
                <td><?php echo $row['Sid']; ?></td>
                <td><?php echo $row['Student_name']; ?></td>
                <td><?php echo $row['Room_number'] ? $row['Room_number'] : 'Not Assigned'; ?></td>
                <td>
                    <form method="post" style="display:inline-block;">
                        <input type="hidden" name="student_id" value="<?php echo $row['Sid']; ?>">
                        <input type="text" name="room_number" placeholder="Enter Room Number" required>
                        <button type="submit" name="assign_room">Assign</button>
                        <button type="submit" name="edit_room">Edit</button>
                    </form>
                    <a href="manage-rooms.php?delete_room=<?php echo $row['Sid']; ?>" onclick="return confirm('Are you sure?');">Delete</a>
                </td>
            </tr>
        <?php } ?>
    </table>
    </div>
</body>
</html>

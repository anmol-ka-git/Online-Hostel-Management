<?php
// Database Connection
$conn = mysqli_connect("localhost", "root", "", "hostel_management");
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Add Student
if (isset($_POST['add_student'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $query = "INSERT INTO student (Student_name, gender) VALUES ('$name', '$gender')";
    if (mysqli_query($conn, $query)) {
        echo "Student added successfully.";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// Assign Room
if (isset($_POST['assign_room'])) {
    $student_id = $_POST['student_id'];
    $room_id = $_POST['room_id'];

    // Fetch student gender
    $student_gender_query = mysqli_query($conn, "SELECT gender FROM student WHERE Sid = $student_id");
    $student_gender_row = mysqli_fetch_assoc($student_gender_query);
    $student_gender = $student_gender_row['gender'];

    // Ensure room is within the correct gender range
    $room_range = ($student_gender == 'Male') ? "500-600" : "800-900";
    $room_check = mysqli_query($conn, "SELECT * FROM room WHERE room_number = '$room_id' AND status = 'Available' AND room_number BETWEEN $room_range");

    if (mysqli_num_rows($room_check) > 0) {
        // Assign room to student
        $query = "UPDATE student SET Room_number = '$room_id' WHERE Sid = $student_id";
        if (mysqli_query($conn, $query)) {
            // Mark room as occupied
            mysqli_query($conn, "UPDATE room SET status = 'Occupied' WHERE room_number = '$room_id'");
            echo "Room assigned successfully.";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    } else {
        echo "Room is already occupied, does not exist, or is outside the allowed range.";
    }
}

// Fetch Students
$students = mysqli_query($conn, "SELECT * FROM student");

// Fetch Available Rooms based on Gender
$boys_rooms = mysqli_query($conn, "SELECT * FROM room WHERE status = 'Available' AND room_number BETWEEN 500 AND 600");
$girls_rooms = mysqli_query($conn, "SELECT * FROM room WHERE status = 'Available' AND room_number BETWEEN 800 AND 900");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
       <style>
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
        }

        .cta {
            background-color: orange;
            color: white;
            padding: 0.5rem 1rem;
            text-decoration: none;
            border-radius: 5px;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 80px; /* Push below navbar */
            width: 250px;
            height: calc(100% - 80px); /* Prevent overlap */
            background-color: #333;
            padding-top: 20px;
            color: white;
            z-index:1000;
            overflow-y: auto; /* Prevent content overflow */

        }

        .sidebar a {
            padding: 10px 15px;
            display: block;
            color: white;
            text-decoration: none;
        }

        .sidebar a:hover {
            background-color: #575757;
        }

        /* Content Area */
        .content {
            margin-left: 270px;
            padding: 20px;
            width: calc(100% - 270px);
            margin-top: 90px; /* Push below navbar */
            justify-content: center; /* Center horizontally */
    align-items: center;  /* Center vertically */
    min-height: 80vh; /* Ensure it takes enough height */
        }
</style>
</head>
<body>

<!-- Navigation Bar -->
<header>
    <div class="logo">
        <img src="images/logo1.jpg" alt="Logo">
    </div>
    <nav>
        <ul>
            <li><a href="home.html">Home</a></li>
            <li><a href="about.html">Know Us More</a></li>
            <li><a href="OurHostels.html">Our Hostels</a></li>
            <li><a href="Location.html">Location</a></li>
            <li><a href="login.html">Log in</a></li>
        </ul>
    </nav>
    <a href="tel:7555342289" class="cta">Call: 7555342289</a>
</header>

<!-- Sidebar -->
<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="admin-dashboard.php">Dashboard</a>
    
    <a href="manage-rooms.php">🛏 Manage Rooms</a>
    <a href="manage-payments.php">💰 Manage Payments</a>
    <a href="logout.php">🚪 Logout</a>
</div>
<div class="content">
    <h2>Manage Students</h2>

    <!-- Add Student Form -->
    <h3>Add New Student</h3>
    <form method="POST">
        <label>Name:</label>
        <input type="text" name="name" required>

        <label>Gender:</label>
        <select name="gender" required>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
        </select>

        <button type="submit" name="add_student">Add Student</button>
    </form>

    <!-- Assign Room Form -->
    <h3>Assign Room</h3>
    <form method="POST">
        <label>Student:</label>
        <select name="student_id" id="studentDropdown" required>
            <option value="">Select Student</option>
            <?php while ($student = mysqli_fetch_assoc($students)): ?>
                <option value="<?= $student['Sid']; ?>" data-gender="<?= $student['gender']; ?>">
                    <?= $student['Student_name']; ?> (<?= $student['gender']; ?>)
                </option>
            <?php endwhile; ?>
        </select>

        <label>Room:</label>
        <select name="room_id" id="roomDropdown" required>
            <option value="">Select Room</option>
        </select>

        <button type="submit" name="assign_room">Assign</button>
    </form>

    <!-- All Students Table -->
    <h3>All Students</h3>
    <table border="1" width="80%">
        <tr>
            <th>Name</th>
            <th>Gender</th>
            <th>Room Assigned</th>
        </tr>
        <?php
        $students = mysqli_query($conn, "SELECT Student_name, gender, Room_number FROM student");
        while ($row = mysqli_fetch_assoc($students)): ?>
            <tr>
                <td><?= $row['Student_name']; ?></td>
                <td><?= $row['gender']; ?></td>
                <td><?= ($row['Room_number']) ? $row['Room_number'] : 'Not Assigned'; ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<!-- JavaScript for Dynamic Room Selection -->
<script>
document.getElementById('studentDropdown').addEventListener('change', function() {
    var gender = this.options[this.selectedIndex].getAttribute('data-gender');
    var roomDropdown = document.getElementById('roomDropdown');
    
    // Clear existing options
    roomDropdown.innerHTML = '<option value="">Select Room</option>';

    // Fetch available rooms dynamically
    var rooms = gender === 'Male' ? <?= json_encode(mysqli_fetch_all($boys_rooms, MYSQLI_ASSOC)); ?> 
                                   : <?= json_encode(mysqli_fetch_all($girls_rooms, MYSQLI_ASSOC)); ?>;

    rooms.forEach(function(room) {
        var option = document.createElement('option');
        option.value = room.room_number;
        option.textContent = "Room " + room.room_number;
        roomDropdown.appendChild(option);
    });
});
</script>
</body>
</html>
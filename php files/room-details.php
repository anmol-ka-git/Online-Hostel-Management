<?php
session_start();
include('connection.php');

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Fetch Student Details
$query = "SELECT Student_name, gender FROM student WHERE Sid = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$student_name = $row['Student_name'];
$student_gender = $row['gender'];

// Assign hostel ID based on gender
$hostel_id = ($student_gender == 'Male') ? 1 : 2; 

// Fetch Mess Details from booking table
$query = "SELECT mess_type, mess_cost, bid FROM booking WHERE Student_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

$mess_type = isset($booking['mess_type']) ? $booking['mess_type'] : 'Not Selected';
$mess_cost = isset($booking['mess_cost']) ? $booking['mess_cost'] : 0.00;
$bid = isset($booking['bid']) ? $booking['bid'] : NULL;

if (!$bid) {
    die("Error: Booking ID not found for Student ID: $student_id");
}

// Check if student already has a room entry
$query = "SELECT rid, room_type, price FROM room WHERE bid = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $bid);
$stmt->execute();
$result = $stmt->get_result();
$booked_room = $result->fetch_assoc();

$selected_room_type = isset($booked_room['room_type']) ? $booked_room['room_type'] : '';
$selected_room_price = isset($booked_room['price']) ? $booked_room['price'] : 0.00;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selected_room_type = $_POST['selected_room_type'];
    $selected_room_price = $_POST['selected_room_price'];

    // Assign room values based on selected type
    switch ($selected_room_type) {
        case 'Single Bed':
            $no_of_beds = 1;
            $washroom_available = 'No';
            break;
        case 'Twin':
            $no_of_beds = 2;
            $washroom_available = 'No';
            break;
        case '2 BHK':
            $no_of_beds = 4;
            $washroom_available = 'Yes';
            break;
        default:
            die("Invalid room type selected!");
    }

    // Determine booking status based on button clicked
    $status = isset($_POST['save_pay_now']) ? 'occupied' : 'booked';

    if (!$booked_room) {
        // No existing room entry, insert a new one
        $query = "INSERT INTO room (hostel_id, room_number, room_type, no_of_beds, Washroom_Available, price, status, bid) 
                  VALUES (?, NULL, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isisssi", $hostel_id, $selected_room_type, $no_of_beds, $washroom_available, $selected_room_price, $status, $bid);
    } else {
        // Room entry already exists, update it
        $query = "UPDATE room SET room_type = ?, no_of_beds = ?, Washroom_Available = ?, price = ?, status = ? WHERE bid = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sisssi", $selected_room_type, $no_of_beds, $washroom_available, $selected_room_price, $status, $bid);
    }

    if ($stmt->execute()) {
        echo "Room details updated successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Redirect based on button clicked
    if (isset($_POST['save_pay_now'])) {
        $_SESSION['room_type'] = $selected_room_type;
        $_SESSION['room_price'] = $selected_room_price;
        $_SESSION['booking_id'] = $student_id;
        header("Location: payment.php");
        exit();
    } 
    elseif (isset($_POST['save_pay_later'])) {
        echo "<script>alert('Room booked successfully! Please complete the payment within 15 days to confirm your stay.'); window.location.href='my-profile.php';</script>";
    }
                  
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Details</title>
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
        /* Ensure container is well-aligned */
.container {
    width: 60%;
    max-width: 800px;
    margin: 100px auto; /* Adjusted to avoid overlapping with fixed navbar */
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.2);
    text-align: center;
}

/* Table layout improvements */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

th, td {
    padding: 12px;
    border: 1px solid #ddd;
    text-align: left;
}

th {
    background-color: #f2f2f2;
    font-weight: bold;
}
/* New styles for buttons */
.button-container {
            text-align: center;
            margin-top: 20px;
        }
        .button-container button {
            background-color: green;
            color: white;
            font-size: 18px;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 200px;
            margin: 10px;
            transition: background-color 0.3s ease;
        }
        .button-container button:hover {
            background-color: darkgreen;
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
    <h1>Your Room Booking Details</h1>
    <table>
        <tr>
            <td><strong>Name:</strong></td>
            <td><?php echo htmlspecialchars($student_name); ?></td>
        </tr>
        <tr>
            <td><strong>Room Type:</strong></td>
            <td>
                <select id="room_type" name="room_type" onchange="updateRoomPrice()">
                    <option value="">Select Room Type</option>
                    <option value="Single Bed" data-price="12000" <?php echo ($selected_room_type == 'Single Bed') ? 'selected' : ''; ?>>Single Bed</option>
                    <option value="Twin" data-price="8000" <?php echo ($selected_room_type == 'Twin') ? 'selected' : ''; ?>>Twin</option>
                    <option value="2 BHK" data-price="20000" <?php echo ($selected_room_type == '2 BHK') ? 'selected' : ''; ?>>2 BHK</option>
                </select>
            </td>
        </tr>
        <tr>
            <td><strong>Room Cost:</strong></td>
            <td>₹<span id="room_cost"><?php echo number_format((float)$selected_room_price, 2); ?></span></td>
        </tr>
        <tr>
            <td><strong>Mess Type:</strong></td>
            <td><?php echo htmlspecialchars($mess_type); ?></td>
        </tr>
        <tr>
            <td><strong>Mess Cost:</strong></td>
            <td>₹<?php echo number_format((float)$mess_cost, 2); ?></td>
        </tr>
    </table>
    <div class="button-container">
    <form method="POST" action="room-details.php">
    <input type="hidden" name="selected_room_type" id="selected_room_type" value="<?php echo htmlspecialchars($selected_room_type); ?>">
    <input type="hidden" name="selected_room_price" id="selected_room_price" value="<?php echo htmlspecialchars($selected_room_price); ?>">
    <input type="hidden" name="mess_type" value="<?php echo htmlspecialchars($mess_type); ?>">
    <input type="hidden" name="mess_cost" value="<?php echo htmlspecialchars($mess_cost); ?>">

    <!-- Save & Pay Now Button -->
    <button type="submit" name="save_pay_now">Save & Pay Now</button>

    <!-- Save & Pay Later Button -->
    <button type="submit" name="save_pay_later">Save & Pay Later</button>
</form>

    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    updateRoomPrice();
});

function updateRoomPrice() {
    let roomTypeDropdown = document.getElementById("room_type");
    let selectedOption = roomTypeDropdown.options[roomTypeDropdown.selectedIndex];
    let selectedPrice = selectedOption.getAttribute("data-price") || "0.00";

    document.getElementById("room_cost").innerText = selectedPrice;
    document.getElementById("selected_room_price").value = selectedPrice;
    document.getElementById("selected_room_type").value = selectedOption.value;
}
</script>
</body>
</html>

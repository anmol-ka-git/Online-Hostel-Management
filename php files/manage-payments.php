<?php
include("connection.php"); // Ensure database connection

// DELETE Payment Record (if requested)
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    $stmt = $conn->prepare("DELETE FROM payment WHERE pid = ?");
    $stmt->bind_param("i", $delete_id);
    
    if ($stmt->execute()) {
        echo "<script>alert('Payment record deleted successfully!'); window.location.href='manage-payments.php';</script>";
    } else {
        echo "<script>alert('Error deleting record: " . mysqli_error($conn) . "');</script>";
    }
    $stmt->close();
}

// Fetch all payments (both successful and pending)
$query_all = "SELECT 
    p.pid AS Payment_ID, 
    COALESCE(s.Student_name, 'N/A') AS Student,
    COALESCE(s.gender, 'N/A') AS Gender,
    COALESCE(r.room_number, 'N/A') AS Room_No,
    p.amount AS Amount,
    p.method AS Payment_Method,
    p.status AS Status,
    p.date AS Payment_Date
FROM payment p
LEFT JOIN booking b ON p.bid = b.bid
LEFT JOIN student s ON b.Student_id = s.Sid
LEFT JOIN room r ON b.Room_number = r.room_number
WHERE p.status IN ('pending', 'successful') -- Only show pending or successful payments
ORDER BY p.date DESC"; // Sort by latest payments first

$result_all = $conn->query($query_all);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Payments</title>
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

        .content {
            margin-left: 240px;
            padding: 20px;
            width: calc(100% - 240px);
            margin-top: 90px;
        }

        /* Table Styling */
        .container {
            width: 95%;
            margin: 20px auto;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            min-width: 800px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
            white-space: nowrap;
        }
        th {
            background-color: #f4f4f4;
        }
        .delete-btn {
            background-color: red;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
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

</div>

<!-- Main Content -->
<div class="content">
    <h2>Manage Payments</h2>
    <div class="container">
        <table>
            <tr>
                <th>Payment ID</th>
                <th>Student</th>
                <th>Gender</th>
                <th>Room No.</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Status</th>
                <th>Payment Date</th>
                <th>Action</th>
            </tr>
            <?php
            if ($result_all->num_rows > 0) {
                while ($row = $result_all->fetch_assoc()) {
                    echo "<tr>
                        <td>{$row['Payment_ID']}</td>
                        <td>{$row['Student']}</td>
                        <td>{$row['Gender']}</td>
                        <td>{$row['Room_No']}</td>
                        <td>{$row['Amount']}</td>
                        <td>{$row['Payment_Method']}</td>
                        <td style='color:" . ($row['Status'] == 'pending' ? 'red' : 'green') . "; font-weight:bold;'>{$row['Status']}</td>
                        <td>{$row['Payment_Date']}</td>
                        <td><a href='manage-payments.php?delete_id={$row['Payment_ID']}' onclick='return confirm(\"Are you sure you want to delete this payment?\")'>Delete</a></td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='9'>No payment records found.</td></tr>";
            }
            ?>
        </table>
    </div>
</div>

</body>
</html>
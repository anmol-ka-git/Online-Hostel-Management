<?php
include("connection.php");

if (isset($_GET['pid'])) {
    $pid = intval($_GET['pid']); // Ensure pid is an integer

    // Check if the payment exists
    $checkQuery = "SELECT * FROM payment WHERE pid = $pid";
    $checkResult = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        // Delete the payment
        $deleteQuery = "DELETE FROM payment WHERE pid = $pid";
        if (mysqli_query($conn, $deleteQuery)) {
            echo "<script>alert('Payment deleted successfully!'); window.location.href='manage-payments.php';</script>";
        } else {
            echo "<script>alert('Error deleting payment!'); window.location.href='manage-payments.php';</script>";
        }
    } else {
        echo "<script>alert('Payment not found!'); window.location.href='manage-payments.php';</script>";
    }
} else {
    echo "<script>alert('Invalid request!'); window.location.href='manage-payments.php';</script>";
}
?>

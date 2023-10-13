<?php
session_start();
require "connection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["booking_id"]) && isset($_POST["accommodation"])) {
        $bookingId = $_POST["booking_id"];
        $accommodation = $_POST["accommodation"];

        // Update the status in the database
        $updateSql = "UPDATE bookings SET status = '$accommodation' WHERE id = '$bookingId'";
        if (mysqli_query($con, $updateSql)) {
            header('location: adminSRhistory.php');
        } else {
            echo "Error updating status: " . mysqli_error($con);
        }
    }
}
?>

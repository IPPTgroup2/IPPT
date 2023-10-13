<?php
session_start();
require "connection.php";

function deleteRecordFromCSV($facultynumber) {
  $csvFile = 'try.csv';
  $lines = file($csvFile);
  $output = [];
  $removed = false; // Flag to check if a record was removed

  foreach ($lines as $line) {
      $data = str_getcsv($line);
      if ($data[1] != $facultynumber) {
          $output[] = $line;
      } else {
          $removed = true;
      }
  }

  if ($removed) {
      file_put_contents($csvFile, implode("\n", $output));
      $lines = file($csvFile); 
      file_put_contents($csvFile, implode("\n", $lines));
  }
}


if (isset($_GET['facultynumber'])) {
    $fname = $_GET['fname'];
    $lname = $_GET['lname'];
    $facultynumber = $_GET['facultynumber'];

    // Delete the selected person from the database
    $deleteSql = "DELETE FROM personnel WHERE fname = '$fname' AND lname = '$lname' AND facultynumber='$facultynumber'";
    $deleteResult = $con->query($deleteSql);

    if ($deleteResult) {
        // Delete records from other tables and CSV file
        $deleteAttendanceSql = "DELETE FROM attendance2 WHERE facultynumber = '$facultynumber'";
        $deleteBookingsSql = "DELETE FROM bookings WHERE facultynumber = '$facultynumber'";

        $con->query($deleteAttendanceSql);
        $con->query($deleteBookingsSql);

        deleteRecordFromCSV($facultynumber);

        echo "Personnel with Faculty Number $facultynumber has been removed successfully.";
        header('Location: adminpersonnelmanagement.php');
    } else {
        echo "Failed to remove personnel.";
        header('Location: adminpersonnelmanagement.php');
    }
} else {
    echo "Invalid request. Faculty number not provided.";
    header('Location: adminpersonnelmanagement.php');
}
?>

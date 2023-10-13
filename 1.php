<?php
session_start();
require('connection.php');
require('fpdf/fpdf.php');

// Retrieve parameters from the URL
$name = urldecode($_GET['name']);
$filter = urldecode($_GET['filter']);
$date = urldecode($_GET['date']);

// Function to read CSV data
function readCSV($csvFile)
{
    $data = array();

    if (($handle = fopen($csvFile, "r")) !== FALSE) {
        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $data[] = $row;
        }

        fclose($handle);
    }

    return $data;
}

// Read the CSV file
$csvData = readCSV('attendance.csv');

// Filter data based on parameters
$filteredData = array();

foreach ($csvData as $row) {
    $rowName = $row[0];
    $rowDate = date('Y-m-d', strtotime($row[3])); // Assuming date is in the third column

    if ($rowName === $name) {
        if ($filter === 'today' && $rowDate === date('Y-m-d')) {
            $filteredData[] = $row;
        } elseif ($filter === 'daily' && $rowDate === $date) {
            $filteredData[] = $row;
        } elseif ($filter === 'weekly' && strtotime($date) <= strtotime($rowDate) && strtotime($rowDate) <= strtotime("+2 days, -9 hours", strtotime($date))) {
            $filteredData[] = $row;
        } elseif ($filter === 'monthly' && date('Y-m', strtotime($date)) === date('Y-m', strtotime($rowDate))) {
            $filteredData[] = $row;
        }
    }
}

// Display the filtered records
if (!empty($filteredData)) {
    echo '<h1>Attendance Records</h1>';
    echo '<table border="1">';
    echo '<tr><th>Name</th><th>Filter</th><th>Date</th></tr>';

    foreach ($filteredData as $row) {
        echo '<tr>';
        echo '<td>' . $row[0] . '</td>';
        echo '<td>' . $row[3] . '</td>';
        echo '<td>' . $row[4] . '</td>';
        echo '</tr>';
    }

    echo '</table>';
} else {
    echo 'No matching records found.';
}

// Close the connection
$con->close();
?>

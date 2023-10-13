<?php
session_start();
require('connection.php');
require('vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$schoolName = 'Your School Name';
$schoolLogo = 'img/pup.png';

if (isset($_GET['report-type']) && isset($_GET['specific-date'])) {
    $reportType = $_GET['report-type'];
    $specificDate = mysqli_real_escape_string($con, $_GET['specific-date']);

    $whereCondition = '';
    $date = '';

    if ($reportType === 'daily') {
        $whereCondition = "DATE(date_request) = '$specificDate'";
        $date = $specificDate;
    } elseif ($reportType === 'weekly') {
        $endDate = date('Y-m-d', strtotime($specificDate . ' +6 days'));
        $whereCondition = "date_request >= '$specificDate' AND date_request <= '$endDate'";
        $date = "From $specificDate to $endDate";
    } elseif ($reportType === 'monthly') {
        $startDate = date('Y-m-01', strtotime($specificDate));
        $endDate = date('Y-m-t', strtotime($specificDate));
        $whereCondition = "date_request >= '$startDate' AND date_request <= '$endDate'";
        $date = "Month of " . date('F Y', strtotime($specificDate));
    } elseif ($reportType === 'today') {
        $today = date('Y-m-d H:i:s', strtotime('-9 hours'));
        $whereCondition .= "AND date_request >= '$today'";
        $date = "Today";
    } elseif ($reportType === 'all') {
        // No additional constraints needed for "All" filter
    } else {
        echo "Invalid report type.";
        exit;
    }

    $sql = "SELECT * FROM bookings";
    if (!empty($whereCondition)) {
        $sql .= " WHERE $whereCondition";
    }
    $sql .= " ORDER BY name, date_request";

    $result = mysqli_query($con, $sql);

    if ($result) {
        if (mysqli_num_rows($result) > 0) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            $sheet->setCellValue('A1', 'Office');
            $sheet->setCellValue('B1', 'Personnel');
            $sheet->setCellValue('C1', 'Request By');
            $sheet->setCellValue('D1', 'Purpose');
            $sheet->setCellValue('E1', 'Email');
            $sheet->setCellValue('F1', 'Date');
            $sheet->setCellValue('G1', 'Time');
            $sheet->setCellValue('H1', 'Status');
            $sheet->setCellValue('I1', 'Reason');

            $rowIndex = 2;

while ($row = mysqli_fetch_assoc($result)) {
    $sheet->setCellValue('A' . $rowIndex, $row['office']);
    $sheet->setCellValue('B' . $rowIndex, $row['name']);
    $sheet->setCellValue('C' . $rowIndex, $row['booked_by']);
    $sheet->setCellValue('D' . $rowIndex, $row['purpose']);
    $sheet->setCellValue('E' . $rowIndex, $row['email']);
    $sheet->setCellValue('F' . $rowIndex, $row['date']);
    $sheet->setCellValue('G' . $rowIndex, $row['time']);
    $sheet->setCellValue('H' . $rowIndex, $row['status']);
    $sheet->setCellValue('I' . $rowIndex, $row['reason']);
    $rowIndex++;
}


            $writer = new Xlsx($spreadsheet);
            $writer->save('Service_Request_Report.xlsx');

            header('Location: Service_Request_Report.xlsx');

            mysqli_free_result($result);
        } else {
            echo "No records found.";
        }

        mysqli_close($con);
    } else {
        echo "Query error: " . mysqli_error($con);
    }
} else {
    echo "Invalid request.";
}
?>

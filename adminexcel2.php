<?php
session_start();
require('connection.php');
require('vendor/autoload.php'); // Include the autoload file for PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$schoolName = 'Your School Name';
$schoolLogo = 'img/pup.png';

if (isset($_GET['office']) && isset($_GET['report-type']) && isset($_GET['specific-date'])) {
    $office = mysqli_real_escape_string($con, $_GET['office']);
    $reportType = $_GET['report-type'];
    $specificDate = mysqli_real_escape_string($con, $_GET['specific-date']);

    // Initialize WHERE condition and date
    $whereCondition = "office = '$office'";
    $date = '';
    
    // Construct the query based on report type and specific date
    if ($reportType === 'daily') {
      $whereCondition .= " AND DATE(date_request) = '$specificDate'";
      $date = $specificDate;
  } elseif ($reportType === 'weekly') {
      $endDate = date('Y-m-d', strtotime($specificDate . ' +6 days'));
      $whereCondition .= " AND date_request >= '$specificDate' AND date_request <= '$endDate'";
      $date = "From $specificDate to $endDate";
  } elseif ($reportType === 'monthly') {
      $startDate = date('Y-m-01', strtotime($specificDate));
      $endDate = date('Y-m-t', strtotime($specificDate));
      $whereCondition .= " AND date_request >= '$startDate' AND date_request <= '$endDate'";
      $date = "Month of " . date('F Y', strtotime($specificDate));
  } elseif ($reportType === 'today') {
      $today = date('Y-m-d H:i:s', strtotime('-9 hours'));
      $whereCondition .= "AND date_request >= '$today'";
      $date = "Today";
  } elseif ($reportType === 'all') {

  }else {
      echo "Invalid report type.";
      exit;
  }
   // Fetch data for the selected personnel and criteria
 $sql = "SELECT * FROM bookings WHERE $whereCondition ORDER BY date_request";
 $result = mysqli_query($con, $sql);

 if (mysqli_num_rows($result) > 0) {
  $spreadsheet = new Spreadsheet();
  $sheet = $spreadsheet->getActiveSheet();

  // Set headers
  $sheet->setCellValue('A1', 'Office');
  $sheet->setCellValue('B1', 'Personnel');
  $sheet->setCellValue('C1', 'Request By');
  $sheet->setCellValue('D1', 'Purpose');
  $sheet->setCellValue('E1', 'Email');
  $sheet->setCellValue('F1', 'Date');
  $sheet->setCellValue('G1', 'Time');
  $sheet->setCellValue('H1', 'Status');
  $sheet->setCellValue('I1', 'Reason');

  $rowNumber = 2; // Initialize row counter

  while ($row = mysqli_fetch_assoc($result)) {
      $sheet->setCellValue('A' . $rowNumber, $row['office']);
      $sheet->setCellValue('B' . $rowNumber, $row['name']);
      $sheet->setCellValue('C' . $rowNumber, $row['booked_by']);
      $sheet->setCellValue('D' . $rowNumber, $row['purpose']);
      $sheet->setCellValue('E' . $rowNumber, $row['email']);
      $sheet->setCellValue('F' . $rowNumber, $row['date']);
      $sheet->setCellValue('G' . $rowNumber, $row['time']);
      $sheet->setCellValue('H' . $rowNumber, $row['status']);
      $sheet->setCellValue('I' . $rowNumber, $row['reason']);
      
      $rowNumber++; // Increment row counter
  }

  $filename = "Service_Request_Report_$office.xlsx";
  $writer = new Xlsx($spreadsheet);
  $writer->save($filename);

  header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
  header("Content-Disposition: attachment; filename=$filename");
  readfile($filename);

  unlink($filename);
} else {
  echo "No records found.";
}

mysqli_free_result($result);
mysqli_close($con);
} else {
echo "Invalid request.";
}
?>

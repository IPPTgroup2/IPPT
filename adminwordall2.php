<?php
session_start();
require('connection.php');
require('vendor/autoload.php'); // Include the autoload file for PHPWord

$schoolName = 'Polytechnic University of the Philippines - Ragay Branch';
$schoolLogo = 'img/pup.png';
$region = 'Region V';
$place = 'Republic of the Philippines';

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
          $phpWord = new \PhpOffice\PhpWord\PhpWord();
          $section = $phpWord->addSection(array('orientation' => 'landscape'));
          
          // Create a table for the header
          $headerTable = $section->addTable();
          $headerTable->addRow();
          $headerTable->addCell(3000)->addImage($schoolLogo, array('width' => 50, 'height' => 50));
          $headerTable->addCell(3000)->addText("$place\n$region\n$schoolName");
          
          // Add spacing after the header
          $section->addTextBreak(1);
  
          $prevOffice = null;
          $prevName = null;
  
          while ($row = mysqli_fetch_assoc($result)) {
              $currentOffice = $row['office'];
              $currentName = $row['name'];
  
              if ($currentOffice !== $prevOffice || $currentName !== $prevName) {
                  if ($prevOffice !== null) {
                      $section->addTextBreak(1);
                  }
  
                  $section->addText($currentOffice, array('bold' => true));
  
                  $table = $section->addTable();
                  $table->addRow();
                  $table->addCell(2000)->addText('Request Date', array('bold' => true));
                  $table->addCell(2000)->addText('Request By', array('bold' => true));
                  $table->addCell(2000)->addText('Purpose', array('bold' => true));
                  $table->addCell(2000)->addText('Email', array('bold' => true));
                  $table->addCell(2000)->addText('Date', array('bold' => true));
                  $table->addCell(2000)->addText('Time', array('bold' => true));
                  $table->addCell(2000)->addText('Status', array('bold' => true));
                  $table->addCell(2000)->addText('Reason', array('bold' => true));
  
                  $prevOffice = $currentOffice;
                  $prevName = $currentName;
              }
  
              $table->addRow();
              $table->addCell(2000)->addText($row['date_request']);
              $table->addCell(2000)->addText($row['booked_by']);
              $table->addCell(2000)->addText($row['purpose']);
              $table->addCell(2000)->addText($row['email']);
              $table->addCell(2000)->addText($row['date']);
              $table->addCell(2000)->addText($row['time']);
              $table->addCell(2000)->addText($row['status']);
              $table->addCell(2000)->addText($row['reason']);
          }
  
          $fileName = "Service_Request_Report.docx";
          $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
          $objWriter->save($fileName);
  
          header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
          header("Content-Disposition: attachment; filename=$fileName");
          readfile($fileName);
  
          unlink($fileName);
      } else {
          echo "No records found.";
      }
  
      mysqli_free_result($result);
  } else {
      echo "Query error: " . mysqli_error($con);
  }
  
  mysqli_close($con);
} else {
    echo "Invalid request.";
}
?>

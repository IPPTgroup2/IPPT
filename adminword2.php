<?php
session_start();
require('connection.php');
require('vendor/autoload.php'); // Include the autoload file for PHPWord

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

$school = 'Polytechnic University of the Philippines - Ragay Branch';
$schoolLogo = 'img/pup.png';
$region = 'Region V';
$place = 'Republic of the Philippines';

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

    } else {
        echo "Invalid report type.";
        exit;
    }

    // Fetch data for the selected personnel and criteria
    $sql = "SELECT * FROM bookings WHERE $whereCondition ORDER BY date_request";
    $result = mysqli_query($con, $sql);

    if (mysqli_num_rows($result) > 0) {
        $phpWord = new PhpWord();

        $sectionHeader = $phpWord->addSection();
        $table = $sectionHeader->addTable();
        $table->addRow();
        $table->addCell(2000)->addImage($schoolLogo, array('width' => 70, 'height' => 70));
        $infoCell = $table->addCell(8000);
        $infoCell->addText($place, array('bold' => true, 'size' => 12), array('alignment' => 'left'));
        $infoCell->addText($region, array('bold' => true, 'size' => 12), array('alignment' => 'left'));
        $infoCell->addText($school, array('bold' => true, 'size' => 12), array('alignment' => 'left'));
        $sectionHeader->addText("Service Requests for $office - ($reportType)", array('bold' => true, 'size' => 14));

        $prevDate = null;

        while ($row = mysqli_fetch_assoc($result)) {
            $currentDate = $row['date_request'];
            $name = $row['name'];

            // Check if the date has changed
            if ($currentDate !== $prevDate) {
                if ($prevDate !== null) {
                    $sectionHeader->addTextBreak(1);
                }

                $sectionHeader->addText("Personnel: $name", array('size' => 10));
                $sectionHeader->addText("Date Request: $currentDate", array('size' => 10));

                // Create a table
                $table = $sectionHeader->addTable();
                $table->addRow();
                $table->addCell(6000)->addText('Request By');
                $table->addCell(6000)->addText('Purpose');
                $table->addCell(6000)->addText('Email');
                $table->addCell(3000)->addText('Date');
                $table->addCell(3000)->addText('Time');
                $table->addCell(4200)->addText('Status');
                $table->addCell(4000)->addText('Reason');

                $prevDate = $currentDate;
            }

            $table->addRow();
            $table->addCell(6000)->addText($row['booked_by']);
            $table->addCell(6000)->addText($row['purpose']);
            $table->addCell(6000)->addText($row['email']);
            $table->addCell(3000)->addText($row['date']);
            $table->addCell(3000)->addText($row['time']);
            $table->addCell(4200)->addText($row['status']);
            $table->addCell(4000)->addText($row['reason']);
        }

        $filename = "Service_Request_Report_$office.docx";
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($filename);

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
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

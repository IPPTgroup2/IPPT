<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Define the path to your CSV file
$csvFile = 'try.csv'; // Update with the actual path to your CSV file

if (isset($_GET['filter']) && isset($_GET['date'])) {
    $filterOption = urldecode($_GET['filter']);
    $attendanceDate = urldecode($_GET['date']);

    // Load all attendance data
    $allAttendanceData = loadAllAttendanceData($csvFile);

    // Filter data based on the specified filter
    $filteredData = filterAttendanceData($allAttendanceData, $filterOption, $attendanceDate);

    if (!empty($filteredData)) {
        // Generate the Excel content and output it for download
        generateAttendanceReportExcel($filteredData, $filterOption);
    } else {
        echo "No attendance records found for the selected filter.";
    }
} else {
    // Handle invalid request
    echo "Invalid request.";
}

// Function to generate the attendance report Excel file
function generateAttendanceReportExcel($filteredData, $filterOption) {
    $spreadsheet = new Spreadsheet();

    $worksheet = $spreadsheet->getActiveSheet();
    $rowIndex = 2;

    foreach ($filteredData as $name => $records) {
        $worksheet->setCellValue("A$rowIndex", $name);
        $rowIndex++;

        // Calculate and display daily total working hours
        calculateAndDisplayDailyTotalHoursExcel($worksheet, $records, $rowIndex);
        $rowIndex += count($records) * 2 + 1; // Increment the row index for the next name
    }

    // Output Excel file to download
    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $excelName = "attendance_report_$filterOption.xlsx";
    header("Content-Disposition: attachment; filename=\"$excelName\"");
    $writer->save('php://output');
}

// Function to calculate and display daily total working hours in Excel file
function calculateAndDisplayDailyTotalHoursExcel($worksheet, $records, $rowIndex) {
    $dailyTotalHours = array();
    $previousTime = null; // Move this line here

    foreach ($records as $record) {
        $recordDate = date('Y-m-d', strtotime($record[3]));

        if (!isset($dailyTotalHours[$recordDate])) {
            $dailyTotalHours[$recordDate] = 0;
        }

        $status = $record[4];

        if ($status === 'AM Arrival' || $status === 'PM Arrival') {
            $previousTime = strtotime($record[3]);
        } elseif (($status === 'AM Departure' || $status === 'PM Departure') && isset($previousTime)) {
            $currentTime = strtotime($record[3]);
            $durationInSeconds = $currentTime - $previousTime;
            $dailyTotalHours[$recordDate] += $durationInSeconds;
            $previousTime = null;
        }
}
    foreach ($dailyTotalHours as $date => $totalSeconds) {
        $totalHours = floor($totalSeconds / 3600);
        $remainingSeconds = $totalSeconds % 3600;
        $totalMinutes = floor($remainingSeconds / 60);
        $totalSeconds = $remainingSeconds % 60;

        $worksheet->setCellValue("A$rowIndex", "$date - Total Working Hours: $totalHours hours, $totalMinutes minutes, $totalSeconds seconds");
        $worksheet->getStyle("A$rowIndex")->getFont()->setBold(true);
        $rowIndex++;

        $worksheet->setCellValue("A$rowIndex", 'Time');
        $worksheet->setCellValue("B$rowIndex", 'Status');
        $worksheet->getStyle("A$rowIndex:B$rowIndex")->getFont()->setBold(true);

        foreach ($records as $record) {
            $recordDate = date('Y-m-d', strtotime($record[3]));
            if ($recordDate === $date) {
                $time = date('h:i:s', strtotime($record[3]));
                $status = ($record[4] === 'Time In') ? 'AM Arrival' : (($record[4] === 'Time Out') ? 'AM Departure' : $record[4]); 
                $status = ($record[4] === 'Time In') ? 'PM Arrival' : (($record[4] === 'Time Out') ? 'PM Departure' : $record[4]); 
            
                $rowIndex++;
                $worksheet->setCellValue("A$rowIndex", $time);
                $worksheet->setCellValue("B$rowIndex", $status);
            }
        }

        $rowIndex++;
    }
}

function loadAllAttendanceData($csvFile) {
    $allAttendanceData = array();

    if (($handle = fopen($csvFile, "r")) !== FALSE) {
        while (($row = fgetcsv($handle, 100000000, ",")) !== FALSE) {
            // Check if the row has the required number of elements
            if (count($row) >= 5) {
                $allAttendanceData[] = $row;
            }
        }
        fclose($handle);
    }

    return $allAttendanceData;
}

// Filter attendance data based on the specified filter
function filterAttendanceData($attendanceData, $filterOption, $attendanceDate) {
  $filteredData = array();

  foreach ($attendanceData as $record) {
      $recordDate = strtotime($record[3]);
      if ($filterOption === 'all') {
          $filteredData[$record[0]][] = $record;
      } elseif ($filterOption === 'today') {
        $currentDateMinus9Hours = date('Y-m-d', strtotime('-9 hours'));
        if (date('Y-m-d', $recordDate) === $currentDateMinus9Hours) {
            $filteredData[$record[0]][] = $record;
        }
    } elseif ($filterOption === 'daily' && date('Y-m-d', $recordDate) === $attendanceDate) {
          $filteredData[$record[0]][] = $record;
      } elseif ($filterOption === 'weekly') {
          // Calculate week start date based on the specified date
          $weekStartDate = strtotime($attendanceDate . ' -' . date('w', strtotime($attendanceDate)) . ' days');
          $weekEndDate = strtotime('+7 days, -9 hours', $weekStartDate);

          if ($recordDate >= $weekStartDate && $recordDate < $weekEndDate) {
              $filteredData[$record[0]][] = $record;
          }
      } elseif ($filterOption === 'monthly' && date('m Y', $recordDate) === date('m Y', strtotime($attendanceDate))) {
          $filteredData[$record[0]][] = $record;
      }
  }

  return $filteredData;
}
?>
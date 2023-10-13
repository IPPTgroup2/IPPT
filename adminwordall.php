<?php
require('vendor/autoload.php'); // Include PHPWord autoload file

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

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
        // Generate the Word content and output it for download
        generateAttendanceReportWord($filteredData, $filterOption);
    } else {
        echo "No attendance records found for the selected filter.";
    }
} else {
    // Handle invalid request
    echo "Invalid request.";
}

// Function to generate the attendance report Word document
function generateAttendanceReportWord($filteredData, $filterOption) {
    $phpWord = new PhpWord();
    $section = $phpWord->addSection();

    $place = 'Republic of the Philippines';
    $region='Region V';
    $school='Polytechnic University of the Philippines - Ragay Branch';
    $schoolLogo = 'img/pup.png';
    $table = $section->addTable();

$table->addRow();
$table->addCell(2000)->addImage($schoolLogo, array('width' => 70, 'height' => 70));
$infoCell = $table->addCell(8000);
$infoCell->addText($place, array('bold' => true, 'size' => 12), array('alignment' => 'left'));
$infoCell->addText($region, array('bold' => true, 'size' => 12), array('alignment' => 'left'));
$infoCell->addText($school, array('bold' => true, 'size' => 12), array('alignment' => 'left'));

    $section->addTextBreak(1);

    foreach ($filteredData as $name => $records) {
        $section->addText("Attendance Report of $name - ($filterOption)", array('size' => 14, 'bold' => true));
        $section->addText("\n");

        // Calculate and display daily total working hours
        calculateAndDisplayDailyTotalHoursWord($section, $records);
    }

    // Output Word document to download
    $wordName = "attendance_report_$filterOption.docx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header("Content-Disposition: attachment; filename=\"$wordName\"");
    $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
    $objWriter->save('php://output');
}

function calculateAndDisplayDailyTotalHoursWord($section, $records) {
    $dailyTotalHours = array();
    $previousTimeIn = null;

    foreach ($records as $record) {
        $recordDate = date('Y-m-d', strtotime($record[3]));

        if (!isset($dailyTotalHours[$recordDate])) {
            $dailyTotalHours[$recordDate] = 0;
        }

        $status = $record[4];

        if ($status === 'AM Arrival' || $status === 'PM Arrival') {
            $previousTimeIn = strtotime($record[3]);
        } elseif ($status === 'AM Departure' || $status === 'PM Departure' && isset($previousTimeIn)) {
            $currentTimeOut = strtotime($record[3]);
            $durationInSeconds = $currentTimeOut - $previousTimeIn;
            $dailyTotalHours[$recordDate] += $durationInSeconds;
            $previousTimeIn = null;
        }
    }

    $uniqueDates = array_keys($dailyTotalHours);

    foreach ($uniqueDates as $date) {
        $totalHours = floor($dailyTotalHours[$date] / 3600);
        $remainingSeconds = $dailyTotalHours[$date] % 3600;
        $totalMinutes = floor($remainingSeconds / 60);
        $totalSeconds = $remainingSeconds % 60;

        $section->addText("$date - Total Working Hours: $totalHours hours, $totalMinutes minutes, $totalSeconds seconds");
        $section->addText("\n");

        $table = $section->addTable();
        $table->addRow();
        $table->addCell(4500)->addText('Time');
        $table->addCell(4500)->addText('Status');

        foreach ($records as $record) {
            $recordDate = date('Y-m-d', strtotime($record[3]));
            if ($recordDate === $date) {
                $time = date('h:i:s', strtotime($record[3]));

                $table->addRow();
                $table->addCell(4500)->addText($time);
                $table->addCell(4500)->addText($record[4]);
            }
        }
        $section->addText("\n");
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
      }elseif ($filterOption === 'today') {
        $currentDateMinus9Hours = date('Y-m-d', strtotime('-9 hours'));
        if (date('Y-m-d', $recordDate) === $currentDateMinus9Hours) {
            $filteredData[] = $record;
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

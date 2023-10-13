<?php
require('vendor/autoload.php'); // Include PHPWord library

use PhpOffice\PhpWord\Style\Font;

// Define the path to your CSV file
$csvFile = 'try.csv'; // Update with the actual path to your CSV file
// Define school information
$place = 'Republic of the Philippines';
$region='Region V';
$school='Polytechnic University of the Philippines - Ragay Branch';
$schoolLogo = 'img/pup.png';

if (isset($_GET['name']) && isset($_GET['filter']) && isset($_GET['date'])) {
    $name = urldecode($_GET['name']);
    $filterOption = urldecode($_GET['filter']);
    $attendanceDate = urldecode($_GET['date']);

    // Generate the Word document content
    $phpWord = new \PhpOffice\PhpWord\PhpWord();
    $section = $phpWord->addSection();

    $table = $section->addTable();
$table->addRow();
$table->addCell(2000)->addImage($schoolLogo, array('width' => 70, 'height' => 70));
$infoCell = $table->addCell(8000);
$infoCell->addText($place, array('bold' => true, 'size' => 12), array('alignment' => 'left'));
$infoCell->addText($region, array('bold' => true, 'size' => 12), array('alignment' => 'left'));
$infoCell->addText($school, array('bold' => true, 'size' => 12), array('alignment' => 'left'));

    $section->addTextBreak(1);

    $section->addText("Attendance Report of $name - ($filterOption)", array('bold' => true, 'size' => 16), array('align' => 'center'));
    $section->addTextBreak(1);

    // Load attendance data based on name
    $attendanceData = loadAttendanceData($csvFile, $name);

    // Filter data based on the specified filter and date
    $filteredData = filterAttendanceData($attendanceData, $filterOption, $attendanceDate);

    if (!empty($filteredData)) {
        $currentDate = null;
        $dailyTotalTimeInSeconds = 0; // Initialize daily total time

        $table = $section->addTable(); // Create a new table for the records

        // Define table headers
        $table->addRow();
        $table->addCell(2000)->addText('Date', array('bold' => true));
        $table->addCell(2000)->addText('Time', array('bold' => true));
        $table->addCell(2000)->addText('Status', array('bold' => true));
    
        foreach ($filteredData as $record) {
            $date = date('Y-m-d', strtotime($record[3]));
            $time = date('h:i:s A', strtotime($record[3])); // Format time with AM/PM
            $status = $record[4];
        
            $row = $table->addRow();
            $row->addCell(2000)->addText($date);
            $row->addCell(2000)->addText($time);
            $row->addCell(2000)->addText($status);
            
            // Calculate and accumulate daily total time
            if ($status == 'AM Arrival' || $status == 'PM Arrival') {
                $previousTime = strtotime($record[3]);
            } elseif (($status == 'AM Departure' || $status == 'PM Departure') && isset($previousTime)) {
                $currentTime = strtotime($record[3]);
                $durationInSeconds = $currentTime - $previousTime;
                $dailyTotalTimeInSeconds += $durationInSeconds;
                $previousTime = null; // Reset the previous time
            }
        }        

        // Display the total for the last day
        displayDailyTotal($section, $dailyTotalTimeInSeconds);
    } else {
        $section->addText('No attendance records found for ' . $name . ' with the selected filter.');
    }

    // Save the Word document
    $wordName = "attendance_report_$name($filterOption).docx";
    $phpWord->save($wordName);

    // Provide download link
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment; filename="' . $wordName . '"');
    readfile($wordName);
    unlink($wordName); // Delete the file after sending

} else {
    // Handle invalid request
    echo "Invalid request.";
}
// Function to display daily total time
function displayDailyTotal($section, $totalTimeInSeconds) {
  // Calculate total hours, minutes, and seconds from total time in seconds
  $totalHours = floor($totalTimeInSeconds / 3600); // Get hours
  $remainingSeconds = $totalTimeInSeconds % 3600;
  $totalMinutes = floor($remainingSeconds / 60); // Get minutes
  $totalSeconds = $remainingSeconds % 60; // Get seconds

  $section->addText("Total Working Hours: $totalHours hours, $totalMinutes minutes, $totalSeconds seconds", array('bold' => true));
}

function loadAttendanceData($csvFile, $name) {
    $attendanceData = array();

    if (($handle = fopen($csvFile, "r")) !== FALSE) {
        while (($row = fgetcsv($handle, 100000000, ",")) !== FALSE) {
            if ($row[0] === $name) {
                $row[4] = convertStatus($row[4]); // Convert status to AM/PM format
                $attendanceData[] = $row;
            }
        }
        fclose($handle);
    }

    return $attendanceData;
}

// Function to convert status to AM/PM format
function convertStatus($status) {
    if ($status == 'Time In') {
        return 'AM Arrival';
    } elseif ($status == 'Time Out') {
        return 'AM Departure';
    } elseif ($status == 'PM In') {
        return 'PM Arrival';
    } elseif ($status == 'PM Out') {
        return 'PM Departure';
    } else {
        return $status;
    }
}

// Filter attendance data based on the specified filter and date
function filterAttendanceData($attendanceData, $filterOption, $attendanceDate) {
  $filteredData = array();

  foreach ($attendanceData as $record) {
      $recordDate = strtotime($record[3]);
      
      if ($filterOption === 'all') {
          $filteredData[] = $record;
      }elseif ($filterOption === 'daily' && date('Y-m-d', $recordDate) === $attendanceDate) {
          $filteredData[] = $record;        
      } elseif ($filterOption === 'weekly') {
          $weekStartDate = strtotime($attendanceDate. '-9 hours');
          $weekEndDate = strtotime('+7 days, -9 hours', $weekStartDate);

          if ($recordDate >= $weekStartDate && $recordDate < $weekEndDate) {
              $filteredData[] = $record;
          }
      } elseif ($filterOption === 'monthly' && date('m Y', $recordDate) === date('m Y', strtotime($attendanceDate))) {
          $filteredData[] = $record;
      }
      elseif ($filterOption === 'today') {
      $currentDateMinus9Hours = date('Y-m-d', strtotime('-9 hours'));
      if (date('Y-m-d', $recordDate) === $currentDateMinus9Hours) {
          $filteredData[] = $record;
      }
  }
  }
  return $filteredData;
}
?>

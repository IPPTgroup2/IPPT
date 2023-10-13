<?php
require('fpdf/fpdf.php');

function loadAttendanceData($csvFile, $name) {
    $attendanceData = array();

    if (($handle = fopen($csvFile, "r")) !== FALSE) {
        while (($row = fgetcsv($handle, 10000000, ",")) !== FALSE) {
            if (!empty($row[0]) && $row[0] === $name) {
                $attendanceData[] = $row;
            }
        }
        fclose($handle);
    }

    return $attendanceData;
}

function filterAttendanceData($attendanceData, $filterOption, $attendanceDate) {
    $filteredData = array();

    foreach ($attendanceData as $record) {
        $recordDate = strtotime($record[3]);
        
        if ($filterOption === 'all') {
            $filteredData[] = $record;
        } elseif ($filterOption === 'daily' && date('Y-m-d', $recordDate) === $attendanceDate) {
            $filteredData[] = $record;        
        } elseif ($filterOption === 'weekly') {
            $weekStartDate = strtotime($attendanceDate. '-9 hours');
            $weekEndDate = strtotime('+7 days, -9 hours', $weekStartDate);

            if ($recordDate >= $weekStartDate && $recordDate < $weekEndDate) {
                $filteredData[] = $record;
            }
        } elseif ($filterOption === 'monthly' && date('m Y', $recordDate) === date('m Y', strtotime($attendanceDate))) {
            $filteredData[] = $record;
        } elseif ($filterOption === 'today') {
            $currentDateMinus9Hours = date('Y-m-d', strtotime('-9 hours'));
            if (date('Y-m-d', $recordDate) === $currentDateMinus9Hours) {
                $filteredData[] = $record;
            }
        }
    }

    return $filteredData;
}

$csvFile = 'try.csv'; // Update with the actual path to your CSV file

$schoolName = "Republic of the Philippines\nRegion V\nPolytechnic University of the Philippines - Ragay Branch";
$schoolLogo = 'img/pup.png';

if (isset($_GET['name']) && isset($_GET['filter']) && isset($_GET['date'])) {
    $name = urldecode($_GET['name']);
    $filterOption = urldecode($_GET['filter']);
    $attendanceDate = urldecode($_GET['date']);

    $pdf = new FPDF();
    $pdf->AddPage();

    $pdf->AddFont('Times-Roman', 'B', 'times.php'); 
    $pdf->SetFont('Times-Roman', 'B', 16);

    $schoolInfoWidth = $pdf->GetStringWidth($schoolName);

    $pdf->Image($schoolLogo, 10, 10, 30);
    $pdf->SetX(50);
    $pdf->MultiCell(0, 10, $schoolName, 0, 'L');
    $pdf->Ln(10);

    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, "Attendance Report of $name - ($filterOption)", 0, 1, 'C');
    $pdf->Ln(10);

    $attendanceData = loadAttendanceData($csvFile, $name);

    $filteredData = filterAttendanceData($attendanceData, $filterOption, $attendanceDate);

    if (!empty($filteredData)) {
        $currentDate = null;
        $dailyTotalTimeInSeconds = 0;

        foreach ($filteredData as $record) {
            $date = date('Y-m-d', strtotime($record[3]));
            $time = date('h:i:s', strtotime($record[3]));
            $status = $record[4];

            if ($currentDate !== $date) {
                if ($currentDate !== null) {
                    displayDailyTotal($pdf, $dailyTotalTimeInSeconds);

                    $dailyTotalTimeInSeconds = 0;
                    $pdf->Ln(10);
                }
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->Cell(0, 10, "$date", 0, 1, 'L');
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->Cell(90, 10, 'Time', 1, 0, 'C');
                $pdf->Cell(90, 10, 'Status', 1, 1, 'C');
                $currentDate = $date;
            }

            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(90, 10, $time, 1, 0, 'C');

            if ($status == 'Time In') {
                $status = (date('a', strtotime($record[3])) == 'am') ? 'AM Arrival' : 'PM Arrival';
            } elseif ($status == 'Time Out') {
                $status = (date('a', strtotime($record[3])) == 'am') ? 'AM Departure' : 'PM Departure';
            }

            $pdf->Cell(90, 10, $status, 1, 1, 'C');

            if ($status == 'AM Arrival' || $status == 'PM Arrival') {
                $previousTime = strtotime($record[3]);
            } elseif ($status == 'AM Departure' || $status == 'PM Departure' && isset($previousTime)) {
                $currentTime = strtotime($record[3]);
                $durationInSeconds = $currentTime - $previousTime;
                $dailyTotalTimeInSeconds += $durationInSeconds;
                $previousTime = null;
            }
        }

        displayDailyTotal($pdf, $dailyTotalTimeInSeconds);
    } else {
        echo "No attendance records found for $name with the selected filter.";
    }

    header('Content-Type: application/pdf');
    $pdfName = "attendance_report_$name($filterOption).pdf";
    header('Content-Disposition: attachment; filename="' . $pdfName . '"');
    $pdf->Output($pdfName, 'D');
} else {
    echo "Invalid request.";
}

function displayDailyTotal($pdf, $totalTimeInSeconds) {
    $totalHours = floor($totalTimeInSeconds / 3600);
    $remainingSeconds = $totalTimeInSeconds % 3600;
    $totalMinutes = floor($remainingSeconds / 60);
    $totalSeconds = $remainingSeconds % 60;

    $pdf->Ln(2);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, "Total Working Hours: $totalHours hours, $totalMinutes minutes, $totalSeconds seconds", 0, 1, 'L');
}
?>

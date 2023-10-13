<?php
require('fpdf/fpdf.php');

function loadAllAttendanceData($csvFile) {
    $allAttendanceData = array();

    if (($handle = fopen($csvFile, "r")) !== FALSE) {
        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if (!empty($row[0])) { // Check if the first column is not empty
                $allAttendanceData[] = $row;
            }
        }
        fclose($handle);
    }

    return $allAttendanceData;
}

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

function generateAttendanceReportPDF($filteredData, $filterOption) {
    $pdf = new FPDF();
    $pdf->AddPage();

    $region = 'Region V ';
    $place = '                            Republic of the Philippines ';
    $schoolName = '                            Polytechnic University of the Philippines - Ragay Branch';
    $schoolLogo = 'img/pup.png';

    $pdf->AddFont('Times-Roman', 'B', 'times.php'); 
    $pdf->SetFont('Times-Roman', 'B', 16);

    $schoolInfoWidth = $pdf->GetStringWidth($schoolName, $place, $region);

    $pdf->Image($schoolLogo, 10, 10, 30);
    $pdf->SetX(50);
    $pdf->MultiCell(0, 10, $region, 0, 'L');
    $pdf->MultiCell(0, 10, $place, 0, 'L');    
    $pdf->MultiCell(0, 10, $schoolName, 0, 'L');
    $pdf->Ln(10);

    foreach ($filteredData as $name => $records) {
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, "Attendance Report of $name - ($filterOption)", 0, 1, 'C');
        $pdf->Ln(5);

        calculateAndDisplayDailyTotalHours($pdf, $records);

        $pdf->Ln(5); 
    }

    header('Content-Type: application/pdf');
    $pdfName = "attendance_report_$filterOption.pdf";
    header("Content-Disposition: attachment; filename=\"$pdfName\"");
    $pdf->Output($pdfName, 'D');
}

function calculateAndDisplayDailyTotalHours($pdf, $records) {
    $dailyTotalHours = array();
    
    foreach ($records as $record) {
        $recordDate = date('Y-m-d', strtotime($record[3]));

        if (!isset($dailyTotalHours[$recordDate])) {
            $dailyTotalHours[$recordDate] = 0;
        }

        $status = $record[4];

        if ($status == 'AM Arrival' || $status == 'PM Arrival') {
            $previousTime = strtotime($record[3]);
        } elseif (($status == 'AM Departure' || $status == 'PM Departure') && isset($previousTime)) {
            $currentTime = strtotime($record[3]);
            $durationInSeconds = $currentTime - $previousTime;
            $dailyTotalHours[$recordDate] += $durationInSeconds;
            $previousTime = null;
        }
    }

    $uniqueDates = array_keys($dailyTotalHours);

    foreach ($uniqueDates as $date) {
        $pdf->Ln(5);
        $totalHours = floor($dailyTotalHours[$date] / 3600);
        $remainingSeconds = $dailyTotalHours[$date] % 3600;
        $totalMinutes = floor($remainingSeconds / 60);
        $totalSeconds = $remainingSeconds % 60;

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, "$date - Total Working Hours: $totalHours hours, $totalMinutes minutes, $totalSeconds seconds", 0, 1, 'L');
        $pdf->Ln(2);

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(90, 10, 'Time', 1, 0, 'C');
        $pdf->Cell(90, 10, 'Status', 1, 1, 'C');

        foreach ($records as $record) {
            $recordDate = date('Y-m-d', strtotime($record[3]));
            if ($recordDate === $date) {
                $time = date('h:i:s', strtotime($record[3]));

                $pdf->SetFont('Arial', '', 12);
                $pdf->Cell(90, 10, $time, 1, 0, 'C');
                $pdf->Cell(90, 10, $record[4], 1, 1, 'C');
            }
        }
        $pdf->Ln(2);
    }
}

$csvFile = 'try.csv';

if (isset($_GET['filter']) && isset($_GET['date'])) {
    $filterOption = urldecode($_GET['filter']);
    $attendanceDate = urldecode($_GET['date']);

    $allAttendanceData = loadAllAttendanceData($csvFile);
    $filteredData = filterAttendanceData($allAttendanceData, $filterOption, $attendanceDate);

    if (!empty($filteredData)) {
        generateAttendanceReportPDF($filteredData, $filterOption);
    } else {
        echo "No attendance records found for the selected filter.";
    }
} else {
    echo "Invalid request.";
}
?>

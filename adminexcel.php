<?php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$csvFile = 'try.csv';
$schoolName = 'Your School Name';

function loadAttendanceData($csvFile, $name) {
    $attendanceData = array();

    if (($handle = fopen($csvFile, "r")) !== FALSE) {
        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // Check if the row has the required number of elements
            if (count($row) >= 5) {
                if ($row[0] === $name) {
                    $attendanceData[] = $row;
                }
            }
        }
        fclose($handle);
    }

    return $attendanceData;
}

function calculateTotalWorkingHours($records) {
    $totalWorkingHours = array();

    $previousTimeIn = null;

    foreach ($records as $record) {
        $recordDate = date('Y-m-d', strtotime($record[3]));
        $status = $record[4];

        if ($status === 'AM Arrival' || $status === 'PM Arrival') {
            $previousTimeIn = strtotime($record[3]);
        } elseif (($status === 'AM Departure' || $status === 'PM Departure') && isset($previousTimeIn)) {
            $endTime = strtotime($record[3]);
            $duration = $endTime - $previousTimeIn;

            if (!isset($totalWorkingHours[$recordDate])) {
                $totalWorkingHours[$recordDate] = 0;
            }

            $totalWorkingHours[$recordDate] += $duration;
            $previousTimeIn = null;
        }
    }

    return $totalWorkingHours;
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
            $weekStartDate = strtotime($attendanceDate . '-9 hours');
            $weekEndDate = strtotime('+7 days, -9 hours', $weekStartDate);

            if ($recordDate >= $weekStartDate && $recordDate < $weekEndDate) {
                $filteredData[] = $record;
            }
        } elseif ($filterOption === 'monthly' && date('m Y', $recordDate) === date('m Y', strtotime($attendanceDate))) {
            $filteredData[] = $record;
        }elseif ($filterOption === 'today') {
            $currentDateMinus9Hours = date('Y-m-d', strtotime('-9 hours'));
            if (date('Y-m-d', $recordDate) === $currentDateMinus9Hours) {
                $filteredData[] = $record;
            }
        }
    }

    return $filteredData;
}

$name = isset($_GET['name']) ? urldecode($_GET['name']) : '';
$filterOption = isset($_GET['filter']) ? urldecode($_GET['filter']) : '';
$attendanceDate = isset($_GET['date']) ? urldecode($_GET['date']) : '';

$attendanceData = loadAttendanceData($csvFile, $name);
$filteredData = filterAttendanceData($attendanceData, $filterOption, $attendanceDate);

if (!empty($filteredData)) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set headers and data
    $sheet->setCellValue('A1', 'Name');
    $sheet->setCellValue('B1', 'Date');
    $sheet->setCellValue('C1', 'Time');
    $sheet->setCellValue('D1', 'Status');

    $row = 2;
    foreach ($filteredData as $record) {
        $sheet->setCellValue('A' . $row, $record[0]);
        $sheet->setCellValue('B' . $row, date('Y-m-d', strtotime($record[3])));
        $sheet->setCellValue('C' . $row, date('h:i:s', strtotime($record[3])));
        $sheet->setCellValue('D' . $row, $record[4]);
        $row++;
    }

    // Apply table style
    $tableRange = 'A1:D' . (count($filteredData) + 1);
    $tableStyle = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            ],
        ],
    ];
    $sheet->getStyle($tableRange)->applyFromArray($tableStyle);

    $writer = new Xlsx($spreadsheet);
    $excelName = "attendance_report_$name($filterOption).xlsx";
    $writer->save($excelName);

    if (file_exists($excelName)) {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $excelName . '"');
        header('Cache-Control: max-age=0');
        readfile($excelName);
        unlink($excelName); // Delete the file after sending
    } else {
        echo "Error: File not saved.";
    }
} else {
    echo "No attendance records found for $name with the selected filter.";
}

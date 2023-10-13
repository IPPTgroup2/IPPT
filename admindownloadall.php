<?php
session_start();
require('connection.php');
require('fpdf/fpdf.php');

function downloadPDF($filename, $personnelData, $reportType)
{
    // Create a new PDF instance
    $pdf = new FPDF('L'); // Set landscape orientation

    foreach ($personnelData as $personnelName => $dates) {
        foreach ($dates as $date => $records) {
            // Add a new page for each personnel and date
            $pdf->AddPage();

            // Set font for the table headers
            $pdf->SetFont('Arial', 'B', 10);

            // Output the name, date, and report type
            $pdf->Cell(0, 10, "($reportType) Service Requests", 0, 1, 'C');
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(0, 10, "$personnelName", 0, 1, 'L');
            $pdf->Cell(0, 10, "Date Request: $date", 0, 1, 'L');
            $pdf->Ln(5);

            // Output the table headers with adjusted widths
            $pdf->Cell(60, 10, 'Request By', 1, 0, 'C');
            $pdf->Cell(60, 10, 'Purpose', 1, 0, 'C');
            $pdf->Cell(60, 10, 'Email', 1, 0, 'C');
            $pdf->Cell(25, 10, 'Date', 1, 0, 'C');
            $pdf->Cell(25, 10, 'Time', 1, 0, 'C');
            $pdf->Cell(25, 10, 'Status', 1, 1, 'C');

            // Set font for the table content
            $pdf->SetFont('Arial', '', 10);

            // Output the records for the date with adjusted widths
            foreach ($records as $record) {
                $pdf->Cell(60, 10, $record['booked_by'], 1, 0, 'C');
                $pdf->Cell(60, 10, $record['purpose'], 1, 0, 'C');
                $pdf->Cell(60, 10, $record['email'], 1, 0, 'C');
                $pdf->Cell(25, 10, $record['date'], 1, 0, 'C');
                $pdf->Cell(25, 10, $record['time'], 1, 0, 'C');
                $pdf->Cell(25, 10, $record['status'], 1, 1, 'C');
            }

            // Add a separator line after each table
            $pdf->Ln(10); // Add some space after the table
            $pdf->Cell(0, 0, '---------------------------------------------------------', 0, 1, 'C');
            $pdf->Ln(10); // Add some space after the separator
        }
    }

    // Output the PDF as a download
    header('Content-Type: application/pdf');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    $pdf->Output($filename, 'D');
    exit();
}

if (isset($_GET['report-type'])) {
    $reportType = $_GET['report-type'];

    // Initialize filter dates
    $filterStartDate = '';
    $filterEndDate = '';

    if ($reportType === 'daily' && isset($_GET['selected-date'])) {
        $selectedDate = $_GET['selected-date'];
        $filterStartDate = date('Y-m-d', strtotime('-9 hours', strtotime($selectedDate)));
    } elseif ($reportType === 'weekly' && isset($_GET['week-start-date'])) {
        $selectedStartDate = $_GET['week-start-date'];
        $filterStartDate = $selectedStartDate;
        $filterEndDate = date('Y-m-d', strtotime('+6 days, -9 hours', strtotime($selectedStartDate)));
    } elseif ($reportType === 'monthly' && isset($_GET['selected-month'])) {
        $selectedMonth = $_GET['selected-month'];
        $filterStartDate = date('m Y', strtotime($selectedMonth));
    } elseif ($reportType === 'all') {
        $filterStartDate = '1900-01-01'; // Adjust this to an appropriate date
        $filterEndDate = date('Y-m-d');
    }

    // Fetch booking history based on the report type and adjusted dates
    $sql = "SELECT * FROM bookings WHERE date_request >= '$filterStartDate' AND date_request <= '$filterEndDate'";
    $result = mysqli_query($con, $sql);

    if (mysqli_num_rows($result) > 0) {
        $bookingHistory = array();

        while ($row = mysqli_fetch_assoc($result)) {
            // Organize the records by personnel name and date_request
            $personnelName = $row['name'];
            $date = date('Y-m-d', strtotime($row['date_request']));
            $bookingHistory[$personnelName][$date][] = $row;
        }

        // Generate and download the PDF report
        $filename = "Service_Requests_All_{$reportType}.pdf";
        downloadPDF($filename, $bookingHistory, ucfirst($reportType));
    } else {
        echo "No booking history found.";
    }

    // Close the result and connection
    mysqli_free_result($result);
    mysqli_close($con);
} else {
    echo "Invalid report request.";
}
?>

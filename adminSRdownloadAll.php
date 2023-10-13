<?php
session_start();
require('connection.php');
require('fpdf/fpdf.php');

// Define school information
$schoolName = 'Your School Name';
$schoolLogo = 'img/pup.png';

if (isset($_GET['report-type']) && isset($_GET['specific-date'])) {
    $reportType = $_GET['report-type'];
    $specificDate = mysqli_real_escape_string($con, $_GET['specific-date']);

 // Construct the query based on report type and specific date
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
}elseif ($reportType === 'all') {
    // No additional constraints needed for "All" filter
} else {
    echo "Invalid report type.";
    exit;
}

// Construct the full SQL query
$sql = "SELECT * FROM bookings";
if (!empty($whereCondition)) {
    $sql .= " WHERE $whereCondition";
}
$sql .= " ORDER BY name, date_request";

// Fetch data for the selected criteria
$result = mysqli_query($con, $sql);

if ($result) {
    if (mysqli_num_rows($result) > 0) {
        // Create a new PDF instance
        $pdf = new FPDF('L');
        $pdf->AddPage();

    $pdf->AddFont('Times-Roman', 'B', 'times.php'); 
    $pdf->SetFont('Times-Roman', 'B', 14);

    $schoolInfoWidth = $pdf->GetStringWidth("Republic of the Philippines\nRegion V\nPolytechnic University of the Philippines - Ragay Branch");

    // Add school logo to the header (left side)
    $pdf->Image($schoolLogo, 10, 10, 30);

    // Add school information text on the right side of the logo
    $pdf->SetX(50); // Adjust this value as needed to align the text
    $pdf->MultiCell(0, 10, "Republic of the Philippines\nRegion V\nPolytechnic University of the Philippines - Ragay Branch", 0, 'L');
    $pdf->Ln(5);

        // Set font for the table headers
        $pdf->SetFont('Arial', 'B', 10);

        // Output the name and report type
        $pdf->Cell(0, 10, "Service Requests - ($reportType)", 0, 1, 'C');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Ln(5);

        $prevOffice = null;
        $prevDate = null;
        $prevName = null;

        while ($row = mysqli_fetch_assoc($result)) {
            $currentOffice = $row['office'];
            $currentName = $row['name'];
            $currentDate = $row['date_request'];


            // Check if the personnel or date has changed
            if ($currentOffice !== $prevOffice || $currentName !== $prevName || $currentDate !== $prevDate) {
                // Close previous table (if any)
                if ($prevOffice !== null) {
                    $pdf->Ln(5);
                }

                // Output the name and date_request as a subtitle (if changed)
                if ($currentOffice !== $prevOffice) {
                    $pdf->SetFont('Arial', 'B', 10);
                    $pdf->Cell(0, 10, "$currentOffice", 0, 1, 'L');
                }
                if ($currentName !== $prevName) {
                    $pdf->SetFont('Arial', 'B', 10);
                    $pdf->Cell(0, 10, "$currentName", 0, 1, 'L');
                }
                if ($currentDate !== $prevDate) {
                    $pdf->SetFont('Arial', 'B', 10);
                    $pdf->Cell(0, 10, "$currentDate", 0, 1, 'L');
                }
                $pdf->Ln(5);

                // Output the table headers with adjusted widths
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell(50, 10, 'Request By', 1, 0, 'C');
                $pdf->Cell(50, 10, 'Purpose', 1, 0, 'C');
                $pdf->Cell(50, 10, 'Email', 1, 0, 'C');
                $pdf->Cell(25, 10, 'Date', 1, 0, 'C');
                $pdf->Cell(25, 10, 'Time', 1, 0, 'C');
                $pdf->Cell(35, 10, 'Status', 1, 0, 'C');
                $pdf->Cell(40, 10, 'Reason', 1, 1, 'C');

                // Set font for the table content
                $pdf->SetFont('Arial', '', 10);

                $prevOffice = $currentOffice;
                $prevName = $currentName;
                $prevDate = $currentDate;
            }

            $pdf->Cell(50, 10, $row['booked_by'], 1, 0, 'LR');
            $pdf->Cell(50, 10, $row['purpose'], 1, 0, 'LR');
            $pdf->Cell(50, 10, $row['email'], 1, 0, 'LR');
            $pdf->Cell(25, 10, $row['date'], 1, 0, 'LR');
            $pdf->Cell(25, 10, $row['time'], 1, 0, 'LR');
            $pdf->Cell(35, 10, $row['status'], 1, 0, 'LR');
            $pdf->Cell(40, 10, $row['reason'], 1, 1, 'LR');
            

        }

        // Output the PDF for download
        header('Content-Type: application/pdf');
        header("Content-Disposition: attachment; filename=Service_Request_Report.pdf");
        echo $pdf->Output('S');
    } else {
        echo "No records found.";
    }

    // Close the result
    mysqli_free_result($result);
} else {
    echo "Query error: " . mysqli_error($con);
}

// Close the connection
mysqli_close($con);
} else {
echo "Invalid request.";
}
?>
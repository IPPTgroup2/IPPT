<?php
session_start();
require('connection.php');
require('fpdf/fpdf.php');

// Define school information
$schoolName = 'Your School Name';
$schoolLogo = 'img/pup.png';

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

    }else {
        echo "Invalid report type.";
        exit;
    }

 // Fetch data for the selected personnel and criteria
 $sql = "SELECT * FROM bookings WHERE $whereCondition ORDER BY date_request";
 $result = mysqli_query($con, $sql);

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
 $pdf->Ln(10);

     // Set font for the table headers
     $pdf->SetFont('Arial', 'B', 10);

     // Output the name and report type
     $pdf->Cell(0, 10, " Service Requests for $office - ($reportType)", 0, 1, 'C');


     $prevDate = null;

     while ($row = mysqli_fetch_assoc($result)) {
         $currentDate = $row['date_request'];
         $name = $row['name'];

         // Check if the date has changed
         if ($currentDate !== $prevDate) {
             // Close previous table (if any)
             if ($prevDate !== null) {
                 $pdf->Ln(10);
             }

             // Output the date_request date as a subtitle
             $pdf->SetFont('Arial', '', 10);
             $pdf->Cell(0, 10, "Personnel: $name", 0, 1, 'L');
             $pdf->Cell(0, 10, "Date Request: $currentDate", 0, 1, 'L');
             $pdf->Ln(5);

             // Output the table headers with adjusted widths
             $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell(50, 10, 'Request By', 1, 0, 'C');
                $pdf->Cell(50, 10, 'Purpose', 1, 0, 'C');
                $pdf->Cell(50, 10, 'Email', 1, 0, 'C');
                $pdf->Cell(25, 10, 'Date', 1, 0, 'C');
                $pdf->Cell(25, 10, 'Time', 1, 0, 'C');
                $pdf->Cell(35, 10, 'Status', 1, 0, 'C');
                $pdf->Cell(50, 10, 'Reason', 1, 1, 'C');

             // Set font for the table content
             $pdf->SetFont('Arial', '', 10);

             $prevDate = $currentDate;
         }

         // Output the records for the date with adjusted widths
         $pdf->Cell(50, 10, $row['booked_by'], 1, 0, 'LR');
            $pdf->Cell(50, 10, $row['purpose'], 1, 0, 'LR');
            $pdf->Cell(50, 10, $row['email'], 1, 0, 'LR');
            $pdf->Cell(25, 10, $row['date'], 1, 0, 'LR');
            $pdf->Cell(25, 10, $row['time'], 1, 0, 'LR');
            $pdf->Cell(35, 10, $row['status'], 1, 0, 'LR');
            $pdf->Cell(50, 10, $row['reason'], 1, 1, 'LR');
     }

     // Output the PDF for download
     header('Content-Type: application/pdf');
     header("Content-Disposition: attachment; filename=Service_Request_Report_$office.pdf");
     echo $pdf->Output('S');
 } else {
     echo "No records found.";
 }

 // Close the result and connection
 mysqli_free_result($result);
 mysqli_close($con);
} else {
 echo "Invalid request.";
}
?>
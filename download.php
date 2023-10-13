<?php

use PhpOffice\PhpSpreadsheet\Reader\Xml\Style\Alignment;

require('vendor/autoload.php'); // Include PHPWord library

\PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(false); 
\PhpOffice\PhpWord\Settings::setDefaultFontName('Arial');

session_start();

$form_month = isset($_POST['month']) ? $_POST['month'] : date('m'); // Default to current month if not provided
$form_year = isset($_POST['year']) ? $_POST['year'] : date('Y'); // Default to current year if not provided

$facultyNumber = $_SESSION['facultynumber']; // Assuming you store faculty number in session

$records = [];
$file = fopen('try.csv', 'r');

while (($line = fgetcsv($file)) !== FALSE) {
    // Skip empty lines
    if (empty($line)) {
        continue;
    }
    if (isset($line[3])) {
        $record_datetime = new DateTime($line[3]);
    $record_datetime = new DateTime($line[3]);
    $record_date = $record_datetime->format('Y-m-d');
    $record_time = $record_datetime->format('h:i');
    $record_month = $record_datetime->format('m');
    $record_year = $record_datetime->format('Y');

    if ($record_month == $form_month && $record_year == $form_year) {
        if ($line[1] === $facultyNumber) {
        $status = $line[4];

        // Assign time to the corresponding column based on status
        switch ($status) {
            case 'AM Arrival':
                $records[$record_date]['AM Arrival'] = $record_time;
                break;
            case 'AM Departure':
                $records[$record_date]['AM Departure'] = $record_time;

                // Calculate undertime for AM
                $actual_am_departure = $record_datetime->getTimestamp();
                $scheduled_am_departure = strtotime("$record_date 11:30 AM");

                // If actual departure is earlier than scheduled
                if ($actual_am_departure < $scheduled_am_departure) {
                    $undertime_am_seconds = $scheduled_am_departure - $actual_am_departure;
                    if (!isset($records[$record_date]['Undertime'])) {
                        $records[$record_date]['Undertime'] = ['hours' => 0, 'minutes' => 0];
                    }
                    $records[$record_date]['Undertime']['hours'] += floor($undertime_am_seconds / 3600);
                    $records[$record_date]['Undertime']['minutes'] += floor(($undertime_am_seconds % 3600) / 60);
                }

                break;
            case 'PM Arrival':
                $records[$record_date]['PM Arrival'] = $record_time;
                break;
            case 'PM Departure':
                $records[$record_date]['PM Departure'] = $record_time;

                // Calculate undertime for PM
                $actual_pm_departure = $record_datetime->getTimestamp();
                $scheduled_pm_departure = strtotime("$record_date 8:00 PM");

                // If actual departure is earlier than scheduled
                if ($actual_pm_departure < $scheduled_pm_departure) {
                    if (!isset($records[$record_date]['Undertime'])) {
                        $records[$record_date]['Undertime'] = ['hours' => 0, 'minutes' => 0];
                    }
                    $undertime_pm_seconds = $scheduled_pm_departure - $actual_pm_departure;
                    $records[$record_date]['Undertime']['hours'] += floor($undertime_pm_seconds / 3600);
                    $records[$record_date]['Undertime']['minutes'] += floor(($undertime_pm_seconds % 3600) / 60);
                }

                break;
            default:
                // Handle other statuses if necessary
                break;
        }

        // Set the name from the CSV file
        $name = $line[0];
    }
}
}}
fclose($file);

$days_in_month = cal_days_in_month(CAL_GREGORIAN, $form_month, $form_year);

// Create a new section for each record
$phpWord = new \PhpOffice\PhpWord\PhpWord();
$section = $phpWord->addSection(
    array(
        'marginLeft' => 720, // 1 inch in twips
        'marginRight' => 720, // 1 inch in twips
        'marginTop' => 720, // 1 inch in twips
        'marginBottom' => 720, // 1 inch in twips
    )
);

$fontSize = 2; 
$lineSpacing = 1.0; 

// Create a table with a specified width
$table = $section->addTable();
$table->addRow();
$cell = $table->addCell(4800);

$aa = array(
    'PUP-RAGAY.TEMPORARY SUBSTITUTION',
);
foreach ($aa as $a) {
    $textRun = $cell->addText($a, array('bold' => true), array('alignment' => 'right', 'spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => $lineSpacing));
}

// Add Civil Service Form No. 48
$cell->addText('Civil Service Form No. 48', array('italic' => true), array ( 'spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => $lineSpacing));

$aa = array(
    'DAILY TIME RECORD',
    '-----o0o-----'
);
foreach ($aa as $a) {
    $textRun = $cell->addText($a, array('bold' => true), array('alignment' => 'center'), array ( 'spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => $lineSpacing));
}

$aa = array(
    '(Name)'
);
foreach ($aa as $a) {
    $textRun = $cell->addText($name, array('bold' => true, 'underline' => 'single'), array('alignment' => 'center'),  array ( 'spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => $lineSpacing));
    $textRun = $cell->addText($a, array('bold' => false), array('alignment' => 'center'), array ( 'spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => $lineSpacing));
}

$monthYear = strtoupper(date('F', strtotime("$form_year-$form_month-01")));

$lastDay = date('j', strtotime('last day of', strtotime("$form_year-$form_month-01")));

$textRun = $cell->addTextRun();
$textRun->addText('For the month of: ', array('italic' => true), array ( 'spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => $lineSpacing));
$textRun->addText($monthYear, array('bold' => true),  array ( 'spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => $lineSpacing));

$textRun->addText(" 1-$lastDay, $form_year", array('bold' => true), array ( 'spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => $lineSpacing));

$aa = array(
    'Official hours for',
    'arrival and departure'
);

foreach ($aa as $a) {
    if ($a === 'arrival and departure') {
        $textRun = $cell->addText($a, array('bold' => true, 'underline' => 'single'),  array ( 'spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => $lineSpacing));
    } else {
        $textRun = $cell->addText($a, array('bold' => true, 'underline' => 'single'),  array ( 'spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => $lineSpacing));
    }
}

$aa = array(
    'Regular days',
    'Saturdays'
);

foreach ($aa as $a) {
    $textRun = $cell->addText($a, array('bold' => true, 'underline' => 'single'), array('alignment' => 'right'),  array ( 'spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => $lineSpacing));
}


// Create the table
$table = $cell->addTable();
$table->addRow();
$table->addCell(500)->addText('Date');
$table->addCell(500, array('gridSpan' => 2))->addText('AM');
$table->addCell(500, array('gridSpan' => 2))->addText('PM');
$table->addCell(500, array('gridSpan' => 2))->addText('Undertime');

$table->addRow();
$table->addCell(250)->addText('');
$table->addCell(250)->addText('Arrival');
$table->addCell(250)->addText('Departure');
$table->addCell(250)->addText('Arrival');
$table->addCell(250)->addText('Departure');
$table->addCell(250)->addText('Hours');
$table->addCell(250)->addText('Minutes');


for ($day = 1; $day <= $days_in_month; $day++) {
    $date = sprintf('%04d-%02d-%02d', $form_year, $form_month, $day);

    // Check if the date is a Sunday
    $is_sunday = date('N', strtotime($date)) == 7;

    // Generate table rows dynamically (replace $records with your actual data)
    $row = $table->addRow();
    $cellDate = $row->addCell(500);
    $dayOfWeek = date('d', strtotime($date));
    $cellDate->addText($dayOfWeek, array('bold' => true),  array ( 'spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => $lineSpacing));

    $cellAMArrival = $row->addCell(500);
    $cellAMArrival->addText($records[$date]['AM Arrival'] ?? '');

    $cellAMDeparture = $row->addCell(500);
    $cellAMDeparture->addText($records[$date]['AM Departure'] ?? '');

    // Add PM Arrival and Departure cells
    $cellPMArrival = $row->addCell(500);
    $cellPMArrival->addText($records[$date]['PM Arrival'] ?? '');

    $cellPMDeparture = $row->addCell(500);
    $cellPMDeparture->addText($records[$date]['PM Departure'] ?? '');

    // Add Undertime Hours and Minutes cells
    $cellUndertimeHours = $row->addCell(500);
    $cellUndertimeHours->addText($records[$date]['Undertime']['hours'] ?? '');

    $cellUndertimeMinutes = $row->addCell(500);
    $cellUndertimeMinutes->addText($records[$date]['Undertime']['minutes'] ?? '');

    if ($is_sunday) {
        $cellAMArrival->addText('SUN',  array ( 'spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => $lineSpacing));
    }
}
$totalUndertimeHours = 0;
    $totalUndertimeMinutes = 0;
    foreach ($records as $date => $record) {
        if (isset($record['Undertime'])) {
            $totalUndertimeHours += $record['Undertime']['hours'];
            $totalUndertimeMinutes += $record['Undertime']['minutes'];
        }
    }

    // Add a row for total undertime
    $rowTotalUndertime = $table->addRow();
    $rowTotalUndertime->addCell(250)->addText('Total', array('bold' => true),  array ( 'spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => $lineSpacing));
    $rowTotalUndertime->addCell(250); // Empty cell for AM Arrival
    $rowTotalUndertime->addCell(250); // Empty cell for AM Departure
    $rowTotalUndertime->addCell(250); // Empty cell for PM Arrival
    $rowTotalUndertime->addCell(250); // Empty cell for PM Departure

    // Display total undertime hours and minutes
    $cellTotalUndertimeHours = $rowTotalUndertime->addCell(250);
    $cellTotalUndertimeHours->addText($totalUndertimeHours, array('bold' => true),  array ( 'spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => $lineSpacing));

    $cellTotalUndertimeMinutes = $rowTotalUndertime->addCell(250);
    $cellTotalUndertimeMinutes->addText($totalUndertimeMinutes, array('bold' => true),  array ( 'spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => $lineSpacing));


$certs = array(
    'I certify on my honor that the above is a true and correct report of the hours of work performed, record of which was made daily at the time of arrival and departure from office.',
);
foreach ($certs as $cert) {
    $textRun = $cell->addText($cert, array('italic' => true), array ( 'spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => $lineSpacing));
}

$vers = array(
    'VERIFIED as to the prescribed office hours:',
);
foreach ($vers as $ver) {
    $textRun = $cell->addText($ver, array('italic' => true, 'underline' => 'single'), array('alignment' => 'center'),  array ( 'spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => $lineSpacing));
}
$ass = array(
    'Verified:',
);
foreach ($ass as $as) {
    $textRun = $cell->addTextRun(array('alignment' => 'left'),  array ( 'spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => $lineSpacing));
    $textRun->addText($as, array('bold' => false, 'italic' => true),  array ( 'spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => $lineSpacing));
}
// Add signatures (replace with your actual signatures)
$signatures = array(
    'DR. VERONICA S. ALMASE',
);
foreach ($signatures as $signature) {
    $textRun = $cell->addText($signature, array('bold' => true), array('alignment' => 'center'),  array ( 'spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => $lineSpacing));
}

$ass = array(
    'Branch Director',
);
foreach ($ass as $as) {
    $textRun = $cell->addTextRun(array('alignment' => 'center'),  array ( 'spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => $lineSpacing));
    $textRun->addText($as, array('bold' => false, 'underline' => 'single'),  array ( 'spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => $lineSpacing));
}


$section->addTextBreak(2);

$table = $section->addTable();
$table->addRow();
$cell = $table->addCell(4950);


// Add your additional text
$instructions = array(
    'INSTRUCTIONS');
    
    foreach ($instructions as $instruction) {
        $cell->addText($instruction, array('italic' => false, 'bold' => true), array('alignment'=> 'center'),  array ( 'spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => $lineSpacing));
    }

    // Add your additional text
$instructions = array(
    '   Civil Service Form No. 48, after completion, should be filed in the records of the Bureau or Office which submits the monthly report on Civil Service Form No. 3 to the Bureau of Civil Service.',

    '   In lieu of the above, court interpreters and stenographers who accompany the judges of the Court of First Instance will fill out the daily time reports on this form in triplicate, after which they should be approved by the judge with whom service has been rendered, or by an officer of the Department of Justice authorized to do so. The original should be forwarded promptly after the end of the month to the Bureau of Civil Service, thru the Department of Justice; the duplicate to be kept in the Department of Justice; and the triplicate, in the office of the Clerk Court where service was rendered.',

    '   In the space provided for the purpose on the other side will be indicated for the office hours the employee is required to observe, as for example, “Regular days, 8:00 – 12:00 and 1 – 4; Saturdays, 8:00 – 1:00.”',

    '   Attention is invited to paragraph 3, Civil Service Rule XV, Executive Order No. 5, series of 1909, which read as follows:',   

    '   Each chief of a Bureau or Office shall require a daily record of attendance of all the officers and employees under him entitled to leave of absence or vacation (including teachers) to be kept on the proper form and also a systematic office record showing for each day all absences from duty from any cause whatever. At the beginning of each month he shall report to the Commissioner on the proper form of all absences from any cause whatever, including the exact amount of undertime of each person for each day. Officers or employees serving in the field or on the water need not to be required to keep a daily record, but all absences of such employees must be included in the monthly report of changes and absences. Falsification of time records will render the offending officer or employee liable to summary removal from the service and criminal prosecution.',
    );
    
    foreach ($instructions as $instruction) {
        $cell->addText($instruction, array('italic' => false),  array ( 'spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => $lineSpacing));
    }

    // Add your additional text
$instructions = array(
    '   (NOTE: A record made from memory at sometime subsequent to the occurrence of an event is not reliable. Non-observance of office hours deprives the employee of the leave privileges although he may have rendered overtime service. Where service rendered outside of the Office for the whole morning or afternoon notation to that effect should be made clearly.)
    ');
    
    foreach ($instructions as $instruction) {
        $cell->addText($instruction, array('italic' => false),  array ( 'spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => $lineSpacing));
    }


    
// Save the Word document
// Assuming you have a function to convert month numbers to names
function getMonthName($monthNumber) {
    $dateObj   = DateTime::createFromFormat('!m', $monthNumber);
    return $dateObj->format('F');
}

$monthName = getMonthName($form_month);
$wordName = "DTR_$monthName-$form_year.docx";
$phpWord->save($wordName, 'Word2007');


// Provide download link
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="' . $wordName . '"');
readfile($wordName);
unlink($wordName); // Delete the file after sending
?>

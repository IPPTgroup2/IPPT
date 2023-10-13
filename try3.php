<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attendanceData = [];

    for ($day = 1; $day <= 31; $day++) {
        $amArrival = $_POST["am_arrival_$day"] ?? '';
        $amDeparture = $_POST["am_departure_$day"] ?? '';
        $pmArrival = $_POST["pm_arrival_$day"] ?? '';
        $pmDeparture = $_POST["pm_departure_$day"] ?? '';
        $undertimeHours = $_POST["undertime_hours_$day"] ?? '';
        $undertimeMinutes = $_POST["undertime_minutes_$day"] ?? '';

        $attendanceData[] = [
            $amArrival, $amDeparture, $pmArrival, $pmDeparture, $undertimeHours, $undertimeMinutes
        ];
    }

    $file = fopen("generated_time_record.csv", "w");

    if ($file) {
        foreach ($attendanceData as $row) {
            fputcsv($file, $row);
        }
        fclose($file);
        echo "Data saved successfully!";
    } else {
        echo "Failed to save data.";
    }

    exit;
}

$file = fopen("try.csv", "r");
$attendanceData = [];

if ($file) {
    $groupedData = [];

    while (($row = fgetcsv($file)) !== false) {
        $date = date('Y-m-d', strtotime($row[3])); // Assuming the time is in the 4th column
        $status = $row[4]; // Assuming the status is in the 5th column

        if (!isset($groupedData[$date])) {
            $groupedData[$date] = ['', '', '', '', '', ''];
        }

        // Check if the status contains "am arrival" or "am departure"
        if (stripos($status, "am arrival") !== false) {
            $groupedData[$date][0] = trim($row[3]);
        } elseif (stripos($status, "am departure") !== false) {
            $groupedData[$date][1] = trim($row[3]);
        } elseif (stripos($status, "pm arrival") !== false) {
            $groupedData[$date][2] = trim($row[3]);
        } elseif (stripos($status, "pm departure") !== false) {
            $groupedData[$date][3] = trim($row[3]);
        }
    }
    fclose($file);

    // Flatten the grouped data into a single array
    foreach ($groupedData as $date => $data) {
        $attendanceData[] = array_merge([$date], $data);
    }
} else {
    echo "Failed to open the CSV file.";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Time Record</title>
    <style>
        table {
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 5px;
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Civil Service Form No. 48</h1>
    <h2>DAILY TIME RECORD</h2>
    <h3>For the month of JULY 1-31, 2023</h3>
    <h4>Name: DORREN DULFO-ARENQUE</h4>

    <form method="post" action="generate_time_record.php">
        <table>
            <tr>
                <th>Day</th>
                <th>A.M. Arrival</th>
                <th>A.M. Departure</th>
                <th>P.M. Arrival</th>
                <th>P.M. Departure</th>
                <th>Undertime Hours</th>
                <th>Undertime Minutes</th>
            </tr>

            <?php
            $daysInMonth = 31;
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $rowData = $attendanceData[$day - 1] ?? null;
                $dayOfWeek = date('D', strtotime("2023-07-$day"));
                $isSunday = $dayOfWeek === 'Sun';

                if ($rowData !== null) {
                    list($amArrival, $amDeparture, $pmArrival, $pmDeparture, $undertimeHours, $undertimeMinutes) = $rowData;
                } else {
                    $amArrival = $amDeparture = $pmArrival = $pmDeparture = $undertimeHours = $undertimeMinutes = '';
                }

                echo "<tr>";
                echo "<td>$day";
                if ($isSunday) {
                    echo "<br>SUN";
                }
                echo "</td>";
                echo "<td><input type='text' name='am_arrival_$day' value='$amArrival'></td>";
                echo "<td><input type='text' name='am_departure_$day' value='$amDeparture'></td>";
                echo "<td><input type='text' name='pm_arrival_$day' value='$pmArrival'></td>";
                echo "<td><input type='text' name='pm_departure_$day' value='$pmDeparture'></td>";
                echo "<td><input type='text' name='undertime_hours_$day' value='$undertimeHours'></td>";
                echo "<td><input type='text' name='undertime_minutes_$day' value='$undertimeMinutes'></td>";
                echo "</tr>";
            }
            ?>

        </table>
        <button type="submit">Save Data</button>
    </form>
</body>
</html>
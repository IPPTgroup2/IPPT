<?php
session_start();
require "connection.php";

// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}
 // Check if the user is logged in
  if (!isset($_SESSION['facultynumber'])) {
    header("Location: personnellogin.php"); // Redirect to the login page if not logged in
    exit;
}
// Set the current page based on the query parameter or the actual page name
$activePage = isset($_GET['page']) ? $_GET['page'] : 'home';

// Define a function to check if the current page matches a given page name
function isCurrentPage($pageName) {
    global $activePage;
    return $activePage === $pageName;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="logofinal.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Urbanist&display=swap" rel="stylesheet">
    <title>Personnel Home Page</title>
    <script src="https://kit.fontawesome.com/6c1b1b5263.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="styles/personnelhome.css">
</head>
<style>
        .highlighted-text {
            background-color: yellow;
        }

        .bordered-container {
            border: 2px solid black; /* Add a border to the container */
            padding: 10px; /* Add some padding for better spacing */
            max-width: 430px;
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th, td {
            padding: 8px;
        }
    </style>
<script>
    document.addEventListener("DOMContentLoaded", function() {
      const userDropdown = document.querySelector(".arrow-down");
      const dropdownContent = document.querySelector(".dropdown-content");

      userDropdown.addEventListener("click", function(event) {
        dropdownContent.style.display = (dropdownContent.style.display === "block") ? "none" : "block";
        event.stopPropagation();
      });

      // Close the dropdown when clicking outside
      window.addEventListener("click", function(event) {
        if (!event.target.matches(".arrow-down")) {
          dropdownContent.style.display = "none";
        }
      });
    });

    document.addEventListener("DOMContentLoaded", function() {
    const submenus = document.querySelectorAll(".has-submenu");

    submenus.forEach(submenu => {
      const submenuToggle = submenu.querySelector("a");
      const submenuContent = submenu.querySelector(".submenu");

      submenuToggle.addEventListener("click", function(event) {
        event.preventDefault();
        submenuContent.classList.toggle("show-submenu");
      });
    });

    // Close the submenus when clicking outside
    window.addEventListener("click", function(event) {
      submenus.forEach(submenu => {
        const submenuContent = submenu.querySelector(".submenu");
        if (!event.target.closest(".has-submenu") && submenuContent.classList.contains("show-submenu")) {
          submenuContent.classList.remove("show-submenu");
        }
      });
    });
  });
  </script>
<body  class="<?php echo $activePage; ?>">
    <nav class="main-nav">
        <img src="logofinal.png" alt="ipptlogo">
        <ul class="main-nav-ul">
            <li><a href="personnelhome.php" class="<?php echo isCurrentPage('home') ? 'active' : ''; ?>"><i class='fas fa-home' style="font-size: 20px; color:#105e4e;"></i> Home</a></li>
            <li class="has-submenu">
                <a href="#" class="<?php echo isCurrentPage('servicerequest') || isCurrentPage('servicerequest') || isCurrentPage('servicerequesthistory') ? 'active' : ''; ?>"><i class='far fa-calendar-alt' style="font-size: 20px; color:#76ffa2;"></i> Service Request</a>
                <ul class="submenu" id="sr-submenu">
                    <li><a href="personnelSRrequest.php" class="<?php echo isCurrentPage('servicerequest') ? 'active' : ''; ?>"><i class="far fa-calendar-check" style="font-size: 20px; color:#76ffa2;"></i> Requests</a></li>
                    <li><a href="personnelSRhistory.php" class="<?php echo isCurrentPage('servicerequesthistory') ? 'active' : ''; ?>"><i class="fas fa-history" style="font-size: 20px; color:#76ffa2;"></i> Request History</a></li>
                </ul>
            </li>
            <li class="has-submenu">
                <a href="#" class="<?php echo isCurrentPage('aboutsystem') || isCurrentPage('aboutdeveloper') ? 'active' : ''; ?>"><i class='fas fa-info-circle' style="font-size: 20px; color:#76ffa2;"></i> About</a>
                <ul class="submenu" id="sr-submenu">
                    <li><a href="personnelaboutsystem.php" class="<?php echo isCurrentPage('aboutsystem') ? 'active' : ''; ?>"><i class="fas fa-question-circle" style="font-size: 20px; color:#76ffa2;"></i> About System</a></li>
                    <li><a href="personnelaboutdeveloper.php" class="<?php echo isCurrentPage('aboutdeveloper') ? 'active' : ''; ?>"><i class="fas fa-question-circle" style="font-size: 20px; color:#76ffa2;"></i> About Developer</a></li>
                </ul>
            </li>
            <li><a href="personnellogout.php" class="<?php echo isCurrentPage('logout') ? 'active' : ''; ?>"><i class='fas fa-sign-out-alt' style="font-size: 20px; color:#76ffa2;"></i> Sign out</a></li>
        </ul>
    </nav>
    <section>
        <div class="container">
        <div class="user-info">
  <div class="user-dropdown">
    <a href="personnelaccount.php"><img class = "user-icon" src="img/account.png" alt="User Icon"></a>
    <div class="arrow-down"></div>
    <div class="dropdown-content">
      <a href="personnelaccount.php"><i class="fas fa-book-open"></i>          Account</a>
      <a href="personnelchangepass.php"><i class="fas fa-book-reader"></i>             Change Password</a>
      <a href="personnellogout.php"><i class='fas fa-sign-out-alt' style="font-size: 20px"></i>         Sign Out</a>
    </div>
  </div>
</div>
<?php
$month = isset($_POST['month']) ? $_POST['month'] : date('m'); 
$year = isset($_POST['year']) ? $_POST['year'] : date('Y'); 
$facultyNumber = $_SESSION['facultynumber'];

$records = [];

$file = fopen('try.csv', 'r');
while (($line = fgetcsv($file)) !== FALSE) {
    if (count($line) > 1) { // Check if the line is not empty
        if ($line[1] === $facultyNumber) {
        $record_datetime = new DateTime($line[3]);
        $record_date = $record_datetime->format('Y-m-d');
        $record_time = $record_datetime->format('h:i');
        $record_month = $record_datetime->format('m');
        $record_year = $record_datetime->format('Y');

        if ($record_month == $month && $record_year == $year) {
        // Assuming line[4] contains status (e.g., "AM Arrival", "AM Departure", etc.)
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
}}
}
fclose($file);

$days_in_month = cal_days_in_month(CAL_GREGORIAN, intval($month), intval($year));

if (empty($records)) {
    echo "<h2>No Records Available</h2>";
} else {
    ?>
    <form action="download.php" method="post">
            <input type="hidden" name="download" value="true">
            <button type="submit">Download DTR</button>
        </form>
        <div class="bordered-container">
        <?php
    echo '<div style="position: relative;">
    <span style="background-color: yellow; position: absolute; top: 0; right: 0;"><strong>PUP-RAGAY.TEMPORARY SUBSTITUTION</strong></span>
</div>
<br>';
    echo "<i>Civil Service Form No. 48</i><br>";
    echo '<div style="text-align: center;"><strong>DAILY TIME RECORD</strong><br>-----o0o-----<br>
</div>';
    echo '<div style="text-align: center; border: 1px solid black; padding: 5px; text-transform: uppercase; font-weight: 800;">
    ' . strtoupper($name) . '<br>
    </div><div style="text-align: center;">(Name)</div><br>';
    echo '<table border="1" style="border-collapse: collapse; width: 100%;">
        <tr>
            <td style="text-align: center;"><i>For the month of:</i></td>
            <td style="text-align: left;"><u><strong>' . strtoupper(date('F', strtotime("$year-$month-01"))) . " 1-$days_in_month, $year</strong></u></td>
        </tr>
      </table><br>";

      echo '<table style="width: 100%;">';
echo '<tr>';
echo '<td style="text-align: center;"><strong>Official hours for <br> arrival and departure</strong></td>';
echo '<td style="text-align: center;">';
echo '<u><strong>Regular days</strong></u><br>';
echo '<u><strong>Saturdays</strong></u><br>';
echo '</td>';
echo '<td style="text-align: center;width: 100px;">';
echo '<br>'; // Empty row
echo '<br>'; // Empty row
echo '</td>';
echo '</tr>';
echo '</table><br>';

      
    echo "<table border='1'>";

    echo "<tr><th rowspan='2'>Date</th><th colspan='2'>AM</th><th colspan='2'>PM</th><th colspan='2'>Undertime</th></tr>";
    echo "<tr><th>Arrival</th><th>Departure</th><th>Arrival</th><th>Departure</th><th>Hours</th><th>Minutes</th></tr>";

    $total_undertime_hours = 0;
    $total_undertime_minutes = 0;

    for ($day = 1; $day <= $days_in_month; $day++) {
        $date = date('Y-m-d', strtotime("$year-$month-$day"));
        $day_of_week = date('w', strtotime($date));
        $day_name = date('l', strtotime($date));

        echo "<tr>";
        echo "<td><strong>" . date('d', strtotime($date)) . " </strong></td>";

        // Check if it's Sunday
        if ($day_of_week == 0) {
            echo "<td colspan='6'>SUN</td>";
            // Add any additional styling or text for Sundays here
        } else {
            // Check if there is a record for this date
            if (isset($records[$date])) {
                // Check if the keys exist before attempting to echo them
                echo isset($records[$date]['AM Arrival']) ? "<td>{$records[$date]['AM Arrival']}</td>" : "<td></td>";
                echo isset($records[$date]['AM Departure']) ? "<td>{$records[$date]['AM Departure']}</td>" : "<td></td>";
                echo isset($records[$date]['PM Arrival']) ? "<td>{$records[$date]['PM Arrival']}</td>" : "<td></td>";
                echo isset($records[$date]['PM Departure']) ? "<td>{$records[$date]['PM Departure']}</td>" : "<td></td>";

                // Display undertime
                if (isset($records[$date]['Undertime'])) {
                    $undertime_hours = $records[$date]['Undertime']['hours'];
                    $undertime_minutes = $records[$date]['Undertime']['minutes'];
                    echo "<td>{$undertime_hours}</td>";
                    echo "<td>{$undertime_minutes}</td>";

                    // Accumulate total undertime
                    $total_undertime_hours += $undertime_hours;
                    $total_undertime_minutes += $undertime_minutes;
                } else {
                    echo "<td></td><td></td>";
                }

            } else {
                echo "<td></td><td></td><td></td><td></td><td></td><td></td>";
            }
        }
        echo "</tr>";
    }

    // Print total undertime
    echo "<tr><td colspan='4'></td><td><strong>Total</stromg></td><td>{$total_undertime_hours}</td><td>{$total_undertime_minutes}</td></tr>";

    echo "</table>";
    echo "<i>I certify on my honor that the above is a true and correct report of the hours of work performed, record of which was made daily at the time of arrival and departure from office.<br><br><br>";
    echo "<div style='text-align: center; border-top: 2px solid black;'><i>VERIFIED as to the prescribed office hours:</i></div><br><br>";
    echo "<i>Verified:</i><br>";
    echo "<table border='1' style='border-collapse: collapse; font-size:18px; width: 100%; text-align: center;'>
        <tr>
            <td><strong>DR. VERONICA S. ALMASE</strong><br>Branch Director<br></td>
        </tr>
      </table>";
      echo "<div style='text-align: center; border-top: 2px solid black;'><i>In Charge</i></div>";
      echo "<div style='text-align: center;'><i>(SEE INSTRUCTION ON BACK)</i></div>";

}

?>

</div>

    </div></div></section>

</body>

</html>


<?php
session_start();
require "connection.php";

 // Check if the user is logged in
 if (!isset($_SESSION['facultynumber'])) {
  header("Location: adminlogin.php"); // Redirect to the login page if not logged in
  exit;
}

// Retrieve the name parameter from the URL
if (isset($_GET['name'])) {
  $name = $_GET['name'];

    // Check connection
    if ($con->connect_error) {
        die("Connection failed: " . $con->connect_error);
    }
  }else {
    echo "Invalid request.";}

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
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Urbanist&display=swap" rel="stylesheet">
  <title>Admin Home Page</title>
  <script src="https://kit.fontawesome.com/6c1b1b5263.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="styles/adminpersonnelinfo.css">
</head>

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
            <li><a href="adminhome.php" class="<?php echo isCurrentPage('home') ? 'active' : ''; ?>"><i class='fas fa-home' style="font-size: 20px; color:#105e4e;"></i> Home</a></li>
            <li><a href="QRgenerator.php" class="<?php echo isCurrentPage('qrgenerator') ? 'active' : ''; ?>"><i class='fas fa-cogs' style="font-size: 20px; color:#76ffa2;"></i> Generate QR Code</a></li>
            <li><a href="adminpersonnelmanagement.php" class="<?php echo isCurrentPage('personnelmanagement') ? 'active' : ''; ?>"><i class='far fa-address-book' style="font-size: 20px;color:#76ffa2;"></i>                       Manage Personnel</a></li>
            <li><a href="adminSRstatus.php" class="<?php echo isCurrentPage('servicerequeststatus') ? 'active' : ''; ?>"><i class='far fa-calendar-alt' style="font-size: 20px; color:#76ffa2;"></i> Service Request</a></li>
            <li class="has-submenu">
                <a href="#" class="<?php echo isCurrentPage('attendancereport') || isCurrentPage('servicerequestreport') ? 'active' : ''; ?>"><i class='fas fa-book' style="font-size: 20px; color:#76ffa2;"></i> Report</a>
                <ul class="submenu" id="sr-submenu">
                    <li><a href="adminARreport.php" class="<?php echo isCurrentPage('attendancereport') ? 'active' : ''; ?>"><i class="fas fa-book-open" style="font-size: 20px; color:#76ffa2;"></i> Attendance Report</a></li>
                    <li><a href="adminSRreport.php" class="<?php echo isCurrentPage('servicerequestreport') ? 'active' : ''; ?>"><i class="fas fa-book-reader" style="font-size: 20px; color:#76ffa2;"></i> Service Request Report</a></li>
                </ul>
            </li>
            <li class="has-submenu">
                <a href="#" class="<?php echo isCurrentPage('aboutsystem') || isCurrentPage('aboutdeveloper') ? 'active' : ''; ?>"><i class='fas fa-info-circle' style="font-size: 20px; color:#76ffa2;"></i> About</a>
                <ul class="submenu" id="sr-submenu">
                    <li><a href="adminaboutsystem.php" class="<?php echo isCurrentPage('aboutsystem') ? 'active' : ''; ?>"><i class="fas fa-question-circle" style="font-size: 20px; color:#76ffa2;"></i> About System</a></li>
                    <li><a href="adminaboutdeveloper.php" class="<?php echo isCurrentPage('aboutdeveloper') ? 'active' : ''; ?>"><i class="fas fa-question-circle" style="font-size: 20px; color:#76ffa2;"></i> About Developer</a></li>
                </ul>
            </li>
        </ul>
    </nav>
  <section>
  <div class="container">
  <div class="info">
  <?php
      // Retrieve the detailed information for the selected name
      $sql = "SELECT name, office, status FROM attendance2 WHERE name = '$name'";
      $result = $con->query($sql);

    if ($result->num_rows > 0) {
        // Display the detailed information
        $row = $result->fetch_assoc();
        $name = $row['name'];
        $office = $row['office'];
        $status = $row['status'];
    
        if ($status === 'AM Arrival') {
            $color = '<span style="color:green; font-size: 30px;">&#9679;</span>'; // Green circle for Time In
        } elseif ($status === 'AM Departure') {
            $color = '<span style="color:red; font-size: 30px;">&#9679;</span>'; // Red circle for Time Out
        }elseif ($status === 'AM Arrival') {
          $color = '<span style="color:green; font-size: 30px;">&#9679;</span>';
        }elseif ($status === 'PM Departure') {
        $color = '<span style="color:red; font-size: 30px;">&#9679;</span>'; // Red circle for Time Out
    }
          
        echo '<h1 style="margin-right: 10px;">' . $name . $color,'</h1>';

        echo '<p>' . $office . '</p>';
    } else {
        echo "No information found for the selected name.";
    }
    
    $facultynumber = $_GET['facultynumber'];

    $sql = "SELECT name, facultynumber FROM attendance2 WHERE facultynumber = '$facultynumber'";
                $result = $con->query($sql);

                if ($result->num_rows > 0) {
                    // User found, fetch the data
                    $row = $result->fetch_assoc();
                    $name = $row['name'];
                    $facultynumber = $row['facultynumber'];

                    // Read the "attendance.csv" file
                    $file = fopen("try.csv", "r");
                    $found = false;

                    if ($file) {
                        $currentDate = date('Y-m-d', strtotime('-9 hours'));
                        echo "<h2>Attendance for $currentDate</h2>";

                        echo "<table>";
                        echo "<th>Date/Time</th><th>Status</th></tr>";

                        $totalTimeInSeconds = 0;
                        $previousTime = null;

                        while (($row = fgetcsv($file)) !== false) {
                          // Check if the row has the expected number of elements
                          if (count($row) >= 5) {
                              $csvName = $row[0];
                              $csvFacultyNumber = $row[1];
                              $office = $row[2];
                              $timeInOut = $row[3];
                              $status = $row[4];
                              $date = date('Y-m-d', strtotime($timeInOut));
                      
                              // Check if the CSV row matches the logged-in account and specific day
                              if ($csvName == $name && $csvFacultyNumber == $facultynumber && $date == $currentDate) {
                                  if ($status == 'AM Arrival') {
                                      $previousTime = strtotime($timeInOut);
                                  } elseif ($status == 'AM Departure' && $previousTime !== null) {
                                      $currentTime = strtotime($timeInOut);
                                      $durationInSeconds = $currentTime - $previousTime;
                                      $totalTimeInSeconds += $durationInSeconds;
                                      $previousTime = null; // Reset the previous time
                                  } elseif ($status == 'PM Arrival') {
                                      $previousTime = strtotime($timeInOut);
                                  } elseif ($status == 'PM Departure' && $previousTime !== null) {
                                      $currentTime = strtotime($timeInOut);
                                      $durationInSeconds = $currentTime - $previousTime;
                                      $totalTimeInSeconds += $durationInSeconds;
                                      $previousTime = null; // Reset the previous time
                                  }
                                  echo "<td>$timeInOut</td><td>$status</td></tr>";
                              }
                          }
                      }                      

                        // Calculate total hours, minutes, and seconds from total time in seconds
                        $totalHours = floor($totalTimeInSeconds / 3600); // Get hours
                        $remainingSeconds = $totalTimeInSeconds % 3600;
                        $totalMinutes = floor($remainingSeconds / 60); // Get minutes
                        $totalSeconds = $remainingSeconds % 60; // Get seconds

                        // Display the total time in hours, minutes, and seconds
                        echo "<p>Total Time: $totalHours hours, $totalMinutes minutes, $totalSeconds seconds</p>";
                        echo "</table>";
                        fclose($file);
                    } else {
                        echo "Failed to open the attendance file.";
                    }
                } else {
                    echo "No attendance data found for the logged-in account.";
                }
                echo "</div>";

  // Retrieve the name from the database
  $nameSql = "SELECT fname, mname, lname FROM admin";
  $nameResult = $con->query($nameSql);

  if ($nameResult->num_rows > 0) {
    $nameRow = $nameResult->fetch_assoc();
    $fname = $nameRow['fname'];
    $mname = $nameRow['mname'];
    $lname = $nameRow['lname'];
    
    $name = "$fname $mname $lname";}
   ?>
</div>
<div class="user-info">
  <div class="user-dropdown">
  <div class="username"><?php echo $name; ?></div>
    <a href="adminhome.php"><img class = "user-icon" src="img/account.png" alt="User Icon"></a>
    <div class="arrow-down"></div>
    <div class="dropdown-content">
      <a href="adminaccount.php"><i class="fas fa-book-open"></i>          Account</a>
      <a href="adminchangepass.php"><i class="fas fa-book-reader"></i>             Change Password</a>
      <a href="adminlogout.php"><i class='fas fa-sign-out-alt' style="font-size: 20px"></i>         Sign Out</a>
    </div>
  </div>
</div></div>
  </section>
</body>
</html>
                
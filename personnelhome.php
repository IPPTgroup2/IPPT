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
            <li><a href="personnelDTR.php" class="<?php echo isCurrentPage('dtr') ? 'active' : ''; ?>"><i class='fas fa-home' style="font-size: 20px; color:#76ffa2;"></i> Daily Time Record</a></li>
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
            // Retrieve the currently logged-in user's information
            if (isset($_SESSION['facultynumber'])) {
                $facultynumber = $_SESSION['facultynumber'];

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
                      echo "<h1>$name ($facultynumber)</h1>";
                      // Get the current date
                      $currentDate = date('Y-m-d', strtotime('-9 hours'));
                      echo "<h2>Attendance for $currentDate</h2>";
                      ?>
                      <div class="table-container">
                          <?php
                          echo "<table>";
                          echo "<th>Date/Time</th><th>Status</th></tr>";
                  
                          $totalTimeInSeconds = 0;
                          $previousTime = null;
                  
                          // Define variables to store timestamps for different statuses
                          $amArrival = null;
                          $amDeparture = null;
                          $pmArrival = null;
                          $pmDeparture = null;
                  
                          while (($row = fgetcsv($file)) !== false) {
                            if (count($row) < 5) {
                                continue; // Skip empty or incomplete rows
                            }
                        
                            $csvName = $row[0];
                            $csvFacultyNumber = $row[1];
                            $office = $row[2];
                            $timeInOut = $row[3];
                            $status = $row[4];
                            $date = date('Y-m-d', strtotime($timeInOut));
                        
                            // Check if the CSV row matches the logged-in account and specific day
                            if ($csvName == $name && $csvFacultyNumber == $facultynumber && $date == $currentDate) {
                                if ($status == 'AM Arrival') {
                                    $amArrival = strtotime($timeInOut);
                                } elseif ($status == 'AM Departure') {
                                    $amDeparture = strtotime($timeInOut);
                                } elseif ($status == 'PM Arrival') {
                                    $pmArrival = strtotime($timeInOut);
                                } elseif ($status == 'PM Departure') {
                                    $pmDeparture = strtotime($timeInOut);
                                }
                                echo "<td>$timeInOut</td><td>$status</td></tr>";
                            }
                        }                        
                  
                          // Calculate total working hours
                          if ($amArrival && $amDeparture) {
                              $totalTimeInSeconds += $amDeparture - $amArrival;
                          }
                          if ($pmArrival && $pmDeparture) {
                              $totalTimeInSeconds += $pmDeparture - $pmArrival;
                          }
                  
                          // Convert total seconds to hours, minutes, and seconds
                          $totalHours = floor($totalTimeInSeconds / 3600);
                          $totalMinutes = floor(($totalTimeInSeconds % 3600) / 60);
                          $totalSeconds = $totalTimeInSeconds % 60;
                  
                          // Display total working hours
                          echo "<p>Total Time: $totalHours hours, $totalMinutes minutes, $totalSeconds seconds</p>";
                          echo "</table>";
                          fclose($file);
                      } else {
                          echo "Failed to open the attendance file.";
                      }
                  } else {
                      echo "No attendance data found for the logged-in account.";
                  }
            } else {
                echo "User not logged in.";
            }

            // Close the database connection
            $con->close();
            ?>
        
            </div>
            
            <a href="QRscanner.php"><button type="button" class="scan">Scan</a></button>
        </div>
    </section>
</body>

</html>
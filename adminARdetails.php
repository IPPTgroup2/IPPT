<?php
session_start();
require('connection.php');

 // Check if the user is logged in
 if (!isset($_SESSION['facultynumber'])) {
    header("Location: adminlogin.php"); // Redirect to the login page if not logged in
    exit;
  }

  
// Set the current page based on the query parameter or the actual page name
$activePage = isset($_GET['page']) ? $_GET['page'] : 'attendancereport';

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
    <title>Attendance Report</title>
    <script src="https://kit.fontawesome.com/6c1b1b5263.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="styles/adminattendancedetail.css">
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
            <li><a href="adminhome.php" class="<?php echo isCurrentPage('home') ? 'active' : ''; ?>"><i class='fas fa-home' style="font-size: 20px; color:#76ffa2;"></i> Home</a></li>
            <li><a href="QRgenerator.php" class="<?php echo isCurrentPage('qrgenerator') ? 'active' : ''; ?>"><i class='fas fa-cogs' style="font-size: 20px; color:#76ffa2;"></i> Generate QR Code</a></li>
            <li><a href="adminpersonnelmanagement.php" class="<?php echo isCurrentPage('personnelmanagement') ? 'active' : ''; ?>"><i class='far fa-address-book' style="font-size: 20px;color:#76ffa2;"></i>                       Manage Personnel</a></li>
            <li><a href="adminSRstatus.php" class="<?php echo isCurrentPage('servicerequeststatus') ? 'active' : ''; ?>"><i class='far fa-calendar-alt' style="font-size: 20px; color:#76ffa2;"></i> Service Request</a></li>
            <li class="has-submenu">
                <a href="#" class="<?php echo isCurrentPage('attendancereport') || isCurrentPage('servicerequestreport') ? 'active' : ''; ?>"><i class='fas fa-book' style="font-size: 20px; color:#105e4e;"></i> Report</a>
                <ul class="submenu" id="sr-submenu">
                    <li><a href="adminARreport.php" class="<?php echo isCurrentPage('attendancereport') ? 'active' : ''; ?>"><i class="fas fa-book-open" style="font-size: 20px; color:#105e4e;"></i> Attendance Report</a></li>
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

    <?php
$adjustedDate = date('Y-m-d h:i:s', strtotime('-9 hours'));
echo $adjustedDate;
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
</div>
   

        <div class="filter-options">
            <form action="" method="post">
                <!-- Filter by options -->
                <label for="filter"></label>
                <select id="filter" name="filter">
                    <option value="all" <?php echo isset($_POST['filter']) && $_POST['filter'] === 'all' ? 'selected' : ''; ?>>All</option>
                    <option value="today" <?php echo isset($_POST['filter']) && $_POST['filter'] === 'today' ? 'selected' : ''; ?>>Today</option>
                    <option value="daily" <?php echo isset($_POST['filter']) && $_POST['filter'] === 'daily' ? 'selected' : ''; ?>>Daily</option>
                    <option value="weekly" <?php echo isset($_POST['filter']) && $_POST['filter'] === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                    <option value="monthly" <?php echo isset($_POST['filter']) && $_POST['filter'] === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                </select>
                <div class="select">
                <?php if (isset($_POST['filter']) && $_POST['filter'] === 'daily') { ?>
                    <label for="dailyDate"></label>
                    <input type="date" id="dailyDate" name="dailyDate" value="<?php echo isset($_POST['dailyDate']) ? $_POST['dailyDate'] : $today; ?>">
                <?php } ?>

                <?php if (isset($_POST['filter']) && $_POST['filter'] === 'weekly') { ?>
                    <label for="weeklyStartDate"></label>
                    <input type="date" id="weeklyStartDate" name="weeklyStartDate" value="<?php echo isset($_POST['weeklyStartDate']) ? $_POST['weeklyStartDate'] : $today; ?>">
                <?php } ?>

                <?php if (isset($_POST['filter']) && $_POST['filter'] === 'monthly') { ?>
                    <label for="monthlyDate"></label>
                    <input type="month" id="monthlyDate" name="monthlyDate" value="<?php echo isset($_POST['monthlyDate']) ? $_POST['monthlyDate'] : $today; ?>">
                <?php } ?>
                </div>
                <button type="submit" name="applyFilter"><i class="fa fa-search"></i></button>
            </form>
        </div><br>

<?php

       $filterOption = 'all';
       $filterOption = isset($_GET['filter']) ? $_GET['filter'] : 'all';
       $selectedName = isset($_GET['name']) ? $_GET['name'] : '';
   
       ?>
       <br>
       <?php
       echo $selectedName;?><br><br>
<?php
            // Read the CSV file and process data
            $csvFile = 'try.csv';
            $csvData = readCSV($csvFile);
            $filteredData = [];

            if (isset($_POST['applyFilter'])) {
                $filterOption = $_POST['filter'];
                $filteredData = $csvData;
            
                // Apply filter logic based on the selected option
                if ($filterOption === 'today') {
                    // Filter records for today's date (adjusted for the timezone offset)
                    $currentDateMinusNineHours = date('Y-m-d H:i:s', strtotime('-9 hours'));
                    $filteredData = array_filter($filteredData, function ($row) use ($currentDateMinusNineHours) {
                        return date('Y-m-d', strtotime($row[3])) === date('Y-m-d', strtotime($currentDateMinusNineHours));
                    });
                }
                else if ($filterOption === 'daily') {
                    if (isset($_POST['dailyDate'])) {
                        $selectedDate = $_POST['dailyDate'];
                        $filteredData = array_filter($filteredData, function ($row) use ($selectedDate) {
                            return date('Y-m-d', strtotime($row[3])) === $selectedDate;
                        });
                    } else {
                        echo "<div class='message'>Please select a date.</div>";
                        return;
                    }
                } elseif ($filterOption === 'weekly') {
                    if (isset($_POST['weeklyStartDate'])) {
                        $weeklyStartDate = $_POST['weeklyStartDate'];
                        $weeklyEndDate = date('Y-m-d', strtotime($weeklyStartDate . ' +1 week'));
            
                        $filteredData = array_filter($filteredData, function ($row) use ($weeklyStartDate, $weeklyEndDate) {
                            $attendanceDate = date('Y-m-d', strtotime($row[3]));
                            return $attendanceDate >= $weeklyStartDate && $attendanceDate < $weeklyEndDate;
                        });
                    } else {
                        echo "<div class='message'>Please select a starting date for the week.</div>";
                        return;
                    }
                } elseif ($filterOption === 'monthly') {
                    if (isset($_POST['monthlyDate'])) {
                        $selectedMonth = $_POST['monthlyDate'];
                        $filteredData = array_filter($filteredData, function ($row) use ($selectedMonth) {
                            $attendanceDate = date('Y-m', strtotime($row[3]));
                            return $attendanceDate === $selectedMonth;
                        });
                    } else {
                        echo "<div class='message'>Please select a month.</div>";
                        return;
                    }
                } else if ($filterOption === 'all') {
                    // No additional filtering needed for 'all'
                }

            if (!empty($filteredData)) {
                echo '<div class="table-container">';
                echo '<table>';
                echo '<tr><th>Date/Time</th><th>Status</th></tr>';
            
                foreach ($filteredData as $row) {
                    $name = $row[0];
                    $timeIn = $row[3];
                    $status = $row[4];
            
                    // Check if the current row's name matches the selected name
                    if ($name === $selectedName) {
                        echo '<tr><td>' . $timeIn . '</td><td>' . $status . '</td></tr>';
                    }
                }
            
                echo '</table>';
                echo '</div>';
            

                $downloadLinkWord = 'adminword.php?format=word&name=' . urlencode($selectedName) . '&filter=' . urlencode($filterOption);
                $downloadLinkExcel = 'adminexcel.php?format=excel&name=' . urlencode($selectedName) . '&filter=' . urlencode($filterOption);
                $downloadLinkPDF = 'adminARdownload.php?format=pdf&name=' . urlencode($selectedName) . '&filter=' . urlencode($filterOption);

            
                if ($filterOption === 'daily') {
                    $downloadLinkWord .= '&date=' . urlencode($_POST['dailyDate']);
                    $downloadLinkExcel .= '&date=' . urlencode($_POST['dailyDate']);
                    $downloadLinkPDF .= '&date=' . urlencode($_POST['dailyDate']);
                } elseif ($filterOption === 'today'){ 
                    $currentDateMinusNineHours = date('Y-m-d H:i:s', strtotime('-9 hours'));
                    $downloadLinkWord .= '&date=' . urlencode($currentDateMinusNineHours);
                    $downloadLinkExcel .= '&date=' . urlencode($currentDateMinusNineHours); 
                    $downloadLinkPDF .= '&date=' . urlencode($currentDateMinusNineHours);  
                } elseif ($filterOption === 'weekly') {
                    $downloadLinkWord .= '&date=' . urlencode($_POST['weeklyStartDate']);
                    $downloadLinkExcel .= '&date=' . urlencode($_POST['weeklyStartDate']);
                    $downloadLinkPDF .= '&date=' . urlencode($_POST['weeklyStartDate']);
                } elseif ($filterOption === 'monthly') {
                    $downloadLinkWord .= '&date=' . urlencode($_POST['monthlyDate']);
                    $downloadLinkExcel .= '&date=' . urlencode($_POST['monthlyDate']);
                    $downloadLinkPDF .= '&date=' . urlencode($_POST['monthlyDate']);
                } elseif ($filterOption === 'all'){
                    $downloadLinkWord .= '&date=all';
                    $downloadLinkExcel .= '&date=all';  
                    $downloadLinkPDF .= '&date=all';    
                }
            
                $matchingRecordsExist = false;
                foreach ($filteredData as $row) {
                    if ($row[0] === $selectedName) {
                        $matchingRecordsExist = true;
                        break;
                    }
                }
            
                if ($matchingRecordsExist) {
                    echo '<div class="download-button">';
echo '<a href="' . $downloadLinkWord . '"><button><i class="fas fa-file-word"></i> Download Word</button></a>';
echo '<a href="' . $downloadLinkExcel . '"><button><i class="fas fa-file-excel"></i> Download Excel</button></a>';
echo '<a href="' . $downloadLinkPDF . '"><button><i class="fas fa-file-pdf"></i> Download PDF</button></a>';
                }
            } else {
                echo "<div class='message'>No matching records found for the selected filter.</div>";
            }
        } else {
                
            $filterOption = isset($_GET['filter']) ? $_GET['filter'] : 'all';
    $selectedName = isset($_GET['name']) ? $_GET['name'] : '';
    echo '<div class="table-container">';
    echo '<table>';
    echo '<tr><th>Date/Time</th><th>Status</th></tr>';

    foreach ($csvData as $row) {
        $name = $row[0];
        $timeIn = $row[3];
        $status = $row[4];

        // Check if the current row's name matches the selected name
        if ($name === $selectedName) {
            echo '<tr><td>' . $timeIn . '</td><td>' . $status . '</td></tr>';
        }
    }

    echo '</table>';
    echo '</div>';
        }
            ?>

        </div>
    </section>
</body>

</html>

<?php
function readCSV($csvFile)
{
    $data = array();

    if (($handle = fopen($csvFile, "r")) !== FALSE) {
        while (($row = fgetcsv($handle, 10000000, ",")) !== FALSE) {
            if (array_filter($row)) {
                $data[] = $row;
            }
        }

        fclose($handle);
    }

    return $data;
}

            ?>

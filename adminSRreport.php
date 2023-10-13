<?php
session_start();
require('connection.php');
require('fpdf/fpdf.php');

 // Check if the user is logged in
 if (!isset($_SESSION['facultynumber'])) {
    header("Location: adminlogin.php"); // Redirect to the login page if not logged in
    exit;
  }
  
// Set the current page based on the query parameter or the actual page name
$activePage = isset($_GET['page']) ? $_GET['page'] : 'servicerequestreport';

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
    <title>Service Request Report</title>
    <script src="https://kit.fontawesome.com/6c1b1b5263.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="styles/adminbookingreport.css">
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
                    <li><a href="adminARreport.php" class="<?php echo isCurrentPage('attendancereport') ? 'active' : ''; ?>"><i class="fas fa-book-open" style="font-size: 20px; color:#76ffa2;"></i> Attendance Report</a></li>
                    <li><a href="adminSRreport.php" class="<?php echo isCurrentPage('servicerequestreport') ? 'active' : ''; ?>"><i class="fas fa-book-reader" style="font-size: 20px; color:#105e4e;"></i> Service Request Report</a></li>
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
   
            <?php
                // Fetch all personnel from the database
                $sql = "SELECT DISTINCT name FROM bookings";
                $result = mysqli_query($con, $sql);

                if (mysqli_num_rows($result) > 0) {
                    $atLeastOneRecord = false; // Flag to track if at least one record is found

                    // Check user inputs for filtering
                    $searchQuery = isset($_POST['personnel-search']) ? mysqli_real_escape_string($con, $_POST['personnel-search']) : '';
                    $reportType = isset($_POST['report-type']) ? $_POST['report-type'] : 'all';

                    // Prepare the WHERE condition based on the filter type
                    $whereCondition = ''; 
                    $specificDate = '';
           
                ?>
                    <!-- Search and filter options -->
                    <div class="filter-options">
                    <form action="" method="post">
                        <label for="personnel-search"></label>
                        <input type="text" name="personnel-search" id="personnel-search" value="<?php echo isset($_POST['personnel-search']) ? htmlspecialchars($_POST['personnel-search']) : ''; ?>">

                        <label for="report-type"></label>
                        <select name="report-type" id="report-type">
                            <option value="all"<?php if ($reportType === 'all') echo ' selected'; ?>>All</option>
                            <option value="today"<?php if ($reportType === 'today') echo ' selected'; ?>>Today</option>
                            <option value="daily"<?php if ($reportType === 'daily') echo ' selected'; ?>>Daily</option>
                            <option value="weekly"<?php if ($reportType === 'weekly') echo ' selected'; ?>>Weekly</option>
                            <option value="monthly"<?php if ($reportType === 'monthly') echo ' selected'; ?>>Monthly</option>
                        </select>
                        <div class="select">
                        <?php if ($reportType === 'daily') { ?>
                            <label for="specific-date"></label>
                            <input type="date" name="specific-date" id="specific-date" value="<?php echo isset($_POST['specific-date']) ? $_POST['specific-date'] : date('Y-m-d'); ?>">
                        <?php } ?>

                        <?php if ($reportType === 'weekly') { ?>
                            <label for="specific-date"></label>
                            <input type="date" name="specific-date" id="specific-date" value="<?php echo isset($_POST['specific-date']) ? $_POST['specific-date'] : date('Y-m-d'); ?>">
                        <?php } ?>

                        <?php if ($reportType === 'monthly') { ?>
                            <label for="specific-date"></label>
                            <input type="month" name="specific-date" id="specific-date" value="<?php echo isset($_POST['specific-date']) ? $_POST['specific-date'] : date('Y-m'); ?>">
                        <?php } ?>
                        </div>

                        <button type="submit" value="Apply Filter"><i class="fa fa-search"></i></button>
                    </form>
                    </div>
                    <?php

                    if ($reportType === 'daily') {
                        if (isset($_POST['specific-date'])) {
                        $specificDate = mysqli_real_escape_string($con, $_POST['specific-date']);
                        $whereCondition .= "AND DATE(date_request) = '$specificDate'";
                        $date = $specificDate;}
                        else {
                            echo "<div class='message'>Please select a date.</div>";}
                    } elseif ($reportType === 'weekly') {
                        if (isset($_POST['specific-date'])) { // Update to the correct form field name
                            $specificDate = mysqli_real_escape_string($con, $_POST['specific-date']); // Use the correct form field
                            $endDate = date('Y-m-d', strtotime($specificDate . ' +1 week')); // Calculate the end date
                            $whereCondition .= "AND date_request >= '$specificDate' AND date_request < '$endDate'";
                            $date = "From $specificDate to $endDate";
                        } else {
                            echo "<div class='message'>Please select a starting date for the week.</div>";
                        }
                    } elseif ($reportType === 'monthly') {
                        if (isset($_POST['specific-date'])) { // Use the correct form field name
                            $specificDate = mysqli_real_escape_string($con, $_POST['specific-date']);
                            $startDate = date('Y-m-01', strtotime($specificDate)); 
                            $endDate = date('Y-m-t', strtotime($specificDate));
                            $whereCondition .= "AND date_request >= '$startDate' AND date_request <= '$endDate'";
                            $date = "Month of " . date('F Y', strtotime($specificDate));
                        } else {
                            echo "<div class='message'>Please select a month.</div>";
                        }
                    } elseif ($reportType === 'all') {
                        // Construct WHERE condition for the "All" filter without any constraints
                        $whereCondition .= "AND DATE(date_request) <= CURDATE()"; // Retrieve all records up to current date
                    }elseif ($reportType === 'today') {
                        $today = date('Y-m-d H:i:s', strtotime('-9 hours'));
                        $whereCondition .= "AND date_request >= '$today'";
                        $date = "Today";
                    }
                    echo '<div class="name-item-list">'; 
                    $searchCondition = '';
                    if (!empty($searchQuery)) {
                        $searchCondition = "AND office LIKE '%$searchQuery%'";
                    }

                    if ($reportType === 'all' && !empty($searchQuery)) {
                        $sql = "SELECT DISTINCT office FROM bookings WHERE office LIKE '%$searchQuery%'";
                    } else {
                        $sql = "SELECT DISTINCT office FROM bookings";
                    }
                    
                    $result = mysqli_query($con, $sql);

                    while ($row = mysqli_fetch_assoc($result)) {
                        $office = $row['office'];
                        
                        // Count records for the current personnel based on filter and search conditions
                        $stmt = $con->prepare("SELECT COUNT(*) AS recordCount FROM bookings WHERE office = ? $whereCondition");
                        $stmt->bind_param("s", $office);
                        $stmt->execute();
                        $stmt->bind_result($recordCount);
                        $stmt->fetch();
                        $stmt->close();
                      
                        if ($recordCount > 0) {
                            echo '<div class="name-item">';
                            echo '<a href="adminSRdetails.php?office=' . urlencode($office) . '">' . $office . '</a>';
                            echo '</div>';
                            $atLeastOneRecord = true; // Set the flag to true
                        }
                    }
                    
                    
                    echo '</div>';
// Display download button for all personnel report
if ($atLeastOneRecord) {
    echo '<div class="download-all-button">';
    
    // Word Download Button
    echo '<button><a href="adminwordall2.php?report-type=' . $reportType .'&' . urlencode($whereCondition) . '&specific-date='. $specificDate.'&format=docx" target="_blank">Download Word<i class="fas fa-file-word" style="font-size: 18px;"></i></a></button>';
    
    // Excel Download Button
    echo '<button><a href="adminexcelall2.php?report-type=' . $reportType .'&' . urlencode($whereCondition) . '&specific-date='. $specificDate.'&format=xlsx" target="_blank">Download Excel<i class="fas fa-file-excel" style="font-size: 18px;"></i></a></button>';
    
    // PDF Download Button
    echo '<button><a href="adminSRdownloadAll.php?report-type=' . $reportType .'&' . urlencode($whereCondition) . '&specific-date='. $specificDate.'&format=pdf" target="_blank">Download PDF<i class="fas fa-file-pdf" style="font-size: 18px;"></i></a></button>';
    
    echo '</div>';
} else {
    echo "No service requests.";
}

}
                // Close the result and connection
                mysqli_free_result($result);
                mysqli_close($con);
                ?>
            </div>
        </div>
    </section>
</body>

</html>
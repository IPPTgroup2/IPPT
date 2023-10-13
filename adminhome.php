<?php 
session_start();
require "connection.php";

 // Check if the user is logged in
 if (!isset($_SESSION['facultynumber'])) {
  header("Location: adminlogin.php"); // Redirect to the login page if not logged in
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
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Urbanist&display=swap" rel="stylesheet">
  <title>Admin Home Page</title>
  <script src="https://kit.fontawesome.com/6c1b1b5263.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="styles/adminhome1.css">
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
        <input type="checkbox" id="check">
        <label for="check" class="checkbtn">
          <i class="fas fa-bars" id="btn"></i>
          <i class="fas fa-times" id="cancel"></i>
        </label>
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
  <?php
   $searchName = ""; // Initialize variable for search name
   if (isset($_GET['search'])) {
     $searchName = $_GET['search'];
   } 

   $attendanceSql = "SELECT name,facultynumber, office, status, timein, timeout 
   FROM attendance2 
   WHERE name LIKE '%$searchName%'";

  $attendanceResult = $con->query($attendanceSql);
  
  // Retrieve the name from the database
  $nameSql = "SELECT fname, mname, lname FROM admin";
  $nameResult = $con->query($nameSql);

  if ($nameResult->num_rows > 0) {
    $nameRow = $nameResult->fetch_assoc();
    $fname = $nameRow['fname'];
    $mname = $nameRow['mname'];
    $lname = $nameRow['lname'];
    
    $name = "$fname $mname $lname";
   ?>

<div class="user-info">
  <div class="user-dropdown">
  <div class="username"><?php echo $name; ?></div>
    <a href="adminaccount.php"><img class = "user-icon" src="img/account.png" alt="User Icon"></a>
    <div class="arrow-down"></div>
    <div class="dropdown-content">
      <a href="adminaccount.php"><i class="fas fa-book-open"></i>          Account</a>
      <a href="adminchangepass.php"><i class="fas fa-book-reader"></i>             Change Password</a>
      <a href="adminlogout.php"><i class='fas fa-sign-out-alt' style="font-size: 20px"></i>         Sign Out</a>
    </div>
  </div>
</div>
   
<div class="search-bar">
  <form method="GET" action="">
    <input type="text" name="search" placeholder="Search..." value="<?php echo htmlentities($searchName); ?>">
    <button type="submit"><i class="fa fa-search"></i></button>
  </form>
</div>
  <?php

  } else {
    echo "<p style=\"text-align: center;\">No attendance records found.</p>";
  } ?>
  <div class="table-container">
  <table>
  <thead>
    <tr>
      <th>Name</th>
      <th>Office</th>
      <th>Time</th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody>
    <?php
      if ($attendanceResult->num_rows > 0) {
        while ($row = $attendanceResult->fetch_assoc()) {
          $name = $row['name'];
          $office = $row['office'];
          $status = $row['status'];
          $time = $row['timein'];
          $facultynumber = $row['facultynumber'];
    
        if ($status === 'AM Arrival') {
            $color = '<span style="color:green; font-size: 30px;">&#9679;</span>'; // Green circle for Time In
        } elseif ($status === 'AM Departure') {
            $color = '<span style="color:red; font-size: 30px;">&#9679;</span>'; // Red circle for Time Out
        }elseif ($status === 'AM Arrival') {
          $color = '<span style="color:green; font-size: 30px;">&#9679;</span>';
        }elseif ($status === 'PM Departure') {
        $color = '<span style="color:red; font-size: 30px;">&#9679;</span>'; // Red circle for Time Out
    }   

          echo "<tr>
                  <td><a href=\"adminpersonnelinfo.php?name=" . urlencode($name) . "&facultynumber=$facultynumber\">$name </a></td>
                  <td>$office</td>
                  <td>$time</td>
                  <td>$color</td>
                  
                </tr>";
        }
      } else {
        echo "<tr><td colspan=\"3\" style=\"text-align: center;\">No attendance records found.</td></tr>";
      }
    ?>
  </tbody>
</table>
  </div>
</div>
  </section>
</body>
</html>
<?php 
session_start();
require "connection.php";


 // Check if the user is logged in
 if (!isset($_SESSION['facultynumber'])) {
  header("Location: adminlogin.php"); // Redirect to the login page if not logged in
  exit;
}


// Set the current page based on the query parameter or the actual page name
$activePage = isset($_GET['page']) ? $_GET['page'] : 'aboutsystem';

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
  <title>About System</title>
  <script src="https://kit.fontawesome.com/6c1b1b5263.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="styles/aboutdeveloper.css">
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
                <a href="#" class="<?php echo isCurrentPage('attendancereport') || isCurrentPage('servicerequestreport') ? 'active' : ''; ?>"><i class='fas fa-book' style="font-size: 20px; color:#76ffa2;"></i> Report</a>
                <ul class="submenu" id="sr-submenu">
                    <li><a href="adminARreport.php" class="<?php echo isCurrentPage('attendancereport') ? 'active' : ''; ?>"><i class="fas fa-book-open" style="font-size: 20px; color:#76ffa2;"></i> Attendance Report</a></li>
                    <li><a href="adminSRreport.php" class="<?php echo isCurrentPage('servicerequestreport') ? 'active' : ''; ?>"><i class="fas fa-book-reader" style="font-size: 20px; color:#76ffa2;"></i> Service Request Report</a></li>
                </ul>
            </li>
            <li class="has-submenu">
                <a href="#" class="<?php echo isCurrentPage('aboutsystem') || isCurrentPage('aboutdeveloper') ? 'active' : ''; ?>"><i class='fas fa-info-circle' style="font-size: 20px; color:#105e4e;"></i> About</a>
                <ul class="submenu" id="sr-submenu">
                    <li><a href="adminaboutsystem.php" class="<?php echo isCurrentPage('aboutsystem') ? 'active' : ''; ?>"><i class="fas fa-question-circle" style="font-size: 20px; color:#105e4e;"></i> About System</a></li>
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



<section>
  <div class="about">
<h2> About the System</h2><br><br>
  <p align = justify>
Purpose and Objective
<br>

The IPPT System is designed to streamline the process of scheduling and managing appointments for various services offered by the organization, the Polytechnic University of the Philippines-Ragay Branch. It provides an efficient and convenient way for users to track personnel, request services, and receive notifications about the status of their requests. Furthermore, it generates reports for attendance and service requests that can be used for future reference.
<br><br>

Key Features
<br>
- User-friendly interface for easy navigation.<br>
- Secure login and authentication system to protect user information.<br>
- Real-time tracking of attendance or presence of personnel.<br>
- Intuitive service request form with customizable fields.<br>
- Real-time availability calendar for selecting preferred dates and times.<br>
-Provides notifications for service request confirmation and updates.<br>
- History log to track the status of previous service requests.<br>
- Provides a downloadable pdf format for attendance report and service request report.<br>
<br><br>

Target Audience
<br>

The IPPT System is intended for the following:<br>
-Admin:<br> User with administrative privileges responsible for system management.<br>
-Personnel:<br>Individuals who use the system to log their attendance (time in and time out) and approve service requests.<br>
-Student/Visitors:<br>Users who can identify the presence of personnel real-time. Moreover, can request services offered by the institution and receive notifications regarding of their requests.<br><br>

System Version
<br>
Current Version: 2.0.1
<br><br>

Technologies Used
<br>
- Front-end: HTML5, CSS3, JavaScript<br>
- Back-end: PHP, MySQL<br>
- Frameworks: Bootstrap, jQuery<br>
<br>

System Architecture
<br>
The system follows a client-server architecture. The front-end is built using HTML, CSS, and JavaScript, while the back-end relies on PHP for server-side processing. Data is stored and managed in a MySQL database.
<br><br>

Security Measures
<br>
We adhere to R.A. 10173 also known as Data Privacy Act, to keep your data safe. Our comprehensive approach encompasses encryption measures for sensitive information and routine security audits aimed at identifying and promptly addressing potential vulnerabilities.

<br><br>Contact Information
<br>
For support or inquiries related to the IPPT System, please contact our support team by the following emails indicated below.
<br>
- mayanderamos08@gmail.com<br>
- earlgunay123@gmail.com<br>
- lovelyclaudethldevilla@gmail.com<br>
- nbocago@gmail.com<br>
- laoadrian77@gmail.com<br><br>

Development Team
<br>
- Lovely Claudeth de Villa (Project Manager)<br>
- May-an D. de Ramos (Software Developer)<br>
- Earl Angelo M. Gunay (UI/UX Designer)<br>
- Norivien Jane T. Bocago (diko knows role)<br>
- Adrian Lao (Data Gatherer)<br><br>

Legal Information
<br>
This system is legally protected by copyright law, and any unauthorized reproduction, distribution, or use is strictly prohibited and subject to legal consequences<br></p>
  </div></div>
</section>
</body>
</html>
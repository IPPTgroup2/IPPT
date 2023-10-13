<?php
session_start();
require "connection.php";

// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

 // Check if the user is logged in
 if (!isset($_SESSION['facultynumber'])) {
  header("Location: adminlogin.php"); // Redirect to the login page if not logged in
  exit;
}

// Set the current page based on the query parameter or the actual page name
$activePage = isset($_GET['page']) ? $_GET['page'] : 'servicerequeststatus';

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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Urbanist&display=swap" rel="stylesheet">
    <title>Service Requests</title>
    <script src="https://kit.fontawesome.com/6c1b1b5263.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="styles/adminbookingstatus.css">
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
            <li><a href="adminSRstatus.php" class="<?php echo isCurrentPage('servicerequeststatus') ? 'active' : ''; ?>"><i class='far fa-calendar-alt' style="font-size: 20px; color:#105e4e;"></i> Service Request</a></li>
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
        <div class="table-container">  
        <?php
        $searchOffice='';
        
        if (isset($_SESSION['email']) && isset($_SESSION['facultynumber'])) {
            $email = $_SESSION['email'];
        
            // Check if search parameter is set
            if (isset($_GET['search'])) {
                $searchOffice = $_GET['search'];
        
                $sql = "SELECT DISTINCT office, 
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN status = 'declined' THEN 1 ELSE 0 END) as declined_count,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_count,
                SUM(CASE WHEN status = 'accommodated' THEN 1 ELSE 0 END) as accommodated_count,
                SUM(CASE WHEN status = 'not_accommodated' THEN 1 ELSE 0 END) as not_accommodated_count
         FROM bookings 
         WHERE email = '$email' AND office LIKE '%$searchOffice%' 
         GROUP BY office";
} else {
 $sql = "SELECT DISTINCT office, 
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN status = 'declined' THEN 1 ELSE 0 END) as declined_count,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_count,
                SUM(CASE WHEN status = 'accommodated' THEN 1 ELSE 0 END) as accommodated_count,
                SUM(CASE WHEN status = 'not_accommodated' THEN 1 ELSE 0 END) as not_accommodated_count
         FROM bookings 
         GROUP BY office";
}

$result = mysqli_query($con, $sql);

if (mysqli_num_rows($result) > 0) {
 while ($row = mysqli_fetch_assoc($result)) {
     $office = $row["office"];
     $pending_count = $row["pending_count"];
     $declined_count = $row["declined_count"];
     $approved_count = $row["approved_count"];
     $accommodated_count = $row["accommodated_count"];
     $not_accommodated_count = $row["not_accommodated_count"];

     echo "<div class='office-item'>";
            echo "<span class='office-name'>$office</span>";
            echo "<div class='status-icons'>";

            // Wrap each icon and count in a container
            echo "<div class='status-icon-container'><a href='adminpending.php?office=" . urlencode($office) . "&status=pending'><div class='icon-count'>$pending_count</div><i class='fas fa-clock' title='Pending'></i></a></div>";
            echo "<div class='status-icon-container'><a href='admindeclined.php?office=" . urlencode($office) . "&status=declined'><div class='icon-count'>$declined_count</div><i class='fas fa-times' title='Declined'></i></a></div>";
            echo "<div class='status-icon-container'><a href='adminapproved.php?office=" . urlencode($office) . "&status=approved'><div class='icon-count'>$approved_count</div><i class='fas fa-check' title='Approved'></i></a></div>";
            echo "<div class='status-icon-container'><a href='adminaccommodated.php?office=" . urlencode($office) . "&status=accommodated'><div class='icon-count'>$accommodated_count</div><i class='fas fa-bed' title='Accommodated'></i></a></div>";
            echo "<div class='status-icon-container'><a href='adminnotaccommodated.php?office=" . urlencode($office) . "&status=notaccommodated'><div class='icon-count'>$not_accommodated_count</div><i class='fas fa-ban' title='Not Accommodated'></i></a></div>";
            echo "</div>";
            echo "</div>";
              }
          } else {
              echo "<p>No matching booking history.</p>";}
        } else {
            echo "User not logged in or session data not available.";
        }
        ?>
</div>
   
<div class="search-bar">
  <form method="GET" action="">
    <input type="text" name="search" placeholder="Search..." value="<?php echo htmlentities($searchOffice); ?>">
    <button type="submit"><i class="fa fa-search"></i></button>
  </form>
</div>
            <?php
            
  // Retrieve the name from the database
  $nameSql = "SELECT fname, mname, lname FROM admin";
  $nameResult = $con->query($nameSql);

  if ($nameResult->num_rows > 0) {
    $nameRow = $nameResult->fetch_assoc();
    $fname = $nameRow['fname'];
    $mname = $nameRow['mname'];
    $lname = $nameRow['lname'];
    
    $adminname = "$fname $mname $lname";}
   ?>

<div class="user-info">
  <div class="user-dropdown">
  <div class="username"><?php echo $adminname; ?></div>
    <a href="adminhome.php"><img class = "user-icon" src="img/account.png" alt="User Icon"></a>
    <div class="arrow-down"></div>
    <div class="dropdown-content">
      <a href="adminaccount.php"><i class="fas fa-book-open"></i>          Account</a>
      <a href="adminchangepass.php"><i class="fas fa-book-reader"></i>             Change Password</a>
      <a href="adminlogout.php"><i class='fas fa-sign-out-alt' style="font-size: 20px"></i>         Sign Out</a>
    </div>
  </div>
</div>
  </div>
    </section>
</body>

</html>
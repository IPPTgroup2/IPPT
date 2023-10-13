<?php
session_start();
require "connection.php";

 // Check if the user is logged in
 if (!isset($_SESSION['facultynumber'])) {
  header("Location: adminlogin.php"); // Redirect to the login page if not logged in
  exit;
}


// Function to validate password length
function validatePassword($password) {
  return strlen($password) >= 6;
}

if (isset($_GET['fname']) && isset($_GET['lname']) && isset($_GET['facultynumber'])) {
  $fname = urldecode($_GET['fname']);
  $lname = urldecode($_GET['lname']);
  $facultynumber = urldecode($_GET['facultynumber']);

  // Prepare and execute the query with placeholders for retrieval
  $personnelSql = "SELECT * FROM personnel WHERE fname=? AND lname=? AND facultynumber=?";
  $stmt = mysqli_prepare($con, $personnelSql);

  if ($stmt) {
      // Bind parameters
      mysqli_stmt_bind_param($stmt, "sss", $fname, $lname, $facultynumber);

      // Execute the statement
      mysqli_stmt_execute($stmt);

      $personnelResult = mysqli_stmt_get_result($stmt);

      if ($personnelResult->num_rows > 0) {
          $personnelRow = $personnelResult->fetch_assoc();
          // Retrieve relevant information for the edit form
          $type = $personnelRow['type'];
          $fname = $personnelRow['fname'];
          $mname = $personnelRow['mname'];
          $lname = $personnelRow['lname'];
          $email = $personnelRow['email'];
          $facultynumber = $personnelRow['facultynumber'];
          $office = $personnelRow['office'];
          $password = $personnelRow['password'];
          $information = $personnelRow['information'];
      } else {
          echo "<p>Personnel not found.</p>";
          exit;
      }

      mysqli_stmt_close($stmt);
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $type = $_POST['type'];
      $newfname = $_POST['fname'];
      $mname = $_POST['mname'];
      $newlname = $_POST['lname'];
      $email = $_POST['email'];
      $facultynumber = $_POST['facultynumber'];
      $office = $_POST['office'];
      $password = $_POST['password'];
      $information = $_POST['information'];

      // Check password length
      if (!validatePassword($password)) {
          $_SESSION['error_message'] = "Password must be at least 6 characters long.";
      } else {
          // Prepare and execute the query with placeholders to prevent SQL injection
          $query = "UPDATE personnel SET fname=?, mname=?, lname=?, email=?, facultynumber=?, office=?, password=?, information=?, type=? WHERE fname=? AND lname=?";
          $stmt = mysqli_prepare($con, $query);

          if ($stmt) {
              // Hash the password for security (You may need a better hashing method in production)
              $hashed_password = password_hash($password, PASSWORD_DEFAULT);

              // Bind parameters to the prepared statement
              mysqli_stmt_bind_param($stmt, "sssssssssss", $newfname, $mname, $newlname, $email, $facultynumber,$office, $hashed_password, $information, $type, $fname, $lname);

              // Execute the statement
              if (mysqli_stmt_execute($stmt)) {
                  header("Location: adminpersonnelmanagement.php?fname=" . urlencode($fname) . "&lname=" . urlencode($lname));
                  exit();
              } else {
                  $_SESSION['error_message'] = "Error updating personnel information: " . mysqli_error($con);
              }

              mysqli_stmt_close($stmt);
          } else {
              $_SESSION['error_message'] = "Error preparing statement: " . mysqli_error($con);
          }
      }
  }
}

// Set the current page based on the query parameter or the actual page name
$activePage = isset($_GET['page']) ? $_GET['page'] : 'personnelmanagement';

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
    <title>Edit Personnel Information</title>
    <script src="https://kit.fontawesome.com/6c1b1b5263.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="styles/admineditpersonnelinfo.css">
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
            <li><a href="adminpersonnelmanagement.php" class="<?php echo isCurrentPage('personnelmanagement') ? 'active' : ''; ?>"><i class='far fa-address-book' style="font-size: 20px;color:#105e4e;"></i>                       Manage Personnel</a></li>
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
            <!-- Check if there's an error message in the session -->
            <?php if (isset($_SESSION['error_message'])) : ?>
                <p style="color: red;"><?php echo $_SESSION['error_message']; ?></p>
                <?php unset($_SESSION['error_message']); // Unset the error message once displayed 
                ?>
            <?php endif; ?>

            <!-- Check if there's an success message in the session -->
            <?php if (isset($_SESSION['success_message'])) : ?>
                <p style="color: green;"><?php echo $_SESSION['success_message']; ?></p>
                <?php unset($_SESSION['success_message']); // Unset the error message once displayed 
                ?>
            <?php endif; ?>

            <div class="form">
            <form method="post">
                <!-- Add form fields here to edit the personnel information -->
                <div class="label">
                Type: <input type="text" name="type" value="<?php echo $type; ?>">
                </div>
                <br>
                <div class="label">
                First Name: <input type="text" name="fname" value="<?php echo $fname; ?>">
                </div>
                <br>
                <div class="label">
                Middle Name: <input type="text" name="mname" value="<?php echo $mname; ?>">
                </div><br>
                <div class="label">
                Last Name: <input type="text" name="lname" value="<?php echo $lname; ?>"></div><br>
                <div class="label">
                Email: <input type="email" name="email" value="<?php echo $email; ?>"></div><br>
                <div class="label">
                Faculty Number: <input type="text" name="facultynumber" value="<?php echo $facultynumber; ?>"></div><br>
                <div class="label">
                Office: <input type="text" name="office" value="<?php echo $office; ?>"></div><br>
                <div class="label">
                Password: <input type="password" name="password" value="<?php echo $password; ?>"></div><br>
                <div class="label">
                Information: <textarea name="information"><?php echo $information; ?></textarea></div><br>
              
                <div class="update-btn">
                <input type="submit" value="Update"></div>
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
    
    $name = "$fname $mname $lname";
  }
  
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
        </div>

    </section>
</body>

</html>
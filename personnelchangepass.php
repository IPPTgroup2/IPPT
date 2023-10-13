<?php
session_start();
$errors = array();
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Urbanist&display=swap" rel="stylesheet">
    <title>Change Password</title>
    <script src="https://kit.fontawesome.com/6c1b1b5263.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="styles/personnelchangepass.css">
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
  
  <section>
    <?php

if (isset($_SESSION['facultynumber'])) {
     $facultynumber = $_SESSION['facultynumber'];

 $nameSql = "SELECT fname, mname, lname FROM personnel WHERE facultynumber = '$facultynumber'";
 $nameResult = $con->query($nameSql);

 if ($nameResult->num_rows > 0) {
   $nameRow = $nameResult->fetch_assoc();
   $fname = $nameRow['fname'];
   $mname = $nameRow['mname'];
   $lname = $nameRow['lname'];

 $name = "$fname $mname $lname";
 }}?>
   <div class="container">
       <div class="user-info">
 <div class="user-dropdown">
 <div class="username"><?php echo $name; ?></div>
    <a href="personnelhome.php"><img class = "user-icon" src="img/account.png" alt="User Icon"></a>
    <div class="arrow-down"></div>
    <div class="dropdown-content">
      <a href="personnelaccount.php"><i class="fas fa-book-open"></i>          Account</a>
      <a href="personnellogout.php"><i class='fas fa-sign-out-alt' style="font-size: 20px"></i>         Sign Out</a>
    </div>
  </div>
</div>


<?php


    //if user click change password button
    if(isset($_POST['change-password'])){
      $_SESSION['info'] = "";
      $password = mysqli_real_escape_string($con, $_POST['password']);
      $cpassword = mysqli_real_escape_string($con, $_POST['cpassword']);

      // Validate the input fields
  if (empty($password)) {
      $errors[] = "Password is required.";
  } 
      if($password !== $cpassword){
          $errors['password'] = "Confirm password not matched!";
      }elseif (strlen($password) < 6) {
      $errors[] = "Password should be at least 6 characters long.";
      }
      else{
          $code = 0;
          $facultynumber = $_SESSION['facultynumber']; //getting this email using session
          $encpass = password_hash($password, PASSWORD_BCRYPT);
          $update_pass = "UPDATE personnel SET  password = '$password' WHERE facultynumber = '$facultynumber'";
          $run_query = mysqli_query($con, $update_pass);
          if($run_query){
              $info = "Your password has been changed. Now you can login with your new password.";
              $_SESSION['info'] = $info;
              header('Location: personnelhome.php');
          }else{
              $errors['db-error'] = "Failed to change your password!";
          }
      }
  }
            ?>
            <h2>Change Password</h2>
            <form action="personnelchangepass.php" method="POST" autocomplete="">
                <?php
                if (count($errors) > 0) {
                ?>
                    <div class="alert alert-danger text-center">
                        <?php
                        foreach ($errors as $error) {
                            echo $error;
                        }
                        ?>
                    </div>
                <?php
                }
                ?>
                <div class="txtfield">
                    <input type="password" name="password" id="password" placeholder="Create a new password" required>
                </div>
                <div class="txtfield">
                    <input type="password" name="cpassword" id="password" placeholder="Confirm your password" required>
                </div>
                <input class="button" type="submit" name="change-password" value="Change">
        </div>
        </form>
    </section>

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

</body>

</html>
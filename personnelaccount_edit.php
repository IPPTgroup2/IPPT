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

if (isset($_SESSION['facultynumber'])) {
  $facultynumber = $_SESSION['facultynumber'];

  $sql = "SELECT * FROM personnel WHERE facultynumber = '$facultynumber'";
  $result = mysqli_query($con, $sql);

  if ($result && mysqli_num_rows($result) > 0) {
      $row = mysqli_fetch_assoc($result);
      $fname = $row["fname"];
      $mname = $row["mname"];
      $lname = $row["lname"];
      $email = $row["email"];
  } else {
      echo "Personnel information not found.";
      exit();
  }

  if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_account'])) {
      $newFname = mysqli_real_escape_string($con, $_POST['fname']);
      $newMname = mysqli_real_escape_string($con, $_POST['mname']);
      $newLname = mysqli_real_escape_string($con, $_POST['lname']);
      $newEmail = mysqli_real_escape_string($con, $_POST['email']);

      $update_query = "UPDATE personnel SET fname = '$newFname', mname = '$newMname', lname = '$newLname', email = '$newEmail' WHERE facultynumber = '$facultynumber'";
      $update_result = mysqli_query($con, $update_query);

      if ($update_result) {
          $_SESSION['email'] = $newEmail;
          header("Location: personnelaccount.php");
          exit();
      } else {
          echo "Error updating account information: " . mysqli_error($con);
      }
  }
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
    <title>Personnel Home Page</title>
    <script src="https://kit.fontawesome.com/6c1b1b5263.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="styles/personnelaccountedit.css">
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
    <a href="personnelhome.php"><img class = "user-icon" src="img/account.png" alt="User Icon"></a>
    <div class="arrow-down"></div>
    <div class="dropdown-content">
      <a href="personnelchangepass.php"><i class="fas fa-book-reader"></i>             Change Password</a>
      <a href="personnellogout.php"><i class='fas fa-sign-out-alt' style="font-size: 20px"></i>         Sign Out</a>
    </div>
  </div>
</div>

<form action="personnelaccount_edit.php" method="post">
<div class='info'>
                <p><strong>First Name:</strong> 
                    <input type="text" name="fname" value="<?php echo  $fname; ?>" required>
                </p>
                <p>
                    <strong>Middle Name:</strong> <input type="text" name="mname" value="<?php echo $mname; ?>" required>
                </p>
                <p>
                    <strong>Last Name:</strong> <input type="text" name="lname" value="<?php echo $lname; ?>" required>
                </p>
                <p>
                    <strong>Faculty Number:</strong> <?php echo $facultynumber; ?>
                </p>
                <p>
                    <strong>Email:</strong>
                    <input type="email" name="email" value="<?php echo $email; ?>" required>
                </p>
            </div>
                <button type="submit" name="update_account">Save</button>
            </form>
        </div>
    </section>
</body>

</html>
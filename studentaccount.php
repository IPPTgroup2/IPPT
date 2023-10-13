<?php
session_start();
require "connection.php";


$email = $_SESSION['email'];
if($email == false){
  header('Location: studentlogin.php');
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
    <title>Student Account</title>
    <script src="https://kit.fontawesome.com/6c1b1b5263.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="styles/studentaccount.css">
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
            <li><a href="studenthome.php" class="<?php echo isCurrentPage('home') ? 'active' : ''; ?>"><i class='fas fa-home' style="font-size: 20px; color:#105e4e;"></i> Home</a></li>
            <li class="has-submenu">
            <a href="#" class="<?php echo isCurrentPage('servicerequest') || isCurrentPage('request') || isCurrentPage('servicerequeststatus') || isCurrentPage('servicerequesthistory') ? 'active' : ''; ?>"><i class='far fa-calendar-alt' style="font-size: 20px; color:#76ffa2;"></i> Service Request</a>
                <ul class="submenu" id="sr-submenu">
                    <li><a href="studentSR1.php" class="<?php echo isCurrentPage('request') ? 'active' : ''; ?>"><i class="far fa-calendar-check" style="font-size: 20px; color:#76ffa2;"></i> Request</a></li>
                    <li><a href="studentSRstatus.php" class="<?php echo isCurrentPage('servicerequeststatus') ? 'active' : ''; ?>"><i class="far fa-calendar-check" style="font-size: 20px; color:#76ffa2;"></i> Status</a></li>
                    <li><a href="studentSRhistory.php" class="<?php echo isCurrentPage('servicerequesthistory') ? 'active' : ''; ?>"><i class="fas fa-history" style="font-size: 20px; color:#76ffa2;"></i> Request History</a></li>
                </ul>
            </li>
            <li class="has-submenu">
                <a href="#" class="<?php echo isCurrentPage('aboutsystem') || isCurrentPage('aboutdeveloper') ? 'active' : ''; ?>"><i class='fas fa-info-circle' style="font-size: 20px; color:#76ffa2;"></i> About</a>
                <ul class="submenu" id="sr-submenu">
                    <li><a href="studentaboutsystem.php" class="<?php echo isCurrentPage('aboutsystem') ? 'active' : ''; ?>"><i class="fas fa-question-circle" style="font-size: 20px; color:#76ffa2;"></i> About System</a></li>
                    <li><a href="studentaboutdeveloper.php" class="<?php echo isCurrentPage('aboutdeveloper') ? 'active' : ''; ?>"><i class="fas fa-question-circle" style="font-size: 20px; color:#76ffa2;"></i> About Developer</a></li>
                </ul>
            </li>
            <li><a href="studentlogout.php" class="<?php echo isCurrentPage('logout') ? 'active' : ''; ?>"><i class='fas fa-sign-out-alt' style="font-size: 20px; color:#76ffa2;"></i> Sign out</a></li>
        </ul>
    </nav>
    <section>
        <div class="container">
        <div class="user-info">
  <div class="user-dropdown">
  <?php
  $nameSql = "SELECT fname, mname, lname FROM students WHERE email = '$email'";
  $nameResult = $con->query($nameSql);

  if ($nameResult->num_rows > 0) {
    $nameRow = $nameResult->fetch_assoc();
    $fname = $nameRow['fname'];
    $mname = $nameRow['mname'];
    $lname = $nameRow['lname'];

  $name = "$fname $mname $lname";}
  ?>
  <div class="username"><?php echo $name; ?></div>
    <a href="studenthome.php"><img class = "user-icon" src="img/account.png" alt="User Icon"></a>
    <div class="arrow-down"></div>
    <div class="dropdown-content">
      <a href="studentchangepassword.php"><i class="fas fa-book-reader"></i>             Change Password</a>
      <a href="studentlogout.php"><i class='fas fa-sign-out-alt' style="font-size: 20px"></i>         Sign Out</a>
    </div>
  </div>
</div>

            <?php
            if (isset($_SESSION['email'])) {
                $email = $_SESSION['email'];

                // Fetch the student information from the database based on the email
                $sql = "SELECT * FROM students WHERE email = '$email'";
                $result = mysqli_query($con, $sql);

                if (!$result) {
                    // Debugging: Print the MySQL error message to help identify the problem
                    die('Error: ' . mysqli_error($con));
                }

                if (mysqli_num_rows($result) > 0) {
                    $row = mysqli_fetch_assoc($result);
                    $fname = $row["fname"];
                    $mname = $row["mname"];
                    $lname = $row["lname"];
                    $course = $row["course"];
                    $password = $row["password"];
                    // Display the student information along with the Edit button
            ?>
    <div class="admin-info">
    <a href="studentaccount_edit.php" class="edit-icon"><i class="fas fa-edit"></i></a>
    <h2>Student Information</h2>
    <table class="admin-table">
        <tr>
            <th>Field</th>
            <th>Value</th>
        </tr>
        <tr>
            <td><strong>First Name:</strong></td>
            <td><?php echo $fname; ?></td>
        </tr>
        <tr>
            <td><strong>Middle Name:</strong></td>
            <td><?php echo $mname; ?></td>
        </tr>
        <tr>
            <td><strong>Last Name:</strong></td>
            <td><?php echo $lname; ?></td>
        </tr>
        <tr>
            <td><strong>Course:</strong></td>
            <td><?php echo $course; ?></td>
        </tr>
        <tr>
            <td><strong>Email:</strong></td>
            <td><?php echo $email; ?></td>
        </tr>
    </table>
                </div>
            <?php
                } else {
                    echo "No student information found.";
                }
                // Close the result
                mysqli_free_result($result);
            }

            ?>
        </div>
    </section>
</body>

</html>
<?php
session_start();
require "connection.php";

 // Check if the user is logged in
 if (!isset($_SESSION['facultynumber'])) {
    header("Location: adminlogin.php"); // Redirect to the login page if not logged in
    exit;
  }

  
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname = $_POST['fname'];
    $mname = $_POST['mname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $facultynumber = $_POST['facultynumber'];
    $office = $_POST['office'];
    $password = $_POST['password'];
    $information = $_POST['information'];

    // Check if the password meets the minimum length requirement
    if (strlen($password) < 6) {
        $_SESSION['error_message'] = "Password must be 6 characters or more.";
        header("Location: adminaddpersonnel.php");
        exit();
    }

    // Hash the password for security (You may need a better hashing method in production)
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    // Check if the personnel with the given faculty number or email already exists in the database
    $checkSql = "SELECT * FROM personnel WHERE facultynumber='$facultynumber' OR email='$email'";
    $checkResult = mysqli_query($con, $checkSql);

    if (mysqli_num_rows($checkResult) > 0) {
        $_SESSION['error_message'] = "Personnel with the same Faculty Number or Email already exists.";
        header("Location: adminaddpersonnel.php");
        exit();
    }

    // Prepare and execute the query with placeholders to prevent SQL injection
    $query = "INSERT INTO personnel (type, fname, mname, lname, email, facultynumber,office, password, information) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $query);

    if ($stmt) {
        // Assuming the personnel type is 'Personnel' for this example
        $type = 'Personnel';

        // Bind parameters to the prepared statement
        mysqli_stmt_bind_param($stmt, "sssssssss", $type, $fname, $mname, $lname, $email, $facultynumber, $office, $hashed_password, $information);

        // Execute the statement
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = "Personnel added successfully.";
            header("Location: adminaddpersonnel.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Error adding personnel: " . mysqli_error($con);
        }

        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error_message'] = "Error preparing statement: " . mysqli_error($con);
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
    <title>Personnel Management</title>
    <script src="https://kit.fontawesome.com/6c1b1b5263.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="styles/adminaddpersonnel.css">
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
   
            <!-- Check if there's an error message in the session -->
            <?php if (isset($_SESSION['error_message'])) : ?>
                <p style="color: red;"><?php echo $_SESSION['error_message']; ?></p>
                <?php unset($_SESSION['error_message']); // Unset the error message once displayed 
                ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success_message'])) : ?>
                <p style="color: green;"><?php echo $_SESSION['success_message']; ?></p>
                <?php unset($_SESSION['success_message']); // Unset the success message once displayed 
                ?>
            <?php endif; ?>
            
        <div class="form">
            <form method="post" action="adminaddpersonnel.php" autocomplete="on">
            <div class="label">
            <label>First Name:</label>
            <input type="text" name="fname" required value="<?php echo isset($_POST['fname']) ? htmlspecialchars($_POST['fname']) : ''; ?>"></input>
        </div>
        <br>
        <div class="label">
            <label>Middle Name:</label>
            <input type="text" name="mname" value="<?php echo isset($_POST['mname']) ? htmlspecialchars($_POST['mname']) : ''; ?>"></input>
        </div>
        <br>
        <div class="label">
            <label>Last Name:</label>
            <input type="text" name="lname" required value="<?php echo isset($_POST['lname']) ? htmlspecialchars($_POST['lname']) : ''; ?>"></input>
        </div>
        <br>
        <div class="label">
            <label>Email:</label>
            <input type="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"></input>
        </div>
        <br>
        <div class="label">
            <label>Faculty Number:</label>
            <input type="text" name="facultynumber" value="<?php echo isset($_POST['facultynumber']) ? htmlspecialchars($_POST['facultynumber']) : ''; ?>"></input>
        </div>
        <br>
        <div class="label">
            <label>Office:</label>
            <input type="text" name="office" value="<?php echo isset($_POST['office']) ? htmlspecialchars($_POST['office']) : ''; ?>"></input>
        </div>
        <br>
        <div class="label">
            <label>Password:</label>
            <input type="password" name="password" required value="<?php echo isset($_POST['password']) ? htmlspecialchars($_POST['password']) : ''; ?>"></input>
        </div>
        <br>
        <div class="label">
            <label>Information:</label>
            <textarea type="text" name="information" required value="<?php echo isset($_POST['information']) ? htmlspecialchars($_POST['information']) : ''; ?>"></textarea>
        </div>

                <div class="submit-btn">
                <input type="submit" value="Add Personnel"></div>
            </form>
        </div>
        </div>
    </section>
</body>

</html>
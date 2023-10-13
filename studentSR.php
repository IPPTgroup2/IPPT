<?php
session_start();
require "connection.php";

// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}


$email = $_SESSION['email'];
if($email == false){
  header('Location: studentlogin.php');
}

// Set the current page based on the query parameter or the actual page name
$activePage = isset($_GET['page']) ? $_GET['page'] : 'request';

// Define a function to check if the current page matches a given page name
function isCurrentPage($pageName) {
    global $activePage;
    return $activePage === $pageName;
}

if(isset($_GET['office'])) {
    $office = $_GET['office'];

    // Retrieve name and facultynumber corresponding to the selected office
    $officeInfoSql = "SELECT fname, mname, lname, facultynumber FROM personnel WHERE office = '$office'";
    $officeInfoResult = $con->query($officeInfoSql);

    if ($officeInfoResult->num_rows > 0) {
        $officeInfoRow = $officeInfoResult->fetch_assoc();
        $name = $officeInfoRow['fname'] . ' ' . $officeInfoRow['mname'] . ' ' . $officeInfoRow['lname'];
        $facultynumber = $officeInfoRow['facultynumber'];
    }
} else {
    // Handle the case where 'office' is not set
    // You might want to provide a default value or display an error message.
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
    <title>Service Request Form</title>
    <script src="https://kit.fontawesome.com/6c1b1b5263.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="styles/studentSR.css">
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

  $studentname = "$fname $mname $lname";}
  ?>
  <div class="username"><?php echo $studentname; ?></div>
    <a href="studenthome.php"><img class = "user-icon" src="img/account.png" alt="User Icon"></a>
    <div class="arrow-down"></div>
    <div class="dropdown-content">
      <a href="studentaccount.php"><i class="fas fa-book-open"></i>          Account</a>
      <a href="studentchangepassword.php"><i class="fas fa-book-reader"></i>             Change Password</a>
      <a href="studentlogout.php"><i class='fas fa-sign-out-alt' style="font-size: 20px"></i>         Sign Out</a>
    </div>
  </div>
</div>   

<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">

                <?php
                // Retrieve student details from the database
                $email = $_SESSION['email'];
                $studentQuery = "SELECT fname, mname, lname, type FROM students WHERE email = '$email'";
                $studentResult = mysqli_query($con, $studentQuery);

                // Check if the query was successful
                if ($studentResult) {
                    // Fetch the student details
                    $studentRow = mysqli_fetch_assoc($studentResult);
                    $fname = $studentRow['fname'];
                    $mname = $studentRow['mname'];
                    $lname = $studentRow['lname'];
                    $type = $studentRow['type'];

                    // Store the student details in session variables
                    $_SESSION['fname'] = $fname;
                    $_SESSION['mname'] = $mname;
                    $_SESSION['lname'] = $lname;
                    $_SESSION['type'] = $type;
                } else {
                    // Handle the case when the query fails
                    echo "Error fetching student details: " . mysqli_error($con);
                }

                // Check if the form is submitted
                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    // Retrieve form data
                    $date_request = date('Y-m-d', strtotime('-9 hours')); 
                    $name = $_POST["name"];
                    $office = $_POST["office"];
                    $facultynumber = $_POST["facultynumber"];
                    $purpose = isset($_POST["purpose"]) ? $_POST["purpose"] : "";
                    $email = $_SESSION["email"];
                    $date = isset($_POST["date"]) ? $_POST["date"] : "";
                    $hour = isset($_POST["hour"]) ? $_POST["hour"] : "";
                    $minute = isset($_POST["minute"]) ? $_POST["minute"] : "";
                    $ampm = isset($_POST["ampm"]) ? $_POST["ampm"] : "";
                    $time = "$hour:$minute $ampm"; // Combine hour, minute, and AM/PM
                    $bookedBy = $_SESSION["fname"] . ' ' . $_SESSION["mname"] . ' ' . $_SESSION["lname"];
                    $type = $_SESSION["type"];

                    // Validate form data
                    if (empty($purpose) || empty($email) || empty($date) || empty($time)|| empty($office)) {
                        echo "<p>Please fill in all the required fields.</p><br><br>";
                    } else {
                        // Check if the entry already exists in the database
                        $existingEntryQuery = "SELECT * FROM bookings WHERE office = '$office' AND name= '$name' AND facultynumber='$facultynumber' AND booked_by = '$bookedBy' AND email = '$email' AND date = '$date' AND time = '$time'";
                        $existingEntryResult = mysqli_query($con, $existingEntryQuery);

                        if (mysqli_num_rows($existingEntryResult) > 0) {
                            echo "<p>An entry with the same information already exists.</p>";
                        } else {
                            // Insert booking into the database
                            $status = "Pending"; // Initial status
                            $sql = "INSERT INTO bookings (date_request, office , name, facultynumber, purpose, email, date, time, booked_by, type, status) VALUES ('$date_request','$office','$name','$facultynumber', '$purpose', '$email', '$date', '$time', '$bookedBy', '$type', '$status')";
                            if (mysqli_query($con, $sql)) {
                                echo "Booking successful!";
                                header("Location: studentSRstatus.php");
                                exit(); // Make sure to exit to prevent further code execution
                            } else {
                                echo "Error: " . $sql . "<br>" . mysqli_error($con);
                            }
                        }
                    }

                    // Close the database connection
                    mysqli_close($con);
                } elseif ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["office"]) && isset($_GET["name"]) && isset($_GET["facultynumber"]) && isset($_GET["email"]) && isset($_GET["booked_by"])) {
                    $office = $_GET["office"];
                    $name = $_GET["name"];
                    $facultynumber = $_GET["facultynumber"];
                    $email = $_GET["email"];
                    $bookedBy = $_GET["booked_by"];
                }
                ?>

                <input type="hidden" name="office" value="<?php echo isset($office) ? htmlspecialchars($office) : ""; ?>">

                <input type="hidden" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ""; ?>">

                <input type="hidden" name="facultynumber" value="<?php echo isset($facultynumber) ? htmlspecialchars($facultynumber) : ""; ?>">

                <input type="hidden" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>

                <div class="date-time">
                    <label for="date">Date:</label>
                    <input type="date" name="date" id="date" required><br><br>

                    <label for="time">Time:</label>
                        <select type="time" name="hour" required>
                            <?php
                            for ($i = 1; $i <= 12; $i++) {
                                echo "<option value='$i'>$i</option>";
                            }
                            ?>
                        </select>
                        :
                        <select type="time" name="minute" required>
                            <?php
                            for ($i = 0; $i <= 59; $i++) {
                                $formattedMinute = sprintf('%02d', $i);
                                echo "<option value='$formattedMinute'>$formattedMinute</option>";
                            }
                            ?>
                        </select>
                        <select type="time" name="ampm" required>
                            <option value="AM">AM</option>
                            <option value="PM">PM</option>
                        </select>
                        <br><br>
                </div>

                <label for="purpose">Purpose:</label>
                <select name="purpose" id="purpose" required>
                    <option value="Meeting">Meeting</option>
                    <option value="Consultation">Consultation</option>
                    <option value="Other">Other</option>
                </select>
                <br><br>

                <input type="hidden" name="booked_by" value="<?php echo isset($bookedBy) ? htmlspecialchars($bookedBy) : ''; ?>" required>

                <input type="hidden" name="type" value="<?php echo isset($type) ? htmlspecialchars($type) : ''; ?>">
                <input type="submit" value="Request">
            </form>
        </div>
    </section>

<body  class="<?php echo $activePage; ?>">
    <nav class="main-nav">
        <img src="logofinal.png" alt="ipptlogo">
        <ul class="main-nav-ul">
            <li><a href="studenthome.php" class="<?php echo isCurrentPage('home') ? 'active' : ''; ?>"><i class='fas fa-home' style="font-size: 20px; color:#76ffa2;"></i> Home</a></li>
            <li class="has-submenu">
            <a href="#" class="<?php echo isCurrentPage('servicerequest') || isCurrentPage('request') || isCurrentPage('servicerequeststatus') || isCurrentPage('servicerequesthistory') ? 'active' : ''; ?>"><i class='far fa-calendar-alt' style="font-size: 20px; color:#105e4e;"></i> Service Request</a>
                <ul class="submenu" id="sr-submenu">
                    <li><a href="studentSR1.php" class="<?php echo isCurrentPage('request') ? 'active' : ''; ?>"><i class="far fa-calendar-check" style="font-size: 20px; color:#105e4e;"></i> Request</a></li>
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
    
</body>

</html>
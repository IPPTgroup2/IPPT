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
    <title>Service Request Form</title>
    <script src="https://kit.fontawesome.com/6c1b1b5263.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="styles/adminbooking.css">
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

  $searchName = "";
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

            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">

                <?php
                // Retrieve admin details from the database
                $facultynumber = $_SESSION['facultynumber'];
                $adminQuery = "SELECT fname, mname, lname, email, type FROM admin WHERE facultynumber = '$facultynumber'";
                $adminResult = mysqli_query($con, $adminQuery);

                // Check if the query was successful
                if ($adminResult) {
                    // Fetch the admin details
                    $adminRow = mysqli_fetch_assoc($adminResult);
                    $fname = $adminRow['fname'];
                    $mname = $adminRow['mname'];
                    $lname = $adminRow['lname'];
                    $email = $adminRow['email'];
                    $type = $adminRow['type'];

                    // Store the admin details in session variables
                    $_SESSION['fname'] = $fname;
                    $_SESSION['mname'] = $mname;
                    $_SESSION['lname'] = $lname;
                    $_SESSION['email'] = $email;
                    $_SESSION['type'] = $type;
                } else {
                    // Handle the case when the query fails
                    echo "Error fetching admin details: " . mysqli_error($con);
                }

                // Check if the form is submitted
                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    // Retrieve form data
                    $date_request = date('Y-m-d', strtotime('-9 hours')); // Automatically set the date_request value to the current date
                    $name = $_POST["name"];
                    $facultynumber = isset($_POST["facultynumber"]) ? $_POST["facultynumber"] : "";
                    $purpose = isset($_POST["purpose"]) ? $_POST["purpose"] : "";
                    $email = $_SESSION["email"];
                    $date = isset($_POST["date"]) ? $_POST["date"] : "";
                    $hour = isset($_POST["hour"]) ? $_POST["hour"] : "";
                    $minute = isset($_POST["minute"]) ? $_POST["minute"] : "";
                    $ampm = isset($_POST["ampm"]) ? $_POST["ampm"] : "";
                    $time = "$hour:$minute $ampm"; // Combine hour, minute, and AM/PM
                    $bookedBy = $_SESSION["fname"] . ' ' . $_SESSION["mname"] . ' ' . $_SESSION["lname"];
                    $type = $_SESSION["type"] . ' ';

                    // Validate form data
                    if (empty($purpose) || empty($email) || empty($date) || empty($time)) {
                        echo "<p>Please fill in all the required fields.</p><br>";
                    } else {
                        // Check if the entry already exists in the database
                        $existingEntryQuery = "SELECT * FROM bookings WHERE name = '$name' AND facultynumber='$facultynumber' AND booked_by = '$bookedBy' AND email = '$email' AND date = '$date' AND time = '$time' AND type = '$type'";
                        $existingEntryResult = mysqli_query($con, $existingEntryQuery);

                        if (mysqli_num_rows($existingEntryResult) > 0) {
                            echo "<p>An entry with the same information already exists.</p>";
                        } else {
                            // Insert booking into the database
                            $status = "Pending"; // Initial status
                            $sql = "INSERT INTO bookings (date_request, name, facultynumber, purpose, email, date, time, booked_by, type, status) VALUES ('$date_request', '$name', '$facultynumber', '$purpose', '$email', '$date', '$time', '$bookedBy','$type', '$status')";
                            if (mysqli_query($con, $sql)) {
                                echo "Booking successful!";
                                // Booking successful, redirect to adminbookingstatus.php
                                header("Location: adminSRstatus.php");
                                exit();
                            } else {
                                echo "Error: " . $sql . "<br>" . mysqli_error($con);
                            }
                        }
                    }

                } elseif ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["name"]) && isset($_GET["email"]) && isset($_GET["booked_by"])) {
                    $name = $_GET["name"];
                    $email = $_GET["email"];
                    $bookedBy = $_GET["booked_by"];
                }
                ?>

                <input type="hidden" name="facultynumber" value="<?php echo isset($facultynumber) ? htmlspecialchars($facultynumber) : ""; ?>">

                <input type="hidden" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ""; ?>">

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
                <textarea name="purpose" id="purpose" cols="30" rows="10" required></textarea><br><br>

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

</body>

</html>
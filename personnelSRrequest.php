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

if (isset($_POST["booking_id"]) && isset($_POST["status"]) && isset($_POST["reason"])) {
  $bookingId = $_POST["booking_id"];
  $status = $_POST["status"];
  $reason = $_POST["reason"];

  // Update the approval status and reason in the database
  $sql = "UPDATE bookings SET status = '$status', reason = '$reason' WHERE id = '$bookingId'";
  if (mysqli_query($con, $sql)) {
      echo "Approval status updated successfully!";
      header('Location: personnelSRhistory.php');
      exit();
  } else {
      echo "Error updating approval status: " . mysqli_error($con);
  }
}

// Set the current page based on the query parameter or the actual page name
$activePage = isset($_GET['page']) ? $_GET['page'] : 'servicerequest';

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
    <title>Booking Request</title>
    <script src="https://kit.fontawesome.com/6c1b1b5263.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="styles/personnelbookingrequest.css">
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

  document.addEventListener("DOMContentLoaded", function() {
    const declineButtons = document.querySelectorAll(".decline-button");

    declineButtons.forEach(button => {
      button.addEventListener("click", function(event) {
        event.preventDefault();
        const bookingId = this.dataset.bookingId;
        const reasonInput = prompt("Please provide a reason for declining:");
        if (reasonInput !== null) {
          const form = document.createElement("form");
          form.method = "post";
          form.action = "personnelSRrequest.php";
          const bookingIdInput = document.createElement("input");
          bookingIdInput.type = "hidden";
          bookingIdInput.name = "booking_id";
          bookingIdInput.value = bookingId;
          const statusInput = document.createElement("input");
          statusInput.type = "hidden";
          statusInput.name = "status";
          statusInput.value = "Declined";
          const reasonTextInput = document.createElement("input");
          reasonTextInput.type = "hidden";
          reasonTextInput.name = "reason";
          reasonTextInput.value = reasonInput;
          form.appendChild(bookingIdInput);
          form.appendChild(statusInput);
          form.appendChild(reasonTextInput);
          document.body.appendChild(form);
          form.submit();
        }
      });
    });
  });
  </script>

<body  class="<?php echo $activePage; ?>">
    <nav class="main-nav">
        <img src="logofinal.png" alt="ipptlogo">
        <ul class="main-nav-ul">
            <li><a href="personnelhome.php" class="<?php echo isCurrentPage('home') ? 'active' : ''; ?>"><i class='fas fa-home' style="font-size: 20px; color:#76ffa2;"></i> Home</a></li>
              <li><a href="personnelDTR.php" class="<?php echo isCurrentPage('dtr') ? 'active' : ''; ?>"><i class='fas fa-home' style="font-size: 20px; color:#76ffa2;"></i> Daily Time Record</a></li>
            <li class="has-submenu">
                <a href="#" class="<?php echo isCurrentPage('servicerequest') || isCurrentPage('servicerequest') || isCurrentPage('servicerequesthistory') ? 'active' : ''; ?>"><i class='far fa-calendar-alt' style="font-size: 20px; color:#105e4e;"></i> Service Request</a>
                <ul class="submenu" id="sr-submenu">
                    <li><a href="personnelSRrequest.php" class="<?php echo isCurrentPage('servicerequest') ? 'active' : ''; ?>"><i class="far fa-calendar-check" style="font-size: 20px; color:#105e4e;"></i> Requests</a></li>
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
      <a href="personnelchangepass.php"><i class="fas fa-book-reader"></i>             Change Password</a>
      <a href="personnellogout.php"><i class='fas fa-sign-out-alt' style="font-size: 20px"></i>         Sign Out</a>
    </div>
  </div>
</div>
            <div class="history-list">
                <?php
                $name='';
                if (isset($_SESSION['facultynumber'])) {
                    $facultynumber = $_SESSION['facultynumber'];

                    $sql = "SELECT name, facultynumber FROM attendance2 WHERE facultynumber = '$facultynumber'";
                    $result = $con->query($sql);

                    if ($result->num_rows > 0) {
                        // User found, fetch the data
                        $row = $result->fetch_assoc();
                        $name = $row['name'];
                        $facultynumber = $row['facultynumber'];
                    }

                    // Fetch all pending booking requests from the database
                    $sql = "SELECT * FROM bookings WHERE name = '$name' AND status = 'Pending'";
                    $result = mysqli_query($con, $sql);

                    if (mysqli_num_rows($result) > 0) {
                        echo "<table>
                                <tr>
                                    <th>Booked By</th>
                                    <th>Type</th>
                                    <th>Purpose</th>
                                    <th>Email</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Action</th>
                                </tr>";

                        while ($row = mysqli_fetch_assoc($result)) {
                            $bookingId = $row["id"];
                            $bookedBy = $row["booked_by"];
                            $type = $row["type"];
                            $purpose = $row["purpose"];
                            $email = $row["email"];
                            $date = $row["date"];
                            $time = $row["time"];

                            echo "<tr>
                                    <td>$bookedBy</td>
                                    <td>$type</td>
                                    <td>$purpose</td>
                                    <td>$email</td>
                                    <td>$date</td>
                                    <td>$time</td>
                                    <td>
    <form method='post' action='personnelSRrequest.php'>
        <input type='hidden' name='booking_id' value='$bookingId'>
        <button type='submit' name='status' value='Approved'>Approve</button>
        <button type='button' class='decline-button' data-booking-id='$bookingId'>Decline</button>
    </form>
</td>
                                </tr>";
                        }

                        echo "</table>";
                    } else {
                        echo "<p>No pending booking requests.</p>";
                    }
                }

                // Close the result and connection
                mysqli_free_result($result);
                mysqli_close($con);
                ?>
            </div>
        </div>
    </section>
</body>

</html>
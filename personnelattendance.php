<?php
session_start();
require "connection.php";

$activePage = isset($_GET['page']) ? $_GET['page'] : 'home';

// Define a function to check if the current page matches a given page name
function isCurrentPage($pageName)
{
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
    <link rel="stylesheet" href="styles/personnelattendance.css">
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
        }
    } ?>
    <div class="container">
        <div class="user-info">
            <div class="user-dropdown">
                <div class="username"><?php echo $name; ?></div>
                <a href="personnelhome.php"><img class="user-icon" src="img/account.png" alt="User Icon"></a>
                <div class="arrow-down"></div>
                <div class="dropdown-content">
                    <a href="personnelaccount.php"><i class="fas fa-book-open"></i> Account</a>
                    <a href="personnelchangepass.php"><i class="fas fa-book-reader"></i> Change Password</a>
                    <a href="personnellogout.php"><i class='fas fa-sign-out-alt' style="font-size: 20px"></i> Sign Out</a>
                </div>
            </div>
        </div>
        <?php

        // Retrieve the logged-in user's information
        $facultynumber = $_SESSION['facultynumber'];

        // Retrieve the data from the source table
        $sourceTable = 'personnel';
        $sourceQuery = mysqli_query($con, "SELECT fname, mname, lname, facultynumber, office, information FROM $sourceTable WHERE facultynumber = '$facultynumber'");

        // Check if the query was successful
        if (!$sourceQuery) {
            die("Error fetching data from source table: " . mysqli_error($con));
        }

        // Fetch the user's information from the source table
        $row = mysqli_fetch_assoc($sourceQuery);
        $fname = $row['fname'];
        $mname = $row['mname'];
        $lname = $row['lname'];
        $facultyNumber = $row['facultynumber'];
        $office = $row['office'];
        $info = $row['information'];

        // Concatenate the name into a single variable
        $name = "$fname $mname $lname";

        // Insert the data into the destination table
        $destinationTable = 'attendance2';
        while ($row = mysqli_fetch_assoc($sourceQuery)) {
            $name = mysqli_real_escape_string($con, $row['name']);
            $facultyNumber = mysqli_real_escape_string($con, $row['facultynumber']);
            $office = mysqli_real_escape_string($con, $row['office']);
            $info = mysqli_real_escape_string($con, $row['information']);

            $insertQuery = "INSERT INTO $destinationTable (name, facultynumber, office, information) VALUES ('$name', '$facultyNumber', '$office', '$info') ON DUPLICATE KEY UPDATE name = '$name',facultynumber = '$facultyNumber', office = '$office', information = '$info'";

            $insertResult = mysqli_query($con, $insertQuery);

            if (!$insertResult) {
                die("Error inserting data into destination table: " . mysqli_error($con));
            }
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $timeOption = $_POST['timeOption'];
            $dateTime = date('Y-m-d h:i:s a', strtotime('-9 hours'));
        
            // Check connection
            if ($con->connect_error) {
                die("Connection failed: " . $con->connect_error);
            }
        
            // Retrieve existing row for the logged-in person
            $sql = "SELECT * FROM $destinationTable WHERE facultynumber = '$facultyNumber'";
            $result = $con->query($sql);
        
            if ($result->num_rows > 0) {
                // Row exists, fetch the existing record
                $row = $result->fetch_assoc();
                $existingStatus = $row['status'];
        
                // Check the selected time option
                if (
                    ($existingStatus === 'AM Arrival' && $timeOption === 'amArrival') ||
                    ($existingStatus === 'PM Arrival' && $timeOption === 'pmArrival')
                ) {
                    $_SESSION['message'] = "You have already timed in.";
                    echo '<script>';
                    echo 'alert("You have already timed in.");';
                    echo '</script>';
                } elseif (
                    ($existingStatus === 'AM Departure' && $timeOption === 'amDeparture') ||
                    ($existingStatus === 'PM Departure' && $timeOption === 'pmDeparture')
                ) {
                    $_SESSION['message'] = "You have already timed out.";
                    echo '<script>';
                    echo 'alert("You have already timed out.");';
                    echo '</script>';
                } else {
                    updateRecord($name, $facultyNumber, $office, $timeOption, $dateTime, $info);
                }
            } else {
                // Row doesn't exist, insert a new record
                if (
                    ($existingStatus === 'AM Arrival' && $timeOption === 'amDeparture') ||
                    ($existingStatus === 'PM Arrival' && $timeOption === 'pmDeparture')
                ) {
                    $_SESSION['message'] = "You need to time in first.";
                    echo '<script>';
                    echo 'alert("You need to time in first.");';
                    echo '</script>';
                } else {
                    // Set the status based on the selected option
                    if ($timeOption === 'amArrival') {
                        $status = 'AM Arrival';
                    } elseif ($timeOption === 'amDeparture') {
                        $status = 'AM Departure';
                    } elseif ($timeOption === 'pmArrival') {
                        $status = 'PM Arrival';
                    } elseif ($timeOption === 'pmDeparture') {
                        $status = 'PM Departure';
                    }
        
                    // Insert the record into the database
                    $sql = "INSERT INTO $destinationTable (name, facultynumber, office, timein, status, information) VALUES ('$name', '$facultyNumber', '$office', '$dateTime', '$status', '$info')";
        
                    if ($con->query($sql) === TRUE) {
                        $_SESSION['message'] = "$status data saved successfully.";
                        $_SESSION['status'] = $status;
                        appendToCSV($name, $facultyNumber, $office, $dateTime, $status);
                        // Redirect to another page
                        header("Location: personnelhome.php");
                        exit;
                    } else {
                        $_SESSION['message'] = "Error: " . $sql . "<br>" . $con->error;
                        echo '<script>alert("Error: ' . $sql . '<br>' . $con->error . '");</script>';
                    }
                }
            }
        
            // Close the database connection
            $con->close();
        }        

        function updateRecord($name, $facultyNumber, $office, $timeOption, $dateTime)
        {
            global $con;
        
            if ($timeOption === 'amArrival' || $timeOption === 'pmArrival') {
                $status = ($timeOption === 'amArrival') ? 'AM Arrival' : 'PM Arrival';
                $sql = "UPDATE attendance2 SET timein = '$dateTime', status = '$status' WHERE name = '$name' AND facultynumber = '$facultyNumber' AND office ='$office'";
            } elseif ($timeOption === 'amDeparture' || $timeOption === 'pmDeparture') {
                $status = ($timeOption === 'amDeparture') ? 'AM Departure' : 'PM Departure';
                $sql = "UPDATE attendance2 SET timeout = '$dateTime', status = '$status' WHERE name = '$name' AND facultynumber = '$facultyNumber' AND office ='$office'";
            } else {
                // Invalid time option
                $_SESSION['message'] = "Invalid time option.";
                echo '<script>alert("Invalid time option.");</script>';
                return;
            }
        
            if ($con->query($sql) === TRUE) {
                $_SESSION['message'] = "$status data saved successfully.";
                $_SESSION['status'] = $status;
                appendToCSV($name, $facultyNumber, $office, $dateTime, $status);
                // Redirect to another page
                header("Location: personnelhome.php");
                exit;
            } else {
                $_SESSION['message'] = "Error: " . $sql . "<br>" . $con->error;
                echo '<script>alert("Error: ' . $sql . '<br>' . $con->error . '");</script>';
            }
        }
        
        function appendToCSV($name, $facultyNumber, $office, $dateTime, $status)
        {
            $csvFile = 'try.csv';
        
            // Format the data for CSV
            $data = array($name, $facultyNumber, $office, $dateTime, $status);
        
            // Open the CSV file in append mode
            $file = fopen($csvFile, 'a');
        
            // Append the data to the CSV file
            fputcsv($file, $data);
        
            // Close the file
            fclose($file);
        }
        ?>
        
        <h2><?php echo $name; ?></h2>
        <form action="personnelattendance.php" method="POST" action="">
    <div class="form-group">
        <button type="submit" name="timeOption" value="amArrival">AM Arrival</button>
        <button type="submit" name="timeOption" value="amDeparture">AM Departure</button>
        <button type="submit" name="timeOption" value="pmArrival">PM Arrival</button>
        <button type="submit" name="timeOption" value="pmDeparture">PM Departure</button>
    </div>
</form>

        
    </div>

    </div>
</section>

<body class="<?php echo $activePage; ?>">
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
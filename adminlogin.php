<?php
session_start();
require "connection.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $facultynumber = $_POST["facultynumber"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM admin WHERE facultynumber = '$facultynumber'";
    $result = mysqli_query($con, $sql);

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['facultynumber'] = $row['facultynumber'];
            $_SESSION['email'] = $row['email'];
            header("Location: adminhome.php");
            exit();
        } else {
            $error = "Invalid credentials";
        }
    } else {
        $error = "Invalid credentials";
    }
}

$con->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Log In</title>
    <link rel="stylesheet" href="styles/adminlogin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Urbanist&display=swap" rel="stylesheet">
</head>

<body>
    <img src="img/bckgrnd.jpg" alt="bckgrnd" class="bckgrnd">
    <div class="container">
        <img class="image" src="logofinal.png" alt="ipptlogo">
        <h3>Admin Log In</h3>
            <div class="error-message">
                <?php if (!empty($error)) : ?>
                    <p><?php echo $error; ?></p>
                <?php endif; ?>
            </div>
            <form action="adminlogin.php" method="POST">
            <div class="txtfield">
                <input type="text" name="facultynumber" id="facultynumber" placeholder="Faculty Number" required>
            </div>
            <div class="txtfield">
                <input type="password" name="password" id="password" placeholder="Password" required>
            </div>
            <div class="checkbox">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">I agree to the <a href="#" onclick="openTermsPopup()">Terms of Service</a> and <a href="#" onclick="openPrivacyPopup()">Privacy Policy</a></label>
            </div>

            <script>
                function openTermsPopup() {
                    var popup = window.open("terms.html", "Terms of Service", "width=600,height=500");
                    popup.focus();
                    return false;
                }

                function openPrivacyPopup() {
                    var popup = window.open("privacy.html", "Privacy Policy", "width=600,height=500");
                    popup.focus();
                    return false;
                }

                function closeTermsPopup() {
                    window.close();
                }
                </script>

            <input type="submit" value="Login">
        </form>
        <div class="forgotpass">
            <a href="adminforgotpass.php">Forgot Password</a>
        </div>
    </div>

</body>

</html>
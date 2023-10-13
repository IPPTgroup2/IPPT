<?php require_once "studentcontrol.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Log In</title>
    <link rel="stylesheet" href="styles/studentlogin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Urbanist&display=swap" rel="stylesheet">
</head>
<body>
    <img src="img/bckgrnd.jpg" alt="bckgrnd" class="bckgrnd">
    <div class="container">
        <img class="image" src="logofinal.png" alt="ipptlogo">
        <h3>Student Log In</h3>
            <form action = "studentlogin.php" method="post">
            <?php
    if(count($errors) > 0){
        echo '<script>';
        echo 'window.onload = function() {';
        echo 'alert("';
        foreach($errors as $showerror){
            echo $showerror . "\\n";
        }
        echo '");';
        echo '}';
        echo '</script>';
    }
    ?>
                <div class="txtfield">
                <form action = "studentlogin.php" method="POST">
                <input type="text" name="email" id="email" placeholder="Email"required value="<?php echo $email ?>">
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

                <input type="submit" name="login" value="Login">
            </form>
        <div class="forgotpass">
        <a href="studentforgotpass.php">Forgot Password</a>
        </div>
        <div class="account">
            <a href="studentsignup.php">No account? Sign Up here!</a>
        </div>
        
    </div>
</body>
</html>
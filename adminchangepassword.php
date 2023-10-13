<?php
session_start();
require "connection.php";

$errors = []; // Initialize the $errors array

if(isset($_POST['change-password'])){
    $_SESSION['info'] = "";
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $cpassword = mysqli_real_escape_string($con, $_POST['cpassword']);

    // Validate the input fields
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password should be at least 6 characters long.";
    }
    if($password !== $cpassword){
        $errors[] = "Confirm password not matched!";
    }else{
        $code = 0;
        $email = $_SESSION['email']; //getting this email using session
        $encpass = password_hash($password, PASSWORD_BCRYPT);
        $update_pass = "UPDATE admin SET password = '$encpass' WHERE email = '$email'";
        $run_query = mysqli_query($con, $update_pass);
        if($run_query){
            $info = "Your password changed. Now you can login with your new password.";
            $_SESSION['info'] = $info;
            header('Location: adminlogin.php');
        }else{
            $errors[] = "Failed to change your password!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Password</title>
    <link rel="stylesheet" href="styles/studentlogin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Urbanist&display=swap" rel="stylesheet">
</head>

<body>
    <img src="img/bckgrnd.jpg" alt="bckgrnd" class="bckgrnd">
    <div class="container">
        <img class="image" src="logofinal.png" alt="ipptlogo">
        <form action="adminchangepassword.php" method="POST" autocomplete="off">
            <h2 class="text-center">New Password</h2>
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
                <input type="password" name="password" id="password" placeholder="Create a new password" required>
            </div>
            <div class="txtfield">
                <input type="password" name="cpassword" id="password" placeholder="Confirm your password" required>
            </div>
            <input class="button" type="submit" name="change-password" value="Change">
    </div>
    </form>
    </div>
    </div>
    </div>

</body>

</html>
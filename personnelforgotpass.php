<?php 
session_start();
require "connection.php";
$email = "";
$errors = array();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="styles/studentlogin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Urbanist&display=swap" rel="stylesheet">
</head>
<body>
<img src="img/bckgrnd.jpg" alt="bckgrnd" class="bckgrnd">
    <div class="container">

<?php 
//if user click continue button in forgot password form
        if (isset($_POST['check-email'])) {
            $email = mysqli_real_escape_string($con, $_POST['email']);
            $check_email = "SELECT * FROM personnel WHERE email='$email'";
            $run_sql = mysqli_query($con, $check_email);

            if (mysqli_num_rows($run_sql) > 0) {
                $subject = "Password Reset Link";
                $message = "Click the link below to reset your password:\n\n";
                $message .= "http://localhost:3000/personnelchangepassword.php?email=$email";
                $sender = "From: mayanderamos08@gmail.com";
                if (mail($email, $subject, $message, $sender)) {
                    $info = "We've sent a password reset link to your email - $email";
                    $_SESSION['info'] = $info;
                    $_SESSION['email'] = $email;
                    header('location: personnellogin.php');
                    exit();
                } else {
                    $errors['email-error'] = "Failed while sending email!";
                }
            } else {
                $errors['email'] = "This email address does not exist!";
            }
        }
        ?>
        <img class="image" src="logofinal.png" alt="ipptlogo">
        <h3>Forgot Password</h3>
                <form action="personnelforgotpass.php" method="POST" autocomplete="">
                    <?php
                        if(count($errors) > 0){
                            ?>
                            <div class="alert alert-danger text-center">
                                <?php 
                                    foreach($errors as $error){
                                        echo $error;
                                    }
                                ?>
                            </div>
                            <?php
                        }
                    ?>
                <form method="post">
                <div class="txtfield">
                <input type="text" name="email" id="email" placeholder="Email"requiredvalue="<?php echo $email ?>">
                </div>
                    <input type="submit" name="check-email" value="Continue">

                </form>
</body>
</html>
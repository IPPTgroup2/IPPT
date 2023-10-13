<?php require_once "studentcontrol.php";

//$email = $_SESSION['email'];
//if($email == false){
 // header('Location: studentlogin.php');
//}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code Verification</title>
    <link rel="stylesheet" href="styles/studentlogin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Urbanist&display=swap" rel="stylesheet">
</head>
<body>
<img src="img/bckgrnd.jpg" alt="bckgrnd" class="bckgrnd">
    <div class="container">
        <img class="image" src="logofinal.png" alt="ipptlogo">
        <h3>Verification Code</h3>
                <form action="studentotp.php" method="POST" autocomplete="off">
                    <?php 
                    if(isset($_SESSION['info'])){
                        ?>
                        <div class="alert-success">
                            <?php echo $_SESSION['info']; ?>
                        </div>
                        <?php
                    }
                    ?>
                    <?php
                    if(count($errors) > 0){
                        ?>
                        <div class="alert-text">
                            <?php
                            foreach($errors as $showerror){
                                echo $showerror;
                            }
                            ?>
                        </div>
                        <?php
                    }
                    ?>
                <div class="txtfield">
                <input type="text" name="otp" id="otp" placeholder="Enter verification code"required>
                </div>
                <input type="submit" name="check" value="Submit">
                <p>Want to reset the code?<a href="studentreset.php">Reset</a></p>
                </form>
            </div>
</body>
</html>
<?php require_once "studentcontrol.php";

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
        <img class="image" src="logofinal.png" alt="ipptlogo">
        <h3>Forgot Password</h3>
                <form action="studentforgotpass.php" method="POST" autocomplete="">
                    <?php
                        if(count($errors) > 0){
                            ?>
                            <div class="alert-text">
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
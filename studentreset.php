<?php require_once "studentcontrol.php"; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Code Verification</title>
    <link rel="stylesheet" href="styles/studentlogin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Urbanist&display=swap" rel="stylesheet">
</head>
<body>
<img src="img/bckgrnd.jpg" alt="bckgrnd" class="bckgrnd">
    <div class="container">
        <img class="image" src="logofinal.png" alt="ipptlogo">
        <h2 class="text-center">Reset Verification Code</h2>

        <?php 
        if(isset($_SESSION['info'])){
            ?>
            <div class="alert-success" style="padding: 0.4rem 0.4rem">
                <?php echo $_SESSION['info']; ?>
            </div>
            <?php
            unset($_SESSION['info']);
        }
        ?>

        <form action="reset_code_logic.php" method="POST" autocomplete="off">
            <input type="submit" name="reset_code" value="Reset Verification Code">
        </form>
    </div>
</body>
</html>
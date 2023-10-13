<?php require_once "studentcontrol.php"; ?>
<?php
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
        <form action="studentnewpass.php" method="POST" autocomplete="off">
            <h2 class="text-center">New Password</h2>
            <?php
            if (count($errors) > 0) {
            ?>
                <div class="alert-text">
                    <?php
                    foreach ($errors as $showerror) {
                        echo $showerror;
                    }
                    ?>
                </div>
            <?php
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

</body>

</html>
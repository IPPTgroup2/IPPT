<?php require_once "studentcontrol.php"; 

if($_SESSION['info'] == false){
    header('Location: studentlogin.php');  
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Change Passsword</title>
    <link rel="stylesheet" href="styles/studentlogin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Urbanist&display=swap" rel="stylesheet">
</head>
<body>
<img src="img/bckgrnd.jpg" alt="bckgrnd" class="bckgrnd">
    <div class="container">
        <img class="image" src="logofinal.png" alt="ipptlogo">
        <h3>Changed Password</h3>
        <?php 
    if(isset($_SESSION['info'])){
        ?>
        <script>
            window.onload = function() {
                alert("<?php echo $_SESSION['info']; ?>");
            }
        </script>
        <?php
        unset($_SESSION['info']); // Clear the session variable after displaying the alert
    }
    ?>
                <form action="studentlogin.php" method="POST">
                    <div class="form-group">
                        <input class="form-control button" type="submit" name="login-now" value="Login Now">
                    </div>
                </form>
            </div>
        </div>
    </div>
    
</body>
</html>
<?php require_once "studentcontrol.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/studentsignup.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Urbanist&display=swap" rel="stylesheet">
    <title>Student SignUp</title>
</head>
<body>   
    <img src="img/bckgrnd.jpg" alt="bckgrnd" class="bckgrnd">
    <div class="container">
        <div class="textfield">
        <img class="image" src="logofinal.png" alt="ipptlogo">
        <h3>Student Sign Up</h3>
        <form action="studentsignup.php" method="post">
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
            <input type="text" name="fname" id="fname" placeholder="First Name"required value="<?php echo $fname ?>">
            <input type="text" name="mname" id="mname" placeholder="Middle Name"required value="<?php echo $mname ?>">
            <input type="text" name="lname" id="lname" placeholder="Last Name"required value="<?php echo $lname ?>">
            <input list="course" name="course" placeholder="Course"required>
            <datalist id="course">
                <option value="Visitor">
                <option value="BSIT 1-1">
                <option value="BSIT 2-1">
                <option value="BSIT 3-1">
                <option value="BSIT 4-1">
                <option value="BSBA HRM 1-1">
                <option value="BSBA HRM 2-1">
                <option value="BSBA HRM 3-1">
                <option value="BSBA HRM 4-1">
                <option value="BSBA MM 1-1">
                <option value="BSBA MM 2-1">
                <option value="BSBA MM 3-1">
                <option value="BSBA MM 4-1">
                <option value="BSOA 1-1">
                <option value="BSOA 2-1">
                <option value="BSOA 3-1">
                <option value="BSOA 4-1">
                <option value="BEED 1-1">
                <option value="BEED 2-1">
                <option value="BEED 3-1">
                <option value="BEED 4-1">
                <option value="BSED 1-1">
                <option value="BSED 2-1">
                <option value="BSED 3-1">
                <option value="BSED 4-1">            
            </datalist>
            <input type="email" name="email" id="email" placeholder="Email"required value="<?php echo $email ?>">
            <input type="password" name="password" id="password" placeholder="Password"required>
            <input type="password" name="cpassword" id="cpassword" placeholder="Confirm Password"required>
            <button type="submit" name="signup">Sign Up</button>
        </form>
        </div>
        <p>Already have an account? <a href="studentlogin.php">Log In</a></p>
    </div>
</body>
</html>

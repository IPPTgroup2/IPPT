<?php 
session_start();
require "connection.php";
$email = "";
$fname = "";
$mname = "";
$lname = "";
$errors = array();

//if user signup button
if (isset($_POST['signup'])) {
    $fname = mysqli_real_escape_string($con, $_POST['fname']);
    $mname = mysqli_real_escape_string($con, $_POST['mname']);
    $lname = mysqli_real_escape_string($con, $_POST['lname']);
    $course = mysqli_real_escape_string($con, $_POST['course']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $cpassword = mysqli_real_escape_string($con, $_POST['cpassword']);

    
    // Validate the input fields
    if (empty($password)) {
        $errors[] =  "Password is required.";
    }if ($password !== $cpassword) {
        $errors[] =  "Confirm password not matched!";
    } elseif (strlen($password) < 6) {
        $errors[] =  "Password should be at least 6 characters long.!";
    }

    $email_check = "SELECT * FROM students WHERE email = '$email'";
    $res = mysqli_query($con, $email_check);

    if (mysqli_num_rows($res) > 0) {
        $errors['email'] = "Email that you have entered already exists!";
    }

    // Check the value of the $course variable to set the value of the 'type' field
    $type = ($course === 'Visitor') ? 'Visitor' : 'Student';

    if (count($errors) === 0) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $code = rand(999999, 111111);
        $status = "notverified";

        $insert_data = "INSERT INTO students ( type, fname, mname, lname, course, email, password, code, status)
                        VALUES ( '$type', '$fname', '$mname', '$lname', '$course', '$email', '$hashedPassword', '$code', '$status' )";

        $data_check = mysqli_query($con, $insert_data);

        if ($data_check) {
            $subject = "Email Verification Code";
            $message = "Your verification code is $code";
            $sender = "From: ippt8111@gmail.com";

            if (mail($email, $subject, $message, $sender)) {
                $info = "We've sent a verification code to your email - $email";
                $_SESSION['info'] = $info;
                $_SESSION['email'] = $email;
                $_SESSION['password'] = $password;
                header('location: studentotp.php');
                exit();
            } else {
                $errors['otp-error'] = "Failed while sending code!";
            }
        } else {
            $errors['db-error'] = "Failed while inserting data into the database!";
        }
    }
}

    //if user click verification code submit button
    if(isset($_POST['check'])){
        $_SESSION['info'] = "";
        $otp_code = mysqli_real_escape_string($con, $_POST['otp']);
        $check_code = "SELECT * FROM students WHERE code = $otp_code";
        $code_res = mysqli_query($con, $check_code);
        if(mysqli_num_rows($code_res) > 0){
            $fetch_data = mysqli_fetch_assoc($code_res);
            $fetch_code = $fetch_data['code'];
            $email = $fetch_data['email'];
            $code = 0;
            $status = 'verified';
            $update_otp = "UPDATE students SET code = $code, status = '$status' WHERE code = $fetch_code";
            $update_res = mysqli_query($con, $update_otp);
            if($update_res){
                $_SESSION['fname'] = $fname;
                $_SESSION['email'] = $email;
                header('location: studenthome.php');
                exit();
            }else{
                $errors['otp-error'] = "Failed while updating code!";
            }
        }else{
            $errors['otp-error'] = "You've entered incorrect code!";
        }
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
    //if user click login button
    if (isset($_POST['login'])) {
        $email = mysqli_real_escape_string($con, $_POST['email']);
        $password = mysqli_real_escape_string($con, $_POST['password']);
    
        $check_email = "SELECT * FROM students WHERE email = '$email'";
        $res = mysqli_query($con, $check_email);
    
        if (mysqli_num_rows($res) > 0) {
            $fetch = mysqli_fetch_assoc($res);
            $storedHashedPassword = $fetch['password'];
    
            if (password_verify($password, $storedHashedPassword)) {
                $_SESSION['email'] = $email;
                $status = $fetch['status'];
    
                if ($status == 'verified') {
                    $_SESSION['email'] = $email;
                    $_SESSION['password'] = $password;
                    header('location: studenthome.php');
                } else {
                    $info = "It seems like you haven't verified your email - $email";
                    $_SESSION['info'] = $info;
                    header('location: studentotp.php');
                }
            } else {
                $errors['email'] = "Incorrect email or password!";
            }
        } else {
            $errors['email'] = "It seems like you're not yet a member! Click the link below to sign up.";
        }
    }}

    //if user click continue button in forgot password form
    if(isset($_POST['check-email'])){
        $email = mysqli_real_escape_string($con, $_POST['email']);
        $check_email = "SELECT * FROM students WHERE email='$email'";
        $run_sql = mysqli_query($con, $check_email);
        if(mysqli_num_rows($run_sql) > 0){
            $code = rand(999999, 111111);
            $insert_code = "UPDATE students SET code = $code WHERE email = '$email'";
            $run_query =  mysqli_query($con, $insert_code);
            if($run_query){
                $subject = "Password Reset Code";
                $message = "Your password reset code is $code";
                $sender = "From: ippt8111@gmail.com";
                if(mail($email, $subject, $message, $sender)){
                    $info = "We've sent a password reset otp to your email - $email";
                    $_SESSION['info'] = $info;
                    $_SESSION['email'] = $email;
                    header('location: studentresetcode.php');
                    exit();
                }else{
                    $errors['otp-error'] = "Failed while sending code!";
                }
            }else{
                $errors['db-error'] = "Something went wrong!";
            }
        }else{
            $errors['email'] = "This email address does not exist!";
        }
    }

    //if user click check reset otp button
    if(isset($_POST['check-reset-otp'])){
        $_SESSION['info'] = "";
        $otp_code = mysqli_real_escape_string($con, $_POST['otp']);
        $check_code = "SELECT * FROM students WHERE code = $otp_code";
        $code_res = mysqli_query($con, $check_code);
        if(mysqli_num_rows($code_res) > 0){
            $fetch_data = mysqli_fetch_assoc($code_res);
            $email = $fetch_data['email'];
            $_SESSION['email'] = $email;
            $info = "Please create a new password that you don't use on any other site.";
            $_SESSION['info'] = $info;
            header('location: studentnewpass.php');
            exit();
        }else{
            $errors['otp-error'] = "You've entered incorrect code!";
        }
    }

    //if user click change password button
    if(isset($_POST['change-password'])){
        $_SESSION['info'] = "";
        $password = mysqli_real_escape_string($con, $_POST['password']);
        $cpassword = mysqli_real_escape_string($con, $_POST['cpassword']);

        // Validate the input fields
    if (empty($password)) {
        $errors[] = "Password is required.";
    } 
        if($password !== $cpassword){
            $errors['password'] = "Confirm password not matched!";
        }elseif (strlen($password) < 6) {
        $errors[] = "Password should be at least 6 characters long.";
        }
        else{
            $code = 0;
            $email = $_SESSION['email']; //getting this email using session
            $encpass = password_hash($password, PASSWORD_BCRYPT);
            $update_pass = "UPDATE students SET code = $code, password = '$encpass' WHERE email = '$email'";
            $run_query = mysqli_query($con, $update_pass);
            if($run_query){

                $info = "Your password changed. Now you can login with your new password.";
                $_SESSION['info'] = $info;
                header('Location: studentchangepass.php');
            }else{
                $errors['db-error'] = "Failed to change your password!";
            }
        }
    }
    
//if user click change password button
if(isset($_POST['change-pass'])){
    $_SESSION['info'] = "";
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $cpassword = mysqli_real_escape_string($con, $_POST['cpassword']);
    if($password !== $cpassword){
        $errors['password'] = "Confirm password not matched!";
    }else{
        $code = 0;
        $email = $_SESSION['email']; //getting this email using session
        $encpass = password_hash($password, PASSWORD_BCRYPT);
        $update_pass = "UPDATE students SET code = $code, password = '$encpass' WHERE email = '$email'";
        $run_query = mysqli_query($con, $update_pass);
        if($run_query){

            $info = "You successfully changed your password.";
            $_SESSION['info'] = $info;
            header('Location: studenthome.php');
        }else{
            $errors['db-error'] = "Failed to change your password!";
        }
    }
}

?>
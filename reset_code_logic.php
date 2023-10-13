<?php
session_start();
require_once "studentcontrol.php";

if(isset($_POST['reset_code'])){
    $email = $_SESSION['email'];
    $new_code = rand(100000, 999999);

    $update_code_query = "UPDATE students SET code = '$new_code' WHERE email = '$email'";
    $result = mysqli_query($con, $update_code_query);

    if($result){
        $subject = "New Verification Code";
        $message = "Your new verification code is $new_code";
        $sender = "From: mayanderamos08@gmail.com";

        if(mail($email, $subject, $message, $sender)){
            $_SESSION['info'] = "New verification code sent to your email - $email.";
        } else {
            $_SESSION['info'] = "Error sending new code.";
        }
    } else {
        $_SESSION['info'] = "Error generating new code.";
    }

    header('Location: studentotp.php');
    exit();
}
?>

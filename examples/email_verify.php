<?php
//require 'path/to/loginer.main.php';
require '../loginer.main.php';

$user = new Loginer();

if(isset($_REQUEST['username']) && isset($_REQUEST['code'])){
    $user->emailVerify($_REQUEST['username'],$_REQUEST['code']);
    if($user == true){
        header('Refresh: 10;URL=../');
        echo "Verification Successful !<br>Redirecting in 10 Seconds...";
    }
    else{
        $errors = $user->get_errors();
        foreach ($errors as $error){
            echo $error.'<br>';
        }
    }
}
else{
    echo "Please enter username and verification code for email verification";
}
?>

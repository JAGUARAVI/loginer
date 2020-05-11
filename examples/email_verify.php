<?php
//require 'path/to/loginer.main.php';
require '../loginer.main.php';
$user = new Loginer();
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

?>

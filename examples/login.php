<?php

//require 'path/to/loginer.main.php';
require '../loginer.main.php';

if(isset($_POST['submit'])){
    $user = new Loginer();
    $user_details = $user->login_getDetails($_POST['username'],$_POST['password']);
    if($user_details['status'] == true){
        echo 'Logged in '.$user_details['username'];
    }
    else{
        $errors = $user->get_errors();
        foreach ($errors as $error){
            echo $error.'<br>';
        }
    }
}
?>
<html>
    <body>
        <form method="post">
            <input type="text" name="username" placeholder="Enter name">
            <input type="password" name="password" placeholder="Enter Password">
            <button name="submit">Submit</button>
        </form>
    </body>
</html>

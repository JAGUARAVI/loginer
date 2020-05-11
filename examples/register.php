<?php
require('loginizer.php');
if(isset($_POST['submit'])){
    $user = new Loginer;
    $register = $user->register( $_POST['username'] , $_POST['email'] , $_POST['password_1'] , $_POST['password_2'] );
    if($register == true){
        echo "Success";
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
            <input type="email" name="email" placeholder="Enter Email">
            <input type="password" name="password_1" placeholder="Enter Password">
            <input type="password" name="password_2" placeholder="Repeat Password">
            <button name="submit">Submit</button>
        </form>
    </body>
</html>

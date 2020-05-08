<?php
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require 'vendor/autoload.php';

$mail = new PHPMailer(true);

require ('config/config.ini.php');

class Loginer{

    public $conn;
    public $errors = array();
    public $mail;
    
    public function __construct(){
        
        global $host, $sql_username, $sql_password, $db, $conn, $mail;
        
        if(!isset($host) || !isset($sql_username) || !isset($sql_password) || !isset($db)){
            http_response_code(503);
            die("Incorrect config File !");
        }
        else{
            $this->conn = mysqli_connect($host,$sql_username,$sql_password,$db);
            if ($this->conn->connect_error) {
                http_response_code(503);
                die("Connection failed: " . $this->conn->connect_error);
            }
            $this->mail = $mail;
        }
    }
    
    //Email
    
    public function sendEmail($email,$username,$subject,$body,$altbody){
        $mail = $this->mail ;
        $mail->addAddress($email, $username);
        
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $altbody;
        
        if($mail->send()){
            return true;
        }
        else{
            array_push($this->errors, "Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
        
    }
    
    public function emailVerification_email($email,$username,$code){
        $subject = "Verify Email Address";
        $body = $this->render_email('email-templates/email-verification.phtml',$username,"https://shortli.cf/login/verify?username=$username&code=$code");
        return $this->sendEmail($email,$username,$subject,$body,'');
    }
    
    public function forgotPassword_email($email,$username,$code){
        $subject = "Reset Password";
        $body = $this->render_email('email-templates/reset-password.phtml',$username,"https://shortli.cf/login/reset_password?username=$username&code=$code");
        return $this->sendEmail($email,$username,$subject,$body,'');
    }
    
    //Login
    
    public function login($username,$password){
        $username = mysqli_real_escape_string($this->conn, $username);
        $password = mysqli_real_escape_string($this->conn, $password);
        if (empty($username)) {
            array_push($this->errors, "Username is required");
        }
        if (empty($password)) {
            array_push($this->errors, "Password is required");
        }
        if (count($this->errors) == 0) {
            $query = "SELECT * FROM users WHERE BINARY  username='$username' OR email='$username'";
            $results = mysqli_query($this->conn, $query);
            if (mysqli_num_rows($results) == 1) {
                $user_data = mysqli_fetch_assoc($results);
                if (password_verify($password, $user_data['password']) || base64_encode($password) == $user_data['password']) {
                    return true;
                } else {
                    array_push($this->errors, "Credentials do not match !");
                    return false;
                }
            }
        }
    }
    
    public function login_getDetails($username,$password){
        $username = mysqli_real_escape_string($this->conn, $username);
        $password = mysqli_real_escape_string($this->conn, $password);
        if (empty($username)) {
            array_push($this->errors, "Username is required");
        }
        if (empty($password)) {
            array_push($this->errors, "Password is required");
        }
        if (count($this->errors) == 0) {
            $query = "SELECT * FROM users WHERE BINARY  username='$username' OR email='$username'";
            $results = mysqli_query($this->conn, $query);
            if (mysqli_num_rows($results) == 1) {
                $data = mysqli_fetch_assoc($results);
                if (password_verify($password, $data['password']) || base64_encode($password) == $data['password']) {
                    return array('username'=>$data['username'] , 'id'=>$data['id'] , 'email'=>$data['email'] , 'roles'=>$data['roles'] , 'status'=>true);
                } else {
                    array_push($this->errors, "Credentials do not match !");
                    return false;
                }
            }
            elseif(mysqli_num_rows($results) > 1){
                array_push($this->errors, "Database Error ! Please contact administrator of this website !");
            }
            else {
                    array_push($this->errors, "Credentials do not match !");
                    return false;
            }
        }
    }
    
    //Register
    
    public function register($username,$email,$password_1,$password_2){
        
        $username = mysqli_real_escape_string($this->conn, $username);
        $email = mysqli_real_escape_string($this->conn, $email);
        $password_1 = mysqli_real_escape_string($this->conn, $password_1);
        $password_2 = mysqli_real_escape_string($this->conn, $password_2);
        
        if (empty($username)) { array_push($this->errors, "Username is required !"); }
        if (empty($email)) { array_push($this->errors, "Email is required !"); }
        if (empty($password_1)) { array_push($this->errors, "Password is required !"); }
        if ($password_1 != $password_2) {array_push($this->errors, "The two passwords do not match !");}
        if (strlen($password_1) < 8) {array_push($this->errors, "Password must be greater than 7 digits !");}
        
        
        $user_check_query = "SELECT * FROM users WHERE BINARY username='$username' OR email='$email' LIMIT 1";
        $result = mysqli_query($this->conn, $user_check_query);
        $user = mysqli_fetch_assoc($result);

        if ($user) { // if user exists
            if ($user['username'] === $username) {
                array_push($this->errors, "Username already exists");
            }
    
            if ($user['email'] === $email) {
                array_push($this->errors, "Email already exists");
            }
        }
        
        if (count($this->errors) == 0) {
                $password = password_hash($password_1, PASSWORD_DEFAULT);
                $code = md5(rand(100,999));
                
                $query = "INSERT INTO users (username, email, password, code)
                VALUES('$username', '$email', '$password', '$code')";
                
                if (!mysqli_query($this->conn, $query)) {
                    array_push($this->errors, "System Error: " . mysqli_error($this->conn));
                    return false;
                } else {
                    //return true;
                    $return = $this->emailVerification_email($email,$username,$code);
                    return $return;
                }
        } else{
            return false;
        }
    }
    
    public function register_getDetails($username,$email,$password_1,$password_2){
        
        $username = mysqli_real_escape_string($this->conn, $username);
        $email = mysqli_real_escape_string($this->conn, $email);
        $password_1 = mysqli_real_escape_string($this->conn, $password_1);
        $password_2 = mysqli_real_escape_string($this->conn, $password_2);
        
        if (empty($username)) { array_push($this->errors, "Username is required !"); }
        if (empty($email)) { array_push($this->errors, "Email is required !"); }
        if (empty($password_1)) { array_push($this->errors, "Password is required !"); }
        if ($password_1 != $password_2) {array_push($this->errors, "The two passwords do not match !");}
        if (strlen($password_1) < 8) {array_push($this->errors, "Password must be greater than 7 digits !");}
        
        
        $user_check_query = "SELECT * FROM users WHERE BINARY username='$username' OR email='$email' LIMIT 1";
        $result = mysqli_query($this->conn, $user_check_query);
        $user = mysqli_fetch_assoc($result);

        if ($user) { // if user exists
            if ($user['username'] === $username) {
                array_push($this->errors, "Username already exists");
            }
    
            if ($user['email'] === $email) {
                array_push($this->errors, "Email already exists");
            }
        }
        
        if (count($this->errors) == 0) {
                $password = password_hash($password_1, PASSWORD_DEFAULT);
                $code = md5(rand(100,999));
                
                $query = "INSERT INTO users (username, email, password, code)
                VALUES('$username', '$email', '$password', '$code')";
                
                if (!mysqli_query($this->conn, $query)) {
                    array_push($this->errors, "System Error: " . mysqli_error($this->conn));
                    return false;
                } else {
                    $query = "SELECT * FROM users WHERE BINARY  username='$username' OR email='$username'";
                    $results = mysqli_query($this->conn, $query);
                    if (mysqli_num_rows($results) == 1) {
                        $data = mysqli_fetch_assoc($results);
                        $return = $this->emailVerification_email($email,$username,$code);
                        if($return == true){
                            return array('username'=>$data['username'] , 'id'=>$data['id'] , 'email'=>$data['email'] , 'roles'=>$data['roles'] , 'status'=>true);
                        }
                        else{
                            return false;
                        }
                    }
                    elseif(mysqli_num_rows($results) > 1){
                        array_push($this->errors, "Database Error ! Please contact administrator of this website !");
                        return false;
                    }
                    else {
                        array_push($this->errors, "No user Found !");
                        return false;
                    }
                }
        } else{
            return false;
        }
    }
    
    //Password Reset
    
    public function reset_password_sendCode($email){
        $email = mysqli_real_escape_string($this->conn, $email);
        
        if (empty($email)) { array_push($this->errors, "Email is required !"); }
        
        $user_check_query = "SELECT * FROM users WHERE email='$email' LIMIT 1";
        $result = mysqli_query($this->conn, $user_check_query);
        $user = mysqli_fetch_assoc($result);

        if (!$user) { // if user does not exists
            array_push($this->errors, "Account with Email: $email does not exists !");
        }
        
        if (count($this->errors) == 0){
            $code = md5(rand(100,999).rand(100,999));
            $query = "UPDATE users SET code = '$code' WHERE email='$email'";
            mysqli_query($this->conn, $query);
            if (!mysqli_query($this->conn, $query)) {
                array_push($this->errors, "System Error: " . mysqli_error($this->conn));
                return false;
            } else {
                $return = $this->forgotPassword_email($email,$username,$code);
                return $return;
            }
        }
        
    }
    
    public function reset_password_getCode($email){
        $email = mysqli_real_escape_string($this->conn, $email);
        
        if (empty($email)) { array_push($this->errors, "Email is required !"); }
        
        $user_check_query = "SELECT * FROM users WHERE email='$email' LIMIT 1";
        $result = mysqli_query($this->conn, $user_check_query);
        $user = mysqli_fetch_assoc($result);

        if (!$user) { // if user does not exists
            array_push($this->errors, "Account with Email: $email does not exists !");
        }
        
        if (count($this->errors) == 0){
            $code = md5(rand(100,999).rand(100,999));
            $query = "UPDATE users SET code = '$code' WHERE email='$email'";
            mysqli_query($this->conn, $query);
            if (!mysqli_query($this->conn, $query)) {
                array_push($this->errors, "System Error: " . mysqli_error($this->conn));
                return false;
            } else {
                return $code;
            }
        }
        
    }
    
    public function reset_password($email,$code,$password_1,$password_2){
        $email = mysqli_real_escape_string($this->conn, $username);
        $password_1 = mysqli_real_escape_string($this->conn, $password_1);
        $password_2 = mysqli_real_escape_string($this->conn, $password_2);
        $code = mysqli_real_escape_string($this->conn, $code);
        
        if (empty($email)) {
            array_push($this->errors, "Email is required");
        }
        if (empty($password_1)) {
            array_push($this->errors, "Password is required");
        }
        if (empty($password_2)) {
            array_push($this->errors, "Repeat Password is required");
        }
        if (empty($code)) {
            array_push($this->errors, "Verification code is required");
        }
        if($password_1 != $password_2){
            array_push($this->errors, "Both the passwords are not same");
        }
        if(strlen($password_1)<8){
            array_push($this->errors, "Password must be minimum of 8 digits");
        }
        
        if (count($this->errors) == 0) {
            $query = "SELECT * FROM users WHERE email='$email' AND BINARY code='$code'";
            $results = mysqli_query($this->conn, $query);
            if (mysqli_num_rows($results) == 1) {
                $user_data = mysqli_fetch_assoc($results);
                $id = $user_data['id'];
                $password = password_hash($password_1, PASSWORD_DEFAULT);
                $update_query = "UPDATE users SET password = '$password' WHERE id='$id'";
                if (!mysqli_query($this->conn, $update_query)) {
                    array_push($this->errors, "System Error: " . mysqli_error($this->conn));
                    return false;
                } else {
                    return true;
                }
                 
            }
            else{
                return false;
                array_push($this->errors, "Verification code is wrong.");
            }
        }
        else{
            return false;
            //array_push($this->errors, "Error Found !");
        }
        
    }
    
    //Misc
    
    public function getDetails($username){
        $username = mysqli_real_escape_string($this->conn, $username);
        $query = "SELECT * FROM users WHERE BINARY  username='$username' OR email='$username'";
        $results = mysqli_query($this->conn, $query);
        if (mysqli_num_rows($results) == 1) {
            $data = mysqli_fetch_assoc($results);
            return array('username'=>$data['username'] , 'id'=>$data['id'] , 'email'=>$data['email'] , 'roles'=>$data['roles'] , 'status'=>true);
        }
        elseif(mysqli_num_rows($results) > 1){
            array_push($this->errors, "Database Error ! Please contact administrator of this website !");
            return false;
        }
        else {
            array_push($this->errors, "No user Found !");
            return false;
        }
    }
    
    public function emailVerify($username,$code){
        $username = mysqli_real_escape_string($this->conn, $username);
        $code = mysqli_real_escape_string($this->conn, $code);
        
        if (empty($username)) {
            array_push($this->errors, "Username is required");
        }
        if (empty($code)) {
            array_push($this->errors, "Verification code is required");
        }
        
        if (count($this->errors) == 0) {
            $query = "SELECT * FROM users WHERE BINARY username='$username' AND BINARY code='$code'";
            $results = mysqli_query($this->conn, $query);
            if (mysqli_num_rows($results) == 1) {
                $user_data = mysqli_fetch_assoc($results);
                $id = $user_data['id'];
                $update_query = "UPDATE users SET verification = '1' WHERE id='$id'";
                if (!mysqli_query($this->conn, $update_query)) {
                    array_push($this->errors, "System Error: " . mysqli_error($this->conn));
                    return false;
                } else {
                    return true;
                }
                 
            }
            else{
                return false;
                array_push($this->errors, "DB error !");
            }
        }
        else{
            return false;
            array_push($this->errors, "Error Found !");
        }
        
    }
    
    public function getVerification($username){
        $username = mysqli_real_escape_string($this->conn, $username);
        
        if (empty($username)) {
            array_push($this->errors, "Username is required !");
        }
        
        if (count($this->errors) == 0) {
            $query = "SELECT * FROM users WHERE BINARY username='$username'";
            $results = mysqli_query($this->conn, $query);
            if (mysqli_num_rows($results) == 1) {
                $user_data = mysqli_fetch_assoc($results);
                if($user_data['verification'] == '1' || $user_data['verification'] == 1){
                    return true;
                }
                else{
                    array_push($this->errors, "User not Verified !");
                    return false;
                }
            }
            else{
                array_push($this->errors, "DB error");
                return false;
            }
        }
        else{
            return false;
        }
    }
    
    public function get_errors(){
        return $this->errors;
    }
    
    private function random_strings($length_of_string) {
            // String of all alphanumeric character 
            $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'; 
            
            // Shufle the $str_result and returns substring 
            // of specified length 
            return substr(str_shuffle($str_result),  
                               0, $length_of_string); 
    }
    
    public function render_email($filename, $username, $link) {
        ob_start();
        include $filename;
        return ob_get_clean();
    }
    
    
    
    public function __destruct(){
        mysqli_close($this->conn);
    }
}

?>

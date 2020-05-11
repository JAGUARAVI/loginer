<?php
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


//Enter MySql Host
$host = 'localhost';

//Enter MySql Username
$sql_username = 'username';

//Enter MySql Password
$sql_password = 'sEcReT1@3';

//Enter MySql Database Name
$db = 'mYdB';

$currentWebDir = 'https://shortli.cf/login/';


//Email Configuration

//Server Settings
//$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Turn on for verbose output - if mail is not going turn on
$mail->isSMTP();                                            // Send using SMTP
$mail->Host       = 'smtp.example.com';                    // Set the SMTP server to send through
$mail->SMTPAuth   = true;                                   // Enable SMTP authentication
$mail->Username   = 'no-reply@example.com';                     // SMTP username
$mail->Password   = 'sEcReTpAsSwOrD1@3$5^';                               // SMTP password
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
$mail->Port       = 587;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

//Other Settings
$mail->setFrom('no-reply@example.com', 'No Reply Example_Brand Mail');
$mail->addReplyTo('info@example.com', 'Info Example');
//$mail->addCC('cc@example.com');                           //Optional
//$mail->addBCC('bcc@example.com');                         //Optional

$mail->isHTML(true);

//$mail->DKIM_domain = 'example.com';                          //Optional
//$mail->DKIM_private = 'your.key';               //Optional
//$mail->DKIM_selector = 'your_selector';                              //Optional
//$mail->DKIM_passphrase = '';                                //Optional
//$mail->DKIM_identity = $mail->From;                         //Optional


?>

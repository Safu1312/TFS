<?php
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$email = $_POST['email'];
$data = json_decode($_POST['data'], true);

$mail = new PHPMailer(true);
try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
    $mail->SMTPAuth = true;
    $mail->Username = 'tfms.srilanka@gmail.com'; // SMTP username
    $mail->Password = 'tnhv ytrx lvlh dfkz'; // SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
    $mail->Port = 587; // TCP port to connect to

    // Recipients
    $mail->setFrom('tfms.srilanka@gmail.com', 'Traffic Police');
    $mail->addAddress($email); // Add driver's email address
    $mail->addReplyTo('no-reply@example.com', 'No Reply');

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Fine Issued';
    $mail->Body = "<h1>Fine Issued</h1>
                  <p>Dear {$data['drivername']},</p>
                  <p>You have been issued a fine for the following:</p>
                  <p>License ID: {$data['license']}</p>
                  <p>Vehicle No: {$data['vehicleno']}</p>
                  <p>Fine Amount: {$data['fineamount']}</p>
                  <p>Please pay the fine within the stipulated time.</p>
                  <p>Thank you.</p>";

    $mail->send();
    echo 'Email sent successfully!';
} catch (Exception $e) {
    echo 'Email could not be sent. Mailer Error: ' . $mail->ErrorInfo;
}
?>
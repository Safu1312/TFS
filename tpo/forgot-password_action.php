<?php
// Include connection and PHPMailer files
include "../connection.php";
session_start();

// Manually include PHPMailer files (if not using Composer)
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if the form was submitted
if (isset($_POST['forgot_password'])) {
    $email = $_POST['email'];
    $_SESSION['email'] = $email;

    // Validate email input
    if (empty($email)) {
        header("Location: forgot-password.php?error=Email Address is required!");
        exit();
    }

    // Check if the email exists in the TPO table
    $emailCheckQuery = "SELECT * FROM tpo WHERE officer_email = ?";
    $stmt = $conn->prepare($emailCheckQuery);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if email exists in the database
    if ($result->num_rows > 0) {
        $code = rand(999999, 111111);

        // Update the TPO table with the verification code
        $updateQuery = "UPDATE tpo SET code = ? WHERE officer_email = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("is", $code, $email);
        if ($stmt->execute()) {

            // PHPMailer setup
            $mail = new PHPMailer(true);
            try {
                // SMTP configuration
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // SMTP server
                $mail->SMTPAuth = true;
                $mail->Username = 'tfms.srilanka@gmail.com'; // Your Gmail address
                $mail->Password = 'tnhv ytrx lvlh dfkz';     // Your Gmail app password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Sender and recipient
                $mail->setFrom('tfms.srilanka@gmail.com', 'TFMS-SL');
                $mail->addAddress($email);

                // Email content
                $mail->isHTML(true);
                $mail->Subject = 'Traffic Police Officer Verification Code';
                $mail->Body = "
                    <html>
                    <head>
                        <style>
                            body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
                            .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
                            h1 { color: #2a9df4; font-size: 24px; }
                            p { color: #333; font-size: 16px; }
                            .code { font-size: 20px; color: #e74c3c; font-weight: bold; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <h1>Verification Code</h1>
                            <p>Dear Traffic Police Officer,</p>
                            <p>Your verification code is:</p>
                            <p class='code'>$code</p>
                            <p>If you did not request this, please contact support.</p>
                            <p>Regards,<br>Traffic Fine Management System</p>
                        </div>
                    </body>
                    </html>
                ";

                // Send the email
                if ($mail->send()) {
                    header("Location: verification-code.php?success=Verification code has been sent to $email");
                    exit();
                } else {
                    header("Location: forgot-password.php?error=Failed to send verification code.");
                    exit();
                }
            } catch (Exception $e) {
                // Handle PHPMailer exceptions
                header("Location: forgot-password.php?error=Mailer Error: {$mail->ErrorInfo}");
                exit();
            }
        } else {
            header("Location: forgot-password.php?error=Failed to update the database with verification code.");
            exit();
        }
    } else {
        header("Location: forgot-password.php?error=No account is associated with this email address.");
        exit();
    }
} else {
    // If the form is not submitted correctly
    header("Location: forgot-password.php");
    exit();
}
?>

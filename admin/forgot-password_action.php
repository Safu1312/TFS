<?php
// Include connection to your database
include "../connection.php";
session_start();

// Manually include PHPMailer files
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the form was submitted
if (isset($_POST['forgot_password'])) {
    $email = $_POST['email'];
    $_SESSION['email'] = $email;

    // Validate email
    if (empty($email)) {
        header("Location: forgot-password.php?error=Email Address is required!");
        exit();
    } else {
        // Query to check if the email exists in the database
        $emailCheckQuery = "SELECT * FROM admins WHERE admin_email = ?";
        $stmt = $conn->prepare($emailCheckQuery);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Email exists, generate a verification code
            $code = rand(999999, 111111);
            
            // Update the admin table with the code
            $updateQuery = "UPDATE admins SET code = ? WHERE admin_email = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("is", $code, $email);
            if ($stmt->execute()) {
                // Create an instance of PHPMailer
                $mail = new PHPMailer(true);

                try {
                    // SMTP server configuration
                    $mail->isSMTP();                                            // Send using SMTP
                    $mail->Host       = 'smtp.gmail.com';                       // Set the SMTP server
                    $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
                    $mail->Username   = 'tfms.srilanka@gmail.com';              // SMTP username
                    $mail->Password   = 'tnhv ytrx lvlh dfkz';                  // SMTP password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption
                    $mail->Port       = 587;                                    // TCP port to connect to

                    // Sender and recipient
                    $mail->setFrom('tfms.srilanka@gmail.com', 'TFMS-SL'); // Sender's email and name
                    $mail->addAddress($email);                                  // Add recipient's email

                    // Content of the email
                    $mail->isHTML(true);                                        // Set email format to HTML
                    $mail->Subject = 'Email Verification Code';
                    $mail->Body    = "
                    <html>
                    <head>
                        <style>
                            body {
                                font-family: Arial, sans-serif;
                                margin: 0;
                                padding: 0;
                                background-color: #f4f4f4;
                            }
                            .container {
                                max-width: 600px;
                                margin: 20px auto;
                                background-color: #ffffff;
                                padding: 20px;
                                border-radius: 8px;
                                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                            }
                            h1 {
                                color: #2a9df4;
                                font-size: 24px;
                            }
                            p {
                                color: #333333;
                                font-size: 16px;
                                line-height: 1.6;
                            }
                            .code {
                                font-size: 20px;
                                color: #e74c3c;
                                font-weight: bold;
                            }
                            .footer {
                                margin-top: 20px;
                                color: #777;
                                font-size: 14px;
                            }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <h1>Email Verification Code</h1>
                            <p>Dear Admin,</p>
                            <p>To reset your password, please use the following verification code:</p>
                            <p class='code'>$code</p>
                            <p>If you did not request this, please ignore this email.</p>
                            <p class='footer'>Best regards,<br>The TFMS-SL Team</p>
                        </div>
                    </body>
                    </html>
                    ";

                    // Send email
                    if ($mail->send()) {
                        header("Location: verification-code.php?success=We've sent a verification code to your Email: $email");
                        exit();
                    } else {
                        header("Location: forgot-password.php?error=Failed to send verification code.");
                        exit();
                    }

                } catch (Exception $e) {
                    header("Location: forgot-password.php?error=Mailer Error: {$mail->ErrorInfo}");
                    exit();
                }

            } else {
                header("Location: forgot-password.php?error=Failed to update database with verification code.");
                exit();
            }

        } else {
            header("Location: forgot-password.php?error=No Account is Associated With this Email Address!");
            exit();
        }
    }
} else {
    header("Location: forgot-password.php");
    exit();
}
?>

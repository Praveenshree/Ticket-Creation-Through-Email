<?php
session_start();
require 'vendor/autoload.php';  // Make sure to include PHPMailer's autoload file

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Database connection
$servername = "localhost";
$username = "root"; // Change to your MySQL username
$password = ""; // Change to your MySQL password
$dbname = "keywordconfigmaster"; // Change to your database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    // Check if email exists in the database
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Generate a unique reset token
        $token = bin2hex(random_bytes(50));
        
        // Insert token into the database or update existing token
        $stmt = $conn->prepare("UPDATE users SET verification_token = ? WHERE email = ?");
        $stmt->bind_param("ss", $token, $email);
        $stmt->execute();

        // Create the reset link
        $resetLink = "http://yourdomain.com/reset-password.php?token=" . $token;

        // Send email using PHPMailer
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; // Your SMTP server
            $mail->SMTPAuth   = true;
            $mail->Username   = 'Add Your Email'; // Your email
            $mail->Password   = 'Add Your App Password'; // Your email password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            //Recipients
            $mail->setFrom('no-reply@yourdomain.com', 'Mailer');
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body    = "Click here to reset your password: <a href='$resetLink'>$resetLink</a>";

            $mail->send();
            $_SESSION['status'] = "Password reset link has been sent to your email.";
            header("Location: reset-password.php");
            exit(0);
        } catch (Exception $e) {
            $_SESSION['status'] = "Failed to send reset link. Mailer Error: {$mail->ErrorInfo}";
            header("Location: forgot-password.php");
            exit(0);
        }
    } else {
        $_SESSION['status'] = "Email address not found.";
        header("Location: forgot-password.php");
        exit(0);
    }
}

$conn->close();

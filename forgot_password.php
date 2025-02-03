<?php
session_start();
require 'vendor/autoload.php'; // Include PHPMailer's autoload file

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "keywordconfigmaster";

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

        // Insert token into the database
        $stmt = $conn->prepare("UPDATE users SET verification_token = ? WHERE email = ?");
        $stmt->bind_param("ss", $token, $email);
        $stmt->execute();

        // Create the reset link
        $resetLink = "http://localhost/admin/reset-password.php?token=" . $token;

        // Send email using PHPMailer
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'Add Your Email ';
            $mail->Password   = 'Add Your App Password';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('no-reply@yourdomain.com', 'Support Team');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body    = "Click here to reset your password: <a href='$resetLink'>$resetLink</a>";

            $mail->send();
            $_SESSION['status'] = "Password reset link has been sent to your email.";
            header("Location: forgot_password.php");
            exit(0);
        } catch (Exception $e) {
            $_SESSION['status'] = "Failed to send reset link. Mailer Error: {$mail->ErrorInfo}";
            header("Location: forgot_password.php");
            exit(0);
        }
    } else {
        $_SESSION['status'] = "Email address not found.";
        header("Location: forgot_password.php");
        exit(0);
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Password reset request page">
    <meta name="author" content="">

    <title>Forgot Password</title>

    <!-- Custom fonts for this template-->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #1f1f2e;
            color: #f1f1f1;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .card {
            background-color: #2d2d3a;
            border: none;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .btn-primary {
            background-color: #5cbdd7;
            border: none;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            border-radius: 8px;
        }

        .btn-primary:hover {
            background-color: #47a7b9;
        }

        .form-control {
            border-radius: 8px;
            padding: 14px;
            background-color: #f1f1f1;
            color: #333;
            box-shadow: inset 0 1px 5px rgba(0, 0, 0, 0.1);
            border: none;
        }

        .form-control:focus {
            outline: none;
            border: 2px solid #5cbdd7;
            box-shadow: none;
        }

        .alert {
            border-radius: 8px;
            padding: 12px;
            font-size: 15px;
        }

        h1 {
            font-weight: 600;
            color: #f1f1f1;
        }

        p {
            color: #d1d1d1;
        }

        .small a {
            color: #5cbdd7;
            transition: color 0.3s ease;
        }

        .small a:hover {
            color: #47a7b9;
        }

    </style>
</head>

<body>

    <div class="container">

        <div class="col-lg-6">
            <div class="card shadow-lg p-5">
                <div class="card-body">
                    <div class="text-center">
                        <h1 class="h4 text-light mb-4">Forgot Your Password?</h1>
                        <p class="text-muted">Enter your email below to receive a password reset link.</p>
                    </div>
                    <form class="user" action="forgot_password.php" method="POST">
                        <div class="form-group">
                            <input type="email" name="email" class="form-control shadow-sm" id="exampleInputEmail" placeholder="Enter Email Address..." required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block shadow">
                            Reset Password
                        </button>
                    </form>

                    <?php
                    if (isset($_SESSION['status'])) {
                        echo '<div class="alert alert-info mt-4" role="alert">' . $_SESSION['status'] . '</div>';
                        unset($_SESSION['status']);
                    }
                    ?>

                    <hr>
                    <div class="text-center">
                        <a class="small" href="register.php">Create an Account!</a>
                    </div>
                    <div class="text-center">
                        <a class="small" href="login.php">Already have an account? Login!</a>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

</body>

</html>

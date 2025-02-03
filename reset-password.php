<?php
session_start();
require 'vendor/autoload.php'; // Include PHPMailer's autoload file

// Database connection
$servername = "localhost";
$username = "root"; // Change to your MySQL username
$password = ""; // Change to your MySQL password
$dbname = "keywordconfigmaster"; // Change to your database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Check if token is valid
    $stmt = $conn->prepare("SELECT * FROM users WHERE verification_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $_SESSION['status'] = "Invalid or expired token.";
        header("Location: forgot_password.php");
        exit(0);
    }
} else {
    $_SESSION['status'] = "No token provided.";
    header("Location: forgot_password.php");
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    // Update password in the database
    $stmt = $conn->prepare("UPDATE users SET password = ?, verification_token = NULL WHERE verification_token = ?");
    $stmt->bind_param("ss", $new_password, $token);
    $stmt->execute();

    $_SESSION['status'] = "Password has been reset successfully.";
    header("Location: login.php");
    exit(0);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #6e8efb, #a777e3);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .card {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            padding: 30px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            color: white;
        }

        .card h2 {
            font-weight: 600;
            color: white;
            margin-bottom: 1.5rem;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            padding: 15px;
            color: white;
        }

        .form-control:focus {
            box-shadow: 0px 4px 10px rgba(255, 255, 255, 0.5);
            background: rgba(255, 255, 255, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.5);
            outline: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #6e8efb, #9d47f0);
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: bold;
            box-shadow: 0px 12px 20px rgba(31, 38, 135, 0.37);
            transition: all 0.4s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #9d47f0, #6e8efb);
            transform: translateY(-5px);
            box-shadow: 0px 15px 30px rgba(31, 38, 135, 0.5);
        }

        .alert-info {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            border-radius: 10px;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="card mx-auto">
            <h2 class="text-center">Reset Your Password</h2>
            <?php if (isset($_SESSION['status'])): ?>
                <div class="alert alert-info">
                    <?php echo $_SESSION['status']; unset($_SESSION['status']); ?>
                </div>
            <?php endif; ?>
            <form action="" method="POST">
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
            </form>
        </div>
    </div>

</body>

</html>

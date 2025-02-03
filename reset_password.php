<?php
session_start();
$connection = mysqli_connect("localhost", "root", "", "keywordconfigmaster");

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Check if token is valid
    $query = "SELECT * FROM password_resets WHERE token='$token'";
    $query_run = mysqli_query($connection, $query);

    if (mysqli_num_rows($query_run) > 0) {
        // Token is valid, allow user to reset password
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Reset Password</title>
            <!-- Add any CSS/JS includes for styling -->
        </head>
        <body>
            <div class="container">
                <h2>Reset Password</h2>
                <?php
                if (isset($_SESSION['status'])) {
                    echo '<div class="alert alert-danger">' . $_SESSION['status'] . '</div>';
                    unset($_SESSION['status']);
                }
                ?>
                <form action="update_password.php" method="POST">
                    <input type="hidden" name="token" value="<?php echo $token; ?>">
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Reset Password</button>
                </form>
            </div>
        </body>
        </html>
        <?php
    } else {
        $_SESSION['status'] = "Invalid or expired token.";
        header('Location: forgot-password.html');
    }
} else {
    $_SESSION['status'] = "Unauthorized access.";
    header('Location: forgot-password.html');
}

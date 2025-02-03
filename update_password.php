<?php
session_start();
$connection = mysqli_connect("localhost", "root", "", "keywordconfigmaster");

if (isset($_POST['token']) && isset($_POST['password']) && isset($_POST['confirm_password'])) {
    $token = $_POST['token'];
    $new_password = mysqli_real_escape_string($connection, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($connection, $_POST['confirm_password']);

    if ($new_password === $confirm_password) {
        // Get the email associated with the token
        $query = "SELECT * FROM password_resets WHERE token='$token'";
        $query_run = mysqli_query($connection, $query);
        if (mysqli_num_rows($query_run) > 0) {
            $row = mysqli_fetch_assoc($query_run);
            $email = $row['email'];

            // Update the user's password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT); // Hash the password
            $update_password_query = "UPDATE users SET password='$hashed_password' WHERE email='$email'";
            $update_password_run = mysqli_query($connection, $update_password_query);

            if ($update_password_run) {
                // Delete the token after successful reset
                $delete_token_query = "DELETE FROM password_resets WHERE token='$token'";
                mysqli_query($connection, $delete_token_query);

                $_SESSION['success'] = "Password successfully updated!";
                header('Location: login.html');
            } else {
                $_SESSION['status'] = "Failed to update password.";
                header('Location: reset_password.php?token='.$token);
            }
        } else {
            $_SESSION['status'] = "Invalid token.";
            header('Location: forgot-password.html');
        }
    } else {
        $_SESSION['status'] = "Passwords do not match.";
        header('Location: reset_password.php?token='.$token);
    }
} else {
    $_SESSION['status'] = "Unauthorized access.";
    header('Location: forgot-password.html');
}
?>

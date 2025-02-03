<?php
session_start();
include('includes/db.php'); // Include your database connection file

if (isset($_POST['reset_password_btn'])) {
    // Get the data from the form
    $token = mysqli_real_escape_string($mysqli, $_POST['token']);
    $new_password = mysqli_real_escape_string($mysqli, $_POST['password']);

    // Debug: Log the received token
    error_log("Received token: $token");

    // Validate token
    $query = "SELECT * FROM users WHERE verification_token='$token' AND token_expiry > NOW()";
    $query_run = mysqli_query($mysqli, $query);

    if (mysqli_num_rows($query_run) > 0) {
        // Valid token, proceed to update the password

        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update the password and clear the token
        $update_query = "UPDATE users SET password='$hashed_password', verification_token=NULL, token_expiry=NULL WHERE verification_token='$token'";
        if (mysqli_query($mysqli, $update_query)) {
            $_SESSION['status'] = "Your password has been reset successfully.";
            header("Location: login.php"); // Redirect to the login page
            exit(0);
        } else {
            $_SESSION['status'] = "Password reset failed. Please try again.";
            header("Location: forgot-password.php"); // Redirect back to the forgot password page
            exit(0);
        }
    } else {
        // Invalid or expired token
        $_SESSION['status'] = "Invalid or expired token.";
        error_log("Invalid token or token expired for token: $token");
        header("Location: forgot-password.php");
        exit(0);
    }
} else {
    // No form submission
    $_SESSION['status'] = "Invalid request.";
    header("Location: forgot-password.php");
    exit(0);
}
?>

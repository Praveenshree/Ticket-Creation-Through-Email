<?php
session_start();

// Database connection
$connection = mysqli_connect('localhost', 'root', '', 'keywordconfigmaster');
if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

// Register new user
if (isset($_POST['registerbtn'])) {
    $first_name = $_POST['FirstName'];
    $last_name = $_POST['LastName'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $cpassword = $_POST['confirmpassword'];

    if ($password === $cpassword) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepared statement to insert user
        $query = "INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)";
        $stmt = $connection->prepare($query);
        $stmt->bind_param('ssss', $first_name, $last_name, $email, $hashed_password);

        if ($stmt->execute()) {
            $_SESSION['success'] = "User Profile Added";
            header('Location: register.php');
        } else {
            $_SESSION['status'] = "User Profile Not Added: " . $stmt->error;
            header('Location: register.php');
        }

        $stmt->close();
    } else {
        $_SESSION['status'] = "Password and Confirm Password Do Not Match";
        header('Location: register.php');
    }
}

// Update user data
if (isset($_POST['updatebtn'])) {
    $id = $_POST['edit_id'];
    $email = $_POST['edit_email'];
    $password = $_POST['edit_password'];

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepared statement to update user
    $query = "UPDATE users SET email=?, password=? WHERE id=?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('ssi', $email, $hashed_password, $id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Your data is updated";
        header('Location: register.php');
    } else {
        $_SESSION['status'] = "Your data is not updated: " . $stmt->error;
        header('Location: register.php');
    }

    $stmt->close();
}

// Delete user data
if (isset($_POST['delete_btn'])) {
    $id = $_POST['delete_btn'];

    // Prepared statement to delete user
    $query = "DELETE FROM users WHERE id=?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Your data is deleted successfully";
        echo "success"; 
    } else {
        $_SESSION['status'] = "Your data is not deleted: " . $stmt->error;
        echo "error"; 
    }

    $stmt->close();
}

// Login user
if (isset($_POST['login_btn'])) {
    $email_login = $_POST['email'];
    $password_login = $_POST['password'];

    // Prepared statement to select user
    $query = "SELECT id, password FROM users WHERE email=?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('s', $email_login);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $hashed_password);

    if ($stmt->num_rows > 0) {
        $stmt->fetch();
        // Debug: print out values for comparison
        error_log("Debug Info: email_login = $email_login, password_login = $password_login, hashed_password = $hashed_password");
        // Verify the password
        if (password_verify($password_login, $hashed_password)) {
            $_SESSION['first_name'] = $email_login;
            header('Location: index.php');
        } else {
            $_SESSION['status'] = 'Email or Password is invalid';
            header('Location: login.php');
        }
    } else {
        $_SESSION['status'] = 'Email or Password is invalid';
        header('Location: login.php');
    }

    $stmt->close();
}


if (isset($_POST['login_btn'])) {
    $email = mysqli_real_escape_string($mysqli, $_POST['email']);
    $password = mysqli_real_escape_string($mysqli, $_POST['password']);

    $query = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($mysqli, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id']; // Set session variable
            header('Location: profile.php'); // Redirect to profile page
            exit();
        } else {
            $_SESSION['status'] = "Invalid password.";
        }
        
    } else {
        $_SESSION['status'] = "User not found.";
    }

    header('Location: login.php'); // Redirect back to login page
    exit();
}

$connection->close();
?>

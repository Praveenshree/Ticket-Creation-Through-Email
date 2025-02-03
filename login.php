<?php
session_start();
include('includes/header.php');
?>

<style>
    /* Add the background image to the body */
    body {
        background-image: url('images/ticketcreation.jpg'); /* Adjust the path if necessary */
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        height: 100vh; /* Full height */
        margin: 0; /* Remove any default margin */
        display: flex; /* Center content horizontally and vertically */
        align-items: center; /* Center content vertically */
        justify-content: center; /* Center content horizontally */
    }

    .container {
        max-width: 500px; /* Maximum width of the card container */
        width: 100%; /* Full width of the card container */
        padding: 20px; /* Padding around the card */
        
    }

    .card {
        background-color: rgba(255, 255, 255, 0.2) !important; /* Semi-transparent background */
        border: none; /* Remove card border */
        backdrop-filter: blur(10px); /* Optional: blur effect for a more elegant look */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Light box shadow for subtle effect */
        width: 600%; /* Full width of the container */
        max-width: 800px; /* Maximum width of the card */
        padding: 20px; /* Padding inside the card */
    }

    .card-body {
        padding: 2rem; /* Add padding inside the card */
    }

    .form-control {
        background-color: rgba(255, 255, 255, 0.5); /* Semi-transparent background for form controls */
        color: #000; /* Black text for readability */
    }

    .form-control::placeholder {
        color: rgba(0, 0, 0, 0.6); /* Semi-transparent placeholder text */
    }

    .btn-user {
        background-color: #4e73df;
        border: none;
    }

    .btn-user:hover {
        background-color: #2e59d9;
    }

    .custom-control-label {
        color: #000; /* Black text for custom checkbox label */
    }

    .alert {
        background-color: rgba(255, 0, 0, 0.6); /* Semi-transparent alert box */
        color: #fff; /* White text for alert box */
    }
</style>

<div class="container">
    <!-- Outer Row -->
    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-10 col-md-10">
            <div class="card o-hidden shadow-lg my-5">
                <div class="card-body">
                    <!-- Nested Row within Card Body -->
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <div class="py-5">
                                <div class="text-center">
                                    <h1 class="h4 text-gray-900 mb-4">Login</h1>
                                    <?php
                                    // Display success message if it exists (e.g., after registration)
                                    if (isset($_SESSION['success']) && $_SESSION['success'] != '') {
                                        echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
                                        unset($_SESSION['success']);
                                    }

                                    // Display error or other status messages
                                    if (isset($_SESSION['status']) && $_SESSION['status'] != '') {
                                        echo '<div class="alert">' . $_SESSION['status'] . '</div>';
                                        unset($_SESSION['status']);
                                    }
                                    ?>
                                </div>
                                <form class="user" action="code.php" method="POST">
                                    <div class="form-group">
                                        <input type="email" name="email" class="form-control form-control-user"
                                            id="Email" aria-describedby="emailHelp"
                                            placeholder="Enter Email Address..." required>
                                    </div>
                                    <div class="form-group">
                                        <input type="password" name="password" class="form-control form-control-user"
                                            id="Password" placeholder="Password" required>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox small">
                                            <input type="checkbox" class="custom-control-input" id="customCheck">
                                            <label class="custom-control-label" for="customCheck">Remember Me</label>
                                        </div>
                                    </div>
                                    <button type="submit" name="login_btn" class="btn btn-primary btn-user btn-block">Login</button>
                                    <br>
                                    <div class="text-center">
                                        <a class="small" href="forgot-password.html">Forgot Password?</a>
                                    </div>
                                    <div class="text-center">
                                        <a class="small" href="register.html">Create an Account!</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include('includes/scripts.php');
?>


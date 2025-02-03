
<?php
$servername = "localhost";
$username = "root"; // Update with your MySQL username
$password = ""; // Update with your MySQL password
$dbname = "keywordconfigmaster";

// Create connection
$mysqli = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// $conn=mysqli_connect("localhost","root","","keywordconfigmaster");
// if(!$conn)
// {
//     die("Failed to DB:".mysqli_error());
// }
// else{
//     echo "Success";
// }
?>

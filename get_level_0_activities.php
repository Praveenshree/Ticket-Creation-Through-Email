<?php
include('db.php');

// Fetch Level 0 activities
$query = "SELECT id, description FROM master_activity WHERE level = '0'";
$result = mysqli_query($mysqli, $query);

$activities = [];
while ($row = mysqli_fetch_assoc($result)) {
    $activities[] = $row;
}

mysqli_close($mysqli);

// Return JSON response
header('Content-Type: application/json');
echo json_encode($activities);
?>

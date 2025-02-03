<?php
include('db.php');

if (isset($_POST['level'])) {
    $level = intval($_POST['level']) - 1; // Fetch the parent level activities
    $query = "SELECT id, description FROM master_activity WHERE level = '$level'";
    $result = mysqli_query($mysqli, $query);

    $activities = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $activities[] = ['id' => $row['id'], 'description' => $row['description']];
    }

    echo json_encode($activities);
    mysqli_close($mysqli);
}
?>
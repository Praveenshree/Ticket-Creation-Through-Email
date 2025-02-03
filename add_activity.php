<?php
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = $conn->real_escape_string($_POST['description']);
    $level = $conn->real_escape_string($_POST['level']);
    $parent = $conn->real_escape_string($_POST['parent']);

    if (empty($description) || empty($level) || empty($parent)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit();
    }

    $sql = "INSERT INTO master_activity (description, level, parent) VALUES ('$description', '$level', '$parent')";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['status' => 'success', 'message' => 'Activity added successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database insert failed: ' . $conn->error]);
    }

    $conn->close();
}
?>

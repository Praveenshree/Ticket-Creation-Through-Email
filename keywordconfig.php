<?php
include('db.php');

$response = ['status' => 'error', 'message' => 'Failed to add activity.'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $position = $mysqli->real_escape_string($_POST['position']);
    $keywordIn = $mysqli->real_escape_string($_POST['keywordIn']);
    $assignAttribute = $mysqli->real_escape_string($_POST['assignAttribute']);
    $attributeValue = $mysqli->real_escape_string($_POST['attributeValue']);
    $rank = (int)$_POST['rank']; // Ensure rank is an integer

    $sql = "INSERT INTO keyword (position, keyword_in, assign_attribute, attribute_value, rank) 
            VALUES ('$position', '$keywordIn', '$assignAttribute', '$attributeValue', $rank)";

    if ($mysqli->query($sql) === TRUE) {
        $response = ['status' => 'success', 'message' => 'Activity added successfully.'];
    } else {
        $response['message'] = 'Error: ' . $mysqli->error; // More detailed error message
    }
}

$mysqli->close();

echo json_encode($response);
?>

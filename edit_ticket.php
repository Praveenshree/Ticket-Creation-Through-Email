<?php
include('db.php');
include('security.php');

if (isset($_POST['ticket_oid'])) {
    $ticket_oid = $_POST['ticket_oid'];

    $query = "SELECT * FROM ticket WHERE ticket_oid = $ticket_oid";
    $result = mysqli_query($mysqli, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'No ticket found.']);
    }

    mysqli_close($mysqli);
}
?>

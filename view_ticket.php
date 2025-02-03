<?php
include('db.php');
include('security.php');

if (isset($_POST['ticket_oid'])) {
    $ticket_oid = $_POST['ticket_oid'];

    $query = "SELECT * FROM ticket WHERE ticket_oid = $ticket_oid";
    $result = mysqli_query($mysqli, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        echo "
            <p><strong>Ticket OID:</strong> {$row['ticket_oid']}</p>
            <p><strong>Message OID:</strong> {$row['message_oid']}</p>
            <p><strong>Activity :</strong> {$row['activity_oid']}</p>
            <p><strong>Activity Type :</strong> {$row['activity_type_oid']}</p>
            <p><strong>Activity Value :</strong> {$row['activity_value_oid']}</p>
            <p><strong>Priority :</strong> {$row['priority_oid']}</p>
            <p><strong>Priority Requester OID:</strong> {$row['priority_requester_oid']}</p>
            <p><strong>Title:</strong> {$row['title']}</p>
        ";
    } else {
        echo "No ticket found.";
    }

    mysqli_close($mysqli);
}
?>

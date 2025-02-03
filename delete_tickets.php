<?php
include('db.php');
include('security.php');

if (isset($_POST['ticket_oid'])) {
    $ticket_oid = $_POST['ticket_oid'];

    $query = "DELETE FROM ticket WHERE ticket_oid = $ticket_oid";
    $result = mysqli_query($mysqli, $query);

    if ($result) {
        echo "Ticket deleted successfully.";
    } else {
        echo "Error deleting ticket: " . mysqli_error($mysqli);
    }

    mysqli_close($mysqli);
}
?>

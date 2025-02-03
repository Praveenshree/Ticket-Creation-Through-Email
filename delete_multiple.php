<?php
include('db.php');
include('security.php');

if (isset($_POST['ticket_oids'])) {
    $ticket_oids = $_POST['ticket_oids'];

    if (!empty($ticket_oids)) {
        // Convert the array of ticket_oids to a comma-separated string for the SQL query
        $ticket_oid_string = implode(',', array_map('intval', $ticket_oids));

        // Prepare the DELETE query
        $query = "DELETE FROM ticket WHERE ticket_oid IN ($ticket_oid_string)";
        $result = mysqli_query($mysqli, $query);

        if ($result) {
            echo json_encode(['success' => 'Tickets deleted successfully.']);
        } else {
            echo json_encode(['error' => 'Error deleting tickets: ' . mysqli_error($mysqli)]);
        }
    } else {
        echo json_encode(['error' => 'No tickets selected for deletion.']);
    }

    mysqli_close($mysqli);
}
?>

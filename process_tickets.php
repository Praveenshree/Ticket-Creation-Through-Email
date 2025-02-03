<?php
include('db.php');

$action = $_REQUEST['action'];

switch ($action) {
    case 'add':
        $priority = $_POST['ticketPriority'];
        $activityOID = $_POST['activity_oid'];
        $activityTypeOID = $_POST['activity_type_oid'];
        $activityValueOID = $_POST['activity_value_oid'];

        $query = "INSERT INTO ticket (priority_oid, activity_oid, activity_type_oid, activity_value_oid, title) VALUES ('$priority', '$activityOID', '$activityTypeOID', '$activityValueOID', 'New Ticket')";
        $result = mysqli_query($mysqli, $query);
        if (!$result) {
            die('Error adding ticket: ' . mysqli_error($mysqli));
        }
        echo 'Ticket added successfully';
        break;

    case 'edit':
        $ticketID = $_POST['ticket_oid'];
        $priority = $_POST['priority_oid'];
        $activityOID = $_POST['activity_oid'];
        $activityTypeOID = $_POST['activity_type_oid'];
        $activityValueOID = $_POST['activity_value_oid'];

        $query = "UPDATE ticket SET priority_oid='$priority', activity_oid='$activityOID', activity_type_oid='$activityTypeOID', activity_value_oid='$activityValueOID' WHERE ticket_oid='$ticketID'";
        $result = mysqli_query($mysqli, $query);
        if (!$result) {
            die('Error updating ticket: ' . mysqli_error($mysqli));
        }
        echo 'Ticket updated successfully';
        break;

    case 'delete':
        $ticketID = $_POST['ticket_oid'];
        $query = "DELETE FROM ticket WHERE ticket_oid='$ticketID'";
        $result = mysqli_query($mysqli, $query);
        if (!$result) {
            die('Error deleting ticket: ' . mysqli_error($mysqli));
        }
        echo 'Ticket deleted successfully';
        break;
        case 'delete_multiple':
            $ids = $_POST['ids'];
            if (is_array($ids) && count($ids) > 0) {
                // Sanitize IDs
                $ids = implode(',', array_map('intval', $ids));
                
                // Log the IDs being processed for deletion
                error_log('Deleting tickets with IDs: ' . $ids);
        
                // Perform the deletion
                $query = "DELETE FROM ticket WHERE ticket_oid IN ($ids)";
                $result = mysqli_query($mysqli, $query);
                
                if ($result) {
                    echo 'Tickets deleted successfully';
                } else {
                    error_log('Error deleting tickets: ' . mysqli_error($mysqli));
                    die('Error deleting tickets: ' . mysqli_error($mysqli));
                }
            } else {
                echo 'No tickets selected for deletion';
            }
            break;
        

    case 'get':
        $ticketID = $_GET['ticket_oid'];
        $query = "SELECT * FROM ticket WHERE ticket_oid='$ticketID'";
        $result = mysqli_query($mysqli, $query);
        if (!$result) {
            die('Error fetching ticket details: ' . mysqli_error($mysqli));
        }
        $ticket = mysqli_fetch_assoc($result);
        echo json_encode($ticket);
        break;
}
?>

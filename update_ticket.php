<?php
include('db.php');
include('security.php');

if (isset($_POST['ticket_oid'])) {
    $ticket_oid = $_POST['ticket_oid'];
    $activity_oid = $_POST['activity_oid'];
    $activity_type_oid = $_POST['activity_type_oid'];
    $activity_value_oid = $_POST['activity_value_oid'];
    $priority_oid = $_POST['priority_oid'];

    $query = "UPDATE ticket SET 
                activity_oid = '$activity_oid', 
                activity_type_oid = '$activity_type_oid', 
                activity_value_oid = '$activity_value_oid', 
                priority_oid = '$priority_oid' 
              WHERE ticket_oid = $ticket_oid";
    
    $result = mysqli_query($mysqli, $query);

    if ($result) {
        echo "Ticket updated successfully.";
    } else {
        echo "Error updating ticket: " . mysqli_error($mysqli);
    }

    mysqli_close($mysqli);
}
?>

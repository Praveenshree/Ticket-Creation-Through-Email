<?php
include('db.php');

// Function to create tickets
function createTickets($mysqli) {
    // Fetch keywords
    $keywordQuery = "SELECT keyword FROM keyword";
    $keywordResult = mysqli_query($mysqli, $keywordQuery);

    if (!$keywordResult) {
        die('Error fetching keywords: ' . mysqli_error($mysqli));
    }

    // Prepare an array to store keywords
    $keywords = [];
    while ($row = mysqli_fetch_assoc($keywordResult)) {
        $keywords[] = $row['keyword'];
    }

    // Convert keywords array to a string for SQL LIKE clause
    $keywordsString = implode('%\' OR emails.email_body LIKE \'%', $keywords);

    // Fetch classified emails based on keywords
    $classifiedQuery = "SELECT email_subject FROM emails 
                        WHERE emails.email_body LIKE '%$keywordsString%'";
    $classifiedResult = mysqli_query($mysqli, $classifiedQuery);

    if (!$classifiedResult) {
        die('Error fetching classified emails: ' . mysqli_error($mysqli));
    }

    // Insert tickets
    while ($emailRow = mysqli_fetch_assoc($classifiedResult)) {
        $emailSubject = mysqli_real_escape_string($mysqli, $emailRow['email_subject']);

        // Example priority and OIDs, these should be replaced with actual logic or defaults
        $ticketPriority = 'medium';
        $activity_oid = 0;
        $activity_type_oid = 0;
        $activity_value_oid = 0;

        $insertTicketQuery = "INSERT INTO ticket (title, priority_oid, activity_oid, activity_type_oid, activity_value_oid) 
                              VALUES ('$emailSubject', '$ticketPriority', '$activity_oid', '$activity_type_oid', '$activity_value_oid')";

        if (!mysqli_query($mysqli, $insertTicketQuery)) {
            echo 'Error creating ticket: ' . mysqli_error($mysqli);
        }
    }

    mysqli_close($mysqli);
}

// Run the function to create tickets
createTickets($mysqli);
?>

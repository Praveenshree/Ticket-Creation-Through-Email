<?php
include('db.php'); // Database connection
include('security.php');
include("includes/header.php");
include("includes/navbar.php");

// Fetch keywords from the `keyword` table
$mysqli = new mysqli('localhost', 'root', '', 'keywordconfigmaster');

// Check for database connection errors
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$query = "SELECT * FROM keyword";
$keywordResult = mysqli_query($mysqli, $query);

$ticketsCreated = 0;

// Display all email subjects for debugging
$allSubjectsQuery = "SELECT subject FROM classified_emails";
$allSubjectsResult = mysqli_query($mysqli, $allSubjectsQuery);

if ($allSubjectsResult) {
    echo "<h3>Available email subjects:</h3>";
    while ($subjectRow = mysqli_fetch_assoc($allSubjectsResult)) {
        echo "- " . htmlspecialchars($subjectRow['subject']) . "<br>";
    }
} else {
    echo "Error fetching all email subjects: " . mysqli_error($mysqli) . "<br>";
}

if ($keywordResult) {
    while ($row = mysqli_fetch_assoc($keywordResult)) {
        // Process each keyword to create a ticket
        $position = $row['position'];
        $keywordIn = trim($row['keyword_in']); // Remove any extra spaces
        $assignAttribute = $row['assign_attribute'];
        $attributeValue = $row['attribute_value'];
        $activityTypeOid = $row['activity_type_oid'];
        $rank = $row['rank'];

        // Debug: Log keyword details
        echo "Processing keyword: '$keywordIn' <br>";

        // Fetch the subject of the classified email related to the keyword_in (case-insensitive)
        $emailQuery = "SELECT subject FROM emails WHERE subject LIKE '%$keywordIn%'";
        $emailResult = mysqli_query($mysqli, $emailQuery);

        if ($emailResult) {
            if (mysqli_num_rows($emailResult) > 0) {
                // Fetch all matching email subjects
                while ($emailRow = mysqli_fetch_assoc($emailResult)) {
                    $emailSubject = $emailRow['subject'];

                    // Debug: Log found email subject
                    echo "Found email subject: '$emailSubject' for keyword: '$keywordIn' <br>";

                    // Check if a ticket with the same details already exists
                    $checkQuery = "SELECT * FROM ticket
                                   WHERE activity_oid = '$assignAttribute'
                                   AND activity_type_oid = '$activityTypeOid'
                                   AND activity_value_oid = '$attributeValue'
                                   AND title = '$emailSubject'";
                    $checkResult = mysqli_query($mysqli, $checkQuery);

                    if ($checkResult) {
                        if (mysqli_num_rows($checkResult) == 0) {
                            // Insert a new ticket if none exists with the same details
                            $ticketQuery = "INSERT INTO ticket (message_oid, activity_oid, activity_type_oid, activity_value_oid, priority_oid, title)
                                            VALUES (0, '$assignAttribute', '$activityTypeOid', '$attributeValue', 1, '$emailSubject')"; // Use dummy values for message_oid, priority_oid

                            if (mysqli_query($mysqli, $ticketQuery)) {
                                $ticketsCreated++;
                            } else {
                                // Debug: Log error message for ticket insertion failure
                                echo "Error inserting ticket: " . mysqli_error($mysqli) . "<br>";
                            }
                        } else {
                            // Debug: Log message if ticket already exists
                            echo "Ticket already exists for: '$emailSubject' <br>";
                        }
                    } else {
                        // Debug: Log error message for ticket check failure
                        echo "Error checking existing ticket: " . mysqli_error($mysqli) . "<br>";
                    }
                }
            } else {
                // Debug: Log message if no email subject is found
                echo "No email subject found for keyword: '$keywordIn' <br>";
            }
        } else {
            // Debug: Log error message for email fetching failure
            echo "Error fetching email subject: " . mysqli_error($mysqli) . "<br>";
        }
    }
} else {
    // Debug: Log error message for keyword fetching failure
    echo "Error fetching keywords: " . mysqli_error($mysqli) . "<br>";
}

mysqli_close($mysqli);

if ($ticketsCreated > 0) {
    echo "<p>$ticketsCreated tickets created based on keywords!</p>";
} else {
    echo "<p>No tickets were created.</p>";
}

include("includes/footer.php");
?>

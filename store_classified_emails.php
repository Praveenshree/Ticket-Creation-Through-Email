<?php
// store_classified_emails.php

include('db.php'); // Make sure this file contains your database connection code

// Function to store classified email in the database
function storeClassifiedEmail($mysqli, $email) {
    $query = "INSERT INTO classified_emails (uid, subject, date_received, from_email, to_email, cc_email) 
              VALUES (?, ?, ?, ?, ?, ?)";
    
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("isssss", 
            $email['uid'], 
            $email['subject'], 
            $email['date_received'], 
            $email['from_email'], 
            $email['to_email'], 
            $email['cc_email']
        );

        if (!$stmt->execute()) {
            error_log("Error storing classified email: " . $stmt->error, 3, 'C:/xampp/htdocs/admin/php_error.log');
        }

        $stmt->close();
    } else {
        error_log("Failed to prepare SQL statement for storing classified email: " . $mysqli->error, 3, 'C:/xampp/htdocs/admin/php_error.log');
    }
}

$data = json_decode(file_get_contents('php://input'), true);

if (is_array($data)) {
    foreach ($data as $email) {
        storeClassifiedEmail($mysqli, $email);
    }
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
}
?>

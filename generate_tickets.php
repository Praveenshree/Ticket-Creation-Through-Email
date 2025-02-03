<?php
// generate_tickets.php

include('db.php'); // Ensure database connection is properly initialized
include('security.php'); // Include any necessary security checks

// Function to fetch keyword configurations for classified emails
function getKeywordConfigurations($mysqli) {
    $keywords = [];
    $query = "SELECT * FROM keyword ORDER BY rank ASC";
    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $keywords[] = $row;
        }
        $result->free();
    } else {
        error_log("Error fetching keywords: " . $mysqli->error, 3, 'C:/xampp/htdocs/admin/php_error.log');
    }
    return $keywords;
}

// Function to build WHERE clause based on keywords
function buildWhereClause($keywords, &$params, &$types) {
    $conditions = [];
    foreach ($keywords as $keyword) {
        $position = strtolower($keyword['position']);
        $keyword_in = '%' . $keyword['keyword_in'] . '%';
        
        switch ($position) {
            case 'to':
                $conditions[] = "to_email LIKE ?";
                $params[] = $keyword_in;
                $types .= 's';
                break;
            case 'cc':
                $conditions[] = "cc_email LIKE ?";
                $params[] = $keyword_in;
                $types .= 's';
                break;
            case 'subject':
                $conditions[] = "subject LIKE ?";
                $params[] = $keyword_in;
                $types .= 's';
                break;
            case 'body':
                $conditions[] = "message LIKE ?";
                $params[] = $keyword_in;
                $types .= 's';
                break;
            default:
                // Handle other positions if needed
                break;
        }
    }
    
    if (!empty($conditions)) {
        return "(" . implode(" OR ", $conditions) . ")";
    } else {
        return "1"; // No filtering if no keywords
    }
}

// Function to fetch filtered emails for ticket generation
function fetchFilteredEmails($mysqli, $whereClause, $params, $types) {
    $query = "SELECT uid, subject, date_received, from_email, to_email, cc_email 
              FROM emails 
              WHERE $whereClause 
              ORDER BY date_received DESC";
    
    if ($stmt = $mysqli->prepare($query)) {
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            error_log("Error executing query: " . $stmt->error, 3, 'C:/xampp/htdocs/admin/php_error.log');
            return [];
        }
        
        $result = $stmt->get_result();
        $emails = [];
        
        while ($row = $result->fetch_assoc()) {
            $emails[] = $row;
        }
        
        $stmt->close();
        return $emails;
    } else {
        error_log("Failed to prepare SQL statement: " . $mysqli->error, 3, 'C:/xampp/htdocs/admin/php_error.log');
        return [];
    }
}

// Function to insert a ticket into the database if it does not already exist
function createTicketIfNotExists($mysqli, $subject, $description, $priority, $email) {
    // Check if a ticket with the same subject already exists
    $query = "SELECT COUNT(*) FROM tickets WHERE subject = ?";
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param('s', $subject);
        if (!$stmt->execute()) {
            error_log("Error checking for existing ticket: " . $stmt->error, 3, 'C:/xampp/htdocs/admin/php_error.log');
            return;
        }
        
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        if ($count > 0) {
            // Ticket with the same subject already exists
            return;
        }
    } else {
        error_log("Failed to prepare SQL statement: " . $mysqli->error, 3, 'C:/xampp/htdocs/admin/php_error.log');
        return;
    }

    // Insert the new ticket
    $query = "INSERT INTO ticket (subject, description, priority, email, created_at) VALUES (?, ?, ?, ?, NOW())";
    
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param('ssss', $subject, $description, $priority, $email);
        if (!$stmt->execute()) {
            error_log("Error creating ticket: " . $stmt->error, 3, 'C:/xampp/htdocs/admin/php_error.log');
        } else {
            error_log("Ticket created: $subject", 3, 'C:/xampp/htdocs/admin/php_error.log');
        }
        $stmt->close();
    } else {
        error_log("Failed to prepare SQL statement: " . $mysqli->error, 3, 'C:/xampp/htdocs/admin/php_error.log');
    }
}

// Main script logic
$keywords = getKeywordConfigurations($mysqli);
$params = [];
$types = '';
$whereClause = buildWhereClause($keywords, $params, $types);
$filteredEmails = fetchFilteredEmails($mysqli, $whereClause, $params, $types);

foreach ($filteredEmails as $email) {
    $subject = $email['subject'];
    $description = "Email Details: " . 
                    "Subject: " . $subject . ", " . 
                    "From: " . $email['from_email'] . ", " . 
                    "To: " . $email['to_email'] . ", " . 
                    "CC: " . $email['cc_email'] . ", " . 
                    "Received On: " . $email['date_received'];
    $priority = "Medium"; // You can adjust priority as needed
    $emailAddress = $email['from_email']; // Use email address for contact

    createTicketIfNotExists($mysqli, $subject, $description, $priority, $emailAddress);
}

// Output for debugging purposes
echo "Tickets have been generated.";
?>

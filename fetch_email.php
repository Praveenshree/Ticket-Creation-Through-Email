<?php
include('security.php');

error_log('fetch_emails.php started');

// Set your preferred time zone
date_default_timezone_set('Asia/Kolkata'); // Replace with your desired time zone

// Fetch environment variables or use default values
$imap_server = getenv('IMAP_SERVER') ?: '{imap.gmail.com:993/imap/ssl}INBOX';
$email_address = getenv('EMAIL_ADDRESS') ?: 'Add Your Email Adress';
$password = getenv('EMAIL_PASSWORD') ?: 'Add Your App Password'; 

// Database connection
$conn = new mysqli("localhost", "root", "", "keywordconfigmaster");

if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
} else {
    error_log("Database connection successful");
}

// Function to store email in the database
function storeEmailInDatabase($email_uid, $subject, $date, $from_email, $to_email, $message) {
    global $conn;

    // Check if the email is already in the database
    $query = "SELECT COUNT(*) FROM emails WHERE uid = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Failed to prepare statement: " . $conn->error);
        return;
    }

    $stmt->bind_param("s", $email_uid);
    if (!$stmt->execute()) {
        error_log("Failed to execute statement: " . $stmt->error);
        return;
    }

    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        // Email already stored
        error_log("Email UID $email_uid already exists in the database");
        return;
    }

    // Insert new email
    $query = "INSERT INTO emails (uid, subject, date_received, from_email, to_email, message, processed)
              VALUES (?, ?, ?, ?, ?, ?, TRUE)";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Failed to prepare insert statement: " . $conn->error);
        return;
    }

    $stmt->bind_param("ssssss", $email_uid, $subject, $date, $from_email, $to_email, $message);

    if (!$stmt->execute()) {
        error_log("Failed to store email UID $email_uid: " . $stmt->error);
    } else {
        error_log("Stored Email UID: $email_uid with Subject: $subject");
    }

    $stmt->close();
}

// Function to fetch emails
function fetchEmails($search_criteria) {
    global $imap_server, $email_address, $password, $conn;

    $inbox = imap_open($imap_server, $email_address, $password);
    if (!$inbox) {
        error_log('Cannot connect to email server: ' . imap_last_error());
        die('Cannot connect to email server: ' . imap_last_error());
    } else {
        error_log("Connected to email server successfully");
    }

    $emails = imap_search($inbox, $search_criteria, SE_UID);
    if ($emails === false) {
        error_log('Search failed: ' . imap_last_error());
        imap_close($inbox);
        return;
    } else {
        error_log("Found " . count($emails) . " emails matching the criteria");
    }

    // Get highest UID processed
    $result = $conn->query("SELECT MAX(uid) FROM emails");
    if (!$result) {
        error_log("Failed to fetch highest UID from database: " . $conn->error);
        imap_close($inbox);
        return;
    }

    $row = $result->fetch_row();
    $highest_uid = $row[0] ?? 0;
    error_log("Highest UID in database: $highest_uid");

    foreach ($emails as $email_uid) {
        if ($email_uid <= $highest_uid) {
            error_log("Skipping already processed email UID: $email_uid");
            continue; // Skip emails already processed
        }

        $msgno = imap_msgno($inbox, $email_uid);
        if ($msgno === false) {
            error_log('Invalid message number: ' . imap_last_error());
            continue;
        }

        $overview = imap_fetch_overview($inbox, $msgno, 0);
        if ($overview === false) {
            error_log('Failed to fetch overview for message UID: ' . $email_uid . ' - ' . imap_last_error());
            continue;
        }

        $structure = imap_fetchstructure($inbox, $msgno);
        if ($structure === false) {
            error_log('Failed to fetch structure for message UID: ' . $email_uid . ' - ' . imap_last_error());
            continue;
        }

        $message = '';
        if ($structure && !empty($structure->parts)) {
            foreach ($structure->parts as $part_number => $part) {
                if ($part->subtype == 'HTML' || $part->subtype == 'PLAIN') {
                    $message = imap_fetchbody($inbox, $msgno, $part_number + 1);
                    $encoding = $part->encoding;
                    if ($encoding == 3) $message = base64_decode($message);
                    elseif ($encoding == 4) $message = quoted_printable_decode($message);
                    break;
                }
            }
        } else {
            $message = imap_fetchbody($inbox, $msgno, 1);
            $encoding = isset($structure->encoding) ? $structure->encoding : 0;
            if ($encoding == 3) $message = base64_decode($message);
            elseif ($encoding == 4) $message = quoted_printable_decode($message);
        }

        $from = isset($overview[0]->from) ? htmlspecialchars($overview[0]->from) : 'Unknown Sender';
        preg_match('/<(.+)>/', $from, $matches);
        $from_email = $matches[1] ?? $from;

        $subject = isset($overview[0]->subject) ? htmlspecialchars($overview[0]->subject) : 'No Subject';

        // Convert the date to Y-m-d H:i:s format with proper time zone handling
        $date = isset($overview[0]->date) ? $overview[0]->date : null;

        if ($date) {
            $email_date = new DateTime($date);
            $email_date->setTimezone(new DateTimeZone('Asia/Kolkata')); // Replace with your correct time zone
            $date = $email_date->format('Y-m-d H:i:s');
        }

        $to = isset($overview[0]->to) ? htmlspecialchars($overview[0]->to) : 'Unknown Recipient';
        preg_match('/<(.+)>/', $to, $matches);
        $to_email = $matches[1] ?? $to;

        error_log("Processing Email UID: $email_uid with Subject: $subject");

        storeEmailInDatabase($email_uid, $subject, $date, $from_email, $to_email, $message);
    }

    imap_close($inbox);
    error_log("Disconnected from email server");
}

// Fetch emails from the last 5 minutes
$search_criteria = 'SINCE "' . date('d-M-Y H:i:s', strtotime('-5 minutes')) . '"';
// $search_criteria = 'ALL';

// Uncomment for testing without time filtering
//$search_criteria = 'ALL';
fetchEmails($search_criteria);

$conn->close();
error_log('fetch_emails.php finished');
?>

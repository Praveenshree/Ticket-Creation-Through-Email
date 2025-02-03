<?php
include('db.php'); // Ensure this file connects to your database and defines $conn

// Function to fetch email details from the database
function getEmailDetails($conn, $id) {
    $stmt = $conn->prepare("SELECT email, username, password, host_folder FROM email_master WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Function to connect to the IMAP server
function connectImap($host, $username, $password) {
    $imapStream = imap_open($host, $username, $password);
    if (!$imapStream) {
        die('Failed to connect to IMAP server: ' . imap_last_error());
    }
    return $imapStream;
}

// Function to fetch and display emails
function fetchEmails($imapStream) {
    $emails = imap_search($imapStream, 'ALL', SE_UID, 'utf-8');
    if (!$emails) {
        return [];
    }

    $emailData = [];
    foreach ($emails as $uid) {
        $header = imap_headerinfo($imapStream, $uid);
        $body = imap_fetchbody($imapStream, $uid, 1);
        $emailData[] = [
            'subject' => $header->subject,
            'from' => $header->fromaddress,
            'date' => $header->date,
            'body' => $body
        ];
    }
    return $emailData;
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    die('Invalid ID.');
}

$emailDetails = getEmailDetails($conn, $id);
if (!$emailDetails) {
    die('No email found for the given ID.');
}

$host = $emailDetails['host_folder'];
$username = $emailDetails['username'];
$password = $emailDetails['password'];

$imapStream = connectImap($host, $username, $password);
$emailData = fetchEmails($imapStream);
imap_close($imapStream);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inbox Access</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h1>Inbox Access</h1>
    <?php if (empty($emailData)): ?>
        <p>No emails found.</p>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($emailData as $email): ?>
                <div class="list-group-item">
                    <h5 class="mb-1"><?php echo htmlspecialchars($email['subject']); ?></h5>
                    <p class="mb-1">From: <?php echo htmlspecialchars($email['from']); ?></p>
                    <small><?php echo htmlspecialchars($email['date']); ?></small>
                    <hr>
                    <p><?php echo nl2br(htmlspecialchars($email['body'])); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

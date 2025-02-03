<?php
include('security.php');

// Email server details
$imap_server = '{imap.gmail.com:993/imap/ssl}INBOX'; // Replace with your server details
$email_address = 'Add Your Email';
$password = 'Add Your App Password'; // Use app password if 2FA is enabled

// Check if an email ID is provided
if (!isset($_GET['id'])) {
    echo 'No email ID provided.';
    exit;
}

$email_uid = $_GET['id'];

// Connect to the IMAP server
$inbox = imap_open($imap_server, $email_address, $password);
if (!$inbox) {
    die('Cannot connect to email server: ' . imap_last_error());
}

// Get the message number based on the UID
$msgno = imap_msgno($inbox, $email_uid);
if ($msgno === false) {
    echo 'Invalid message number: ' . imap_last_error();
    imap_close($inbox);
    exit;
}

// Fetch the email overview
$overview = imap_fetch_overview($inbox, $msgno, 0);
$structure = imap_fetchstructure($inbox, $msgno);
$message = '';

// Fetch the email body
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

// Prepare email metadata
$from = isset($overview[0]->from) ? htmlspecialchars($overview[0]->from) : 'Unknown Sender';
$subject = isset($overview[0]->subject) ? htmlspecialchars($overview[0]->subject) : 'No Subject';
$date = isset($overview[0]->date) ? htmlspecialchars($overview[0]->date) : 'No Date';

// Display email details
echo '<h4>Subject: ' . $subject . '</h4>';
echo '<p><strong>From:</strong> ' . $from . '</p>';
echo '<p><strong>Date:</strong> ' . $date . '</p>';
echo '<hr>';
echo '<div>' . $message . '</div>';

// Define MIME type mapping
$mime_types = [
    0 => 'text',
    1 => 'multipart',
    2 => 'message',
    3 => 'application',
    4 => 'audio',
    5 => 'image',
    6 => 'video',
    7 => 'model',
    8 => 'other'
];

// Recursive function to fetch parts and display attachments
function fetch_parts($inbox, $msgno, $parts, $prefix = '') {
    global $mime_types;

    foreach ($parts as $part_number => $part) {
        $current_part_number = $prefix . ($part_number + 1);
        $mime_type = $mime_types[$part->type] . '/' . strtolower($part->subtype);

        if (isset($part->disposition) && strtolower($part->disposition) == 'attachment') {
            // It's an attachment
            $attachment = imap_fetchbody($inbox, $msgno, $current_part_number);
            if ($part->encoding == 3) $attachment = base64_decode($attachment);
            elseif ($part->encoding == 4) $attachment = quoted_printable_decode($attachment);

            // Save the attachment
            $filename = isset($part->dparameters[0]->value) ? $part->dparameters[0]->value : 'unknown';
            $filepath = 'uploads/' . $filename;
            file_put_contents($filepath, $attachment);

            // Provide view and download options
            echo '<p>MIME Type: ' . htmlspecialchars($mime_type) . '</p>'; // Debugging output

            if (strpos($mime_type, 'image') === 0) {
                // Provide view and download options for images
                echo '<a href="' . $filepath . '" target="_blank">View Image</a> | ';
            } elseif ($mime_type === 'application/pdf') {
                // Provide view and download options for PDFs
                echo '<a href="' . $filepath . '" target="_blank">View PDF</a> | ';
            }

            // Provide download link for all other file types
            echo '<a href="' . $filepath . '" download>Download ' . htmlspecialchars($filename) . '</a><br>';
        } elseif (isset($part->parts) && count($part->parts) > 0) {
            // It's a multipart section, recurse
            fetch_parts($inbox, $msgno, $part->parts, $current_part_number . '.');
        }
    }
}

// Check for attachments
if (!empty($structure->parts)) {
    echo '<hr><h5>Attachments:</h5>';
    fetch_parts($inbox, $msgno, $structure->parts);
} else {
    echo '<p>No attachments found.</p>';
}

// Close the IMAP connection
imap_close($inbox);
?>

<?php
// Email server configuration
$imap_server = '{imap.gmail.com:993/imap/ssl}INBOX'; // Replace with your server details
$email_address = 'Add Your Email';
$password = 'Add Your App Password'; // Use app password if 2FA is enabled    praveenbhakare65@gmail.com  ffwzwhbjfesnvueb

if (isset($_POST['id'])) {
    $email_number = intval($_POST['id']);

    // Open an IMAP stream to the email server
    $inbox = imap_open($imap_server, $email_address, $password) or die('Cannot connect to email server: ' . imap_last_error());

    // Fetch email structure and body
    $structure = imap_fetchstructure($inbox, $email_number);
    $message = '';
    if (!empty($structure->parts)) {
        // Get the message body based on MIME type
        foreach ($structure->parts as $part_number => $part) {
            if ($part->subtype == 'HTML') {
                $message = imap_fetchbody($inbox, $email_number, $part_number + 1);
                break;
            } elseif ($part->subtype == 'PLAIN') {
                $message = imap_fetchbody($inbox, $email_number, $part_number + 1);
            }
        }
    } else {
        // No parts, get the message body directly
        $message = imap_fetchbody($inbox, $email_number, 1);
    }

    // Decode base64 or quoted-printable encoded content
    $encoding = isset($structure->encoding) ? $structure->encoding : 0;
    if ($encoding == 3) {
        $message = base64_decode($message);
    } elseif ($encoding == 4) {
        $message = quoted_printable_decode($message);
    }

    // Fetch overview
    $overview = imap_fetch_overview($inbox, $email_number, 0);
    $email = $overview[0];

    // Prepare email details for response
    $response = array(
        'status' => 'success',
        'data' => array(
            'subject' => htmlspecialchars($email->subject),
            'sender' => htmlspecialchars($email->from),
            'sent_at' => htmlspecialchars($email->date),
            'body' => $message
        )
    );

    // Close the IMAP connection
    imap_close($inbox);

    // Return response in JSON format
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>

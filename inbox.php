<?php
// inbox.php

include('db.php'); // Ensure database connection is properly initialized
include('security.php');
include("includes/header.php"); 
include("includes/navbar.php");

/**
 * Function to fetch all emails from the inbox based on search criteria
 *
 * @param string $search_query The search term entered by the user
 * @return string HTML table rows containing the emails
 */
function fetchEmails($search_query) {
    global $mysqli;

    if ($mysqli === null) {
        error_log("Database connection is not initialized.", 3, 'C:/xampp/htdocs/admin/php_error.log');
        return '<tr><td colspan="6">Database connection error.</td></tr>';
    }

    $query = "SELECT uid, subject, date_received, from_email, to_email 
              FROM emails 
              WHERE (subject LIKE ? OR from_email LIKE ? OR to_email LIKE ?) 
              ORDER BY date_received DESC";
              
    $search_like = '%' . $search_query . '%';

    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("sss", $search_like, $search_like, $search_like);

        if (!$stmt->execute()) {
            error_log("Error executing query: " . $stmt->error, 3, 'C:/xampp/htdocs/admin/php_error.log');
            $stmt->close();
            return '<tr><td colspan="6">Error executing query.</td></tr>';
        }

        $result = $stmt->get_result();
        $output = '';
        $serial_number = 1;

        while ($row = $result->fetch_assoc()) {
            $output .= '<tr>';
            $output .= '<td>' . $serial_number . '</td>';
            $output .= '<td><button class="btn btn-primary view-email" data-id="' . htmlspecialchars($row['uid']) . '">View</button></td>';
            $output .= '<td>' . htmlspecialchars($row['subject']) . '</td>';
            $output .= '<td>' . htmlspecialchars($row['date_received']) . '</td>';
            $output .= '<td>' . htmlspecialchars($row['from_email']) . '</td>';
            $output .= '<td>' . htmlspecialchars($row['to_email']) . '</td>';
            $output .= '</tr>';

            $serial_number++;
        }

        $stmt->close();
        return $output;
    } else {
        error_log("Failed to prepare SQL statement: " . $mysqli->error, 3, 'C:/xampp/htdocs/admin/php_error.log');
        return '<tr><td colspan="6">Error preparing query.</td></tr>';
    }
}

/**
 * Function to fetch keyword configurations for classified emails
 *
 * @param mysqli $mysqli The MySQLi connection object
 * @return array Array of keyword configurations
 */
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

/**
 * Function to build WHERE clause based on keywords using prepared statements
 *
 * @param array $keywords Array of keyword configurations
 * @param array &$params Reference to the array of parameters for binding
 * @param string &$types Reference to the string of parameter types for binding
 * @return string The constructed WHERE clause
 */
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
                // Handle other positions if any
                break;
        }
    }
    
    if (!empty($conditions)) {
        return "(" . implode(" OR ", $conditions) . ")";
    } else {
        return "1"; // No filtering if no keywords
    }
}

/**
 * Function to fetch filtered emails for classified inbox using prepared statements
 *
 * @param mysqli $mysqli The MySQLi connection object
 * @param string $whereClause The WHERE clause with placeholders
 * @param array $params The parameters to bind
 * @param string $types The types of the parameters
 * @return string HTML table rows containing the classified emails
 */
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
            return '<tr><td colspan="7">Error executing query.</td></tr>';
        }
        
        $result = $stmt->get_result();
        $output = '';
        $serial_number = 1;
        
        while ($row = $result->fetch_assoc()) {
            $output .= '<tr>';
            $output .= '<td>' . $serial_number . '</td>';
            $output .= '<td><button class="btn btn-primary view-email" data-id="' . htmlspecialchars($row['uid']) . '">View</button></td>';
            $output .= '<td>' . htmlspecialchars($row['subject']) . '</td>';
            $output .= '<td>' . htmlspecialchars($row['date_received']) . '</td>';
            $output .= '<td>' . htmlspecialchars($row['from_email']) . '</td>';
            $output .= '<td>' . htmlspecialchars($row['to_email']) . '</td>';
            $output .= '<td>' . htmlspecialchars($row['cc_email']) . '</td>';  // Add the CC column here
            $output .= '</tr>';
            $serial_number++;
        }
        
        
        $stmt->close();
        return $output;
    } else {
        error_log("Failed to prepare SQL statement: " . $mysqli->error, 3, 'C:/xampp/htdocs/admin/php_error.log');
        return '<tr><td colspan="7">Error preparing query.</td></tr>';
    }
}

// Fetch inbox search criteria
$search_query = isset($_GET['query']) ? trim($_GET['query']) : '';
$start_date = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
$end_date = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';

// Note: The original inbox.php code builds a search criteria string but doesn't use it in the SQL query.
// To implement date filtering, you'd need to modify the fetchEmails function to include date range in the WHERE clause.
// For simplicity, we'll proceed with the existing search functionality.

$emailTableContent = fetchEmails($search_query);

// Fetch classified emails
$keywords = getKeywordConfigurations($mysqli);
$params = [];
$types = '';
$whereClause = buildWhereClause($keywords, $params, $types);
$emailClassifiedTableContent = fetchFilteredEmails($mysqli, $whereClause, $params, $types);

// Assume this is where you classify emails
if (isset($_POST['action']) && $_POST['action'] === 'classify') {
    // Logic to classify email
    // ...

    // After classifying emails, create tickets
    $title = $_POST['emailSubject'];  // Example of how you might retrieve the email subject
    $activity_oid = $_POST['activity_oid'];
    $activity_type_oid = $_POST['activity_type_oid'];
    $activity_value_oid = $_POST['activity_value_oid'];
    $priority_oid = $_POST['priority_oid'];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://yourdomain/create_ticket.php");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'title' => $title,
        'activity_oid' => $activity_oid,
        'activity_type_oid' => $activity_type_oid,
        'activity_value_oid' => $activity_value_oid,
        'priority_oid' => $priority_oid
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $response_data = json_decode($response, true);
    if ($response_data['status'] === 'success') {
        echo "Ticket created successfully!";
    } else {
        echo "Failed to create ticket: " . $response_data['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inbox and Classified Emails</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.2/css/jquery.dataTables.min.css" rel="stylesheet">
    <!-- DataTables Buttons CSS -->
    <link href="https://cdn.datatables.net/buttons/1.7.1/css/buttons.dataTables.min.css" rel="stylesheet">
    <style>
        .container-fluid {
            min-height: 100vh;
            font-size: 0.9rem;
        }
        .email-item {
            cursor: pointer;
        }
        .pagination {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
            font-size: 0.8rem;
        }
        .pagination .page-item.active .page-link {
            z-index: 3;
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-sm {
            font-size: 0.8rem;
        }
        @media (max-width: 768px) {
            .pagination {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Inbox Section -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Inbox</h6>
            </div>
            <div class="card-body">
                <form id="searchForm" method="GET" class="mb-4">
                    <div class="input-group">
                        <input type="text" class="form-control" id="searchInput" name="query" placeholder="Search by sender's email or subject" aria-label="Search emails" value="<?php echo htmlspecialchars($search_query); ?>">
                        <input type="date" class="form-control" id="startDate" name="start_date" placeholder="Start Date" value="<?php echo htmlspecialchars($start_date); ?>">
                        <input type="date" class="form-control" id="endDate" name="end_date" placeholder="End Date" value="<?php echo htmlspecialchars($end_date); ?>">
                        <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Search</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered" id="emailTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Sr. No</th>
                                <th>Action</th>
                                <th>Subject</th>
                                <th>Received On</th>
                                <th>From</th>
                                <th>To</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php echo $emailTableContent; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Classified Emails Section -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Classified Emails</h6>
            </div>
            <div class="card-body">
                <!-- Optional: Add filters or controls specific to classified emails -->
                <div class="table-responsive">
                    <table class="table table-bordered" id="classifiedEmailTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Sr. No</th>
                                <th>Action</th>
                                <th>Subject</th>
                                <th>Received On</th>
                                <th>From</th>
                                <th>To</th>
                                <th>CC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php echo $emailClassifiedTableContent; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

 <!-- Modal for viewing email details -->
<div class="modal fade" id="emailModal" tabindex="-1" aria-labelledby="emailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"> <!-- Centered modal without a specific size -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Email Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="emailContent">
                <!-- Email content will be loaded here via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Optional: Ensure the modal does not exceed a certain size */
    .modal-content {
        width: auto; /* Allow it to adjust based on content */
        max-width: 90vw; /* Set a maximum width of 90% of the viewport width */
        max-height: 80vh; /* Set a maximum height of 80% of the viewport height */
        overflow-y: auto; /* Enable vertical scrolling if content exceeds max height */
    }
</style>



    <!-- JavaScript Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.2/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.print.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable for Inbox
            $('#emailTable').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'copyHtml5',
                        text: '<i class="fas fa-copy"></i> Copy',
                        titleAttr: 'Copy',
                    },
                    {
                        extend: 'csvHtml5',
                        text: '<i class="fas fa-file-csv"></i> CSV',
                        titleAttr: 'CSV',
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Print',
                        titleAttr: 'Print',
                    },
                ],
                // Optional: Add additional configurations for Inbox table
            });

            // Initialize DataTable for Classified Emails
            $('#classifiedEmailTable').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'copyHtml5',
                        text: '<i class="fas fa-copy"></i> Copy',
                        titleAttr: 'Copy',
                    },
                    {
                        extend: 'csvHtml5',
                        text: '<i class="fas fa-file-csv"></i> CSV',
                        titleAttr: 'CSV',
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Print',
                        titleAttr: 'Print',
                    },
                ],
                // Optional: Add additional configurations for Classified Emails table
            });

            // Delegate the click handler to handle "View" buttons in both tables
            $(document).on('click', '.view-email', function() {
                var emailId = $(this).data('id');
                $.ajax({
                    url: 'view_email.php',
                    method: 'GET',
                    data: { id: emailId },
                    success: function(response) {
                        $('#emailContent').html(response);
                        $('#emailModal').modal('show');
                    },
                    error: function() {
                        alert('Failed to fetch email details.');
                    }
                });
            });
        });
    </script>
</body>
</html>

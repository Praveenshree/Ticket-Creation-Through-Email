<?php
include('db.php');
include('security.php');
include("includes/header.php"); 
include("includes/navbar.php");

// Function to fetch keyword configurations
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
function buildWhereClause($keywords) {
    $conditions = [];
    foreach ($keywords as $keyword) {
        $position = strtolower($keyword['position']);
        $keyword_in = $keyword['keyword_in'];
        
        switch ($position) {
            case 'to':
                $conditions[] = "to_email LIKE '%" . $keyword_in . "%'";
                break;
            case 'cc':
                $conditions[] = "cc_email LIKE '%" . $keyword_in . "%'";
                break;
            case 'subject':
                $conditions[] = "subject LIKE '%" . $keyword_in . "%'";
                break;
            case 'body':
                $conditions[] = "message LIKE '%" . $keyword_in . "%'";
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

/// Function to check if an email already exists
function emailExists($mysqli, $subject, $from_email, $date_received) {
    $query = "SELECT COUNT(*) FROM classified_emails WHERE subject = ? AND from_email = ? AND date_received = ?";
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("sss", $subject, $from_email, $date_received);
        if ($stmt->execute()) {
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();
            return $count > 0;
        } else {
            error_log("Failed to execute statement: " . $stmt->error, 3, 'C:/xampp/htdocs/admin/php_error.log');
            return false;
        }
    } else {
        error_log("Failed to prepare SQL statement: " . $mysqli->error, 3, 'C:/xampp/htdocs/admin/php_error.log');
        return false;
    }
}

// Fetch keyword configurations
$keywords = getKeywordConfigurations($mysqli);

// Build WHERE clause
$whereClause = buildWhereClause($keywords);

// Fetch filtered emails
function fetchFilteredEmails($mysqli, $whereClause) {
    $query = "SELECT uid, subject, date_received, from_email, to_email, cc_email 
              FROM emails 
              WHERE $whereClause 
              ORDER BY date_received DESC";
    
    if ($stmt = $mysqli->prepare($query)) {
        if (!$stmt->execute()) {
            error_log("Error executing query: " . $stmt->error, 3, 'C:/xampp/htdocs/admin/php_error.log');
            return '<tr><td colspan="7">Error executing query.</td></tr>';
        }
        
        $result = $stmt->get_result();
        $output = '';
        $serial_number = 1;
        
        while ($row = $result->fetch_assoc()) {
            // Check if the email already exists
            if (!emailExists($mysqli, $row['subject'], $row['from_email'], $row['date_received'])) {
                // Insert into classified_emails table
                $insertQuery = "INSERT INTO classified_emails (uid, subject, date_received, from_email, to_email, cc_email) VALUES (?, ?, ?, ?, ?, ?)";
                if ($insertStmt = $mysqli->prepare($insertQuery)) {
                    $insertStmt->bind_param("isssss", $row['uid'], $row['subject'], $row['date_received'], $row['from_email'], $row['to_email'], $row['cc_email']);
                    if (!$insertStmt->execute()) {
                        error_log("Error inserting email: " . $insertStmt->error, 3, 'C:/xampp/htdocs/admin/php_error.log');
                    }
                    $insertStmt->close();
                } else {
                    error_log("Failed to prepare insert statement: " . $mysqli->error, 3, 'C:/xampp/htdocs/admin/php_error.log');
                }
            }
            
            $output .= '<tr>';
            $output .= '<td>' . $serial_number . '</td>';
            $output .= '<td><button class="btn btn-primary view-email" data-id="' . $row['uid'] . '">View</button></td>';
            $output .= '<td>' . htmlspecialchars($row['subject']) . '</td>';
            $output .= '<td>' . htmlspecialchars($row['date_received']) . '</td>';
            $output .= '<td>' . htmlspecialchars($row['from_email']) . '</td>';
            $output .= '<td>' . htmlspecialchars($row['to_email']) . '</td>';
            $output .= '<td>' . htmlspecialchars($row['cc_email']) . '</td>';
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

$emailTableContent = fetchFilteredEmails($mysqli, $whereClause);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classified Emails</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.2/css/jquery.dataTables.min.css" rel="stylesheet">
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
                            <?php echo $emailTableContent; ?>
                        </tbody>
                    </table>
                </div>
    
                <!-- No pagination needed as we're showing all data -->
            </div>
        </div>
    </div>

    <!-- Modal for viewing email details -->
    <div class="modal fade" id="emailModal" tabindex="-1" aria-labelledby="emailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.2/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#classifiedEmailTable').DataTable();
            
            // View email details
            $('.view-email').on('click', function() {
                var emailId = $(this).data('id');
                $.ajax({
                    url: 'view_email.php',
                    type: 'GET',
                    data: { id: emailId },
                    success: function(response) {
                        $('#emailContent').html(response);
                        $('#emailModal').modal('show');
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error: ", status, error);
                    }
                });
            });
        });
    </script>
</body>
</html>

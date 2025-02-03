<?php
include('db.php'); // Ensure this file connects to your database and defines $conn
include('security.php'); // Ensure this file handles security measures (e.g., input sanitization)

// Function to send JSON responses
function send_json_response($status, $message = '', $data = [])
{
    header('Content-Type: application/json');
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
    exit();
}

// Handle AJAX request to add new email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'add_email') {
        $title = trim(filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING));
        $email = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
        $host_folder = trim(filter_input(INPUT_POST, 'host_folder', FILTER_SANITIZE_STRING));
        $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING));
        $type = trim(filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING));
        $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING));
        $password = trim(filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING));
        $status = trim(filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING));

        if (!$title || !$email || !$host_folder || !$type || !$username || !$password || !$status) {
            send_json_response('error', 'All fields are required.');
        }

        $stmt = $conn->prepare("INSERT INTO email_master (title, email, host_folder, description, type, username, password, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $title, $email, $host_folder, $description, $type, $username, $password, $status);

        if ($stmt->execute()) {
            send_json_response('success', 'Email added successfully.');
        } else {
            send_json_response('error', 'Database insert failed: ' . $stmt->error);
        }

        $stmt->close();
        $conn->close();
    }

    if ($action === 'edit_email') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $title = trim(filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING));
        $email = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
        $host_folder = trim(filter_input(INPUT_POST, 'host_folder', FILTER_SANITIZE_STRING));
        $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING));
        $type = trim(filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING));
        $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING));
        $password = trim(filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING));
        $status = trim(filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING));

        if (!$id || !$title || !$email || !$host_folder || !$type || !$username || !$password || !$status) {
            send_json_response('error', 'All fields are required.');
        }

        $stmt = $conn->prepare("UPDATE email_master SET title = ?, email = ?, host_folder = ?, description = ?, type = ?, username = ?, password = ?, status = ? WHERE id = ?");
        $stmt->bind_param("ssssssssi", $title, $email, $host_folder, $description, $type, $username, $password, $status, $id);

        if ($stmt->execute()) {
            send_json_response('success', 'Email updated successfully.');
        } else {
            send_json_response('error', 'Database update failed: ' . $stmt->error);
        }

        $stmt->close();
        $conn->close();
    }

    if ($action === 'delete_email') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            send_json_response('error', 'Invalid ID.');
        }

        $stmt = $conn->prepare("DELETE FROM email_master WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            send_json_response('success', 'Email deleted successfully.');
        } else {
            send_json_response('error', 'Database delete failed: ' . $stmt->error);
        }

        $stmt->close();
        $conn->close();
    }
}

// Handle AJAX request to get email data for DataTable
if (isset($_GET['action']) && $_GET['action'] == 'get_data') {
    $result = $conn->query("SELECT * FROM email_master");
    
    if ($result) {
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        send_json_response('success', '', $data);
    } else {
        send_json_response('error', 'Database query failed.');
    }

    $conn->close();
}

// Handle AJAX request to get a single email detail for editing
if (isset($_GET['id'])) {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if (!$id) {
        send_json_response('error', 'Invalid ID.');
    }

    $stmt = $conn->prepare("SELECT * FROM email_master WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $email = $result->fetch_assoc();
        send_json_response('success', '', $email);
    } else {
        send_json_response('error', 'Email not found.');
    }

    $stmt->close();
    $conn->close();
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Master DataTable</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <!-- DataTables Buttons CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.7.1/css/buttons.dataTables.min.css">
    <!-- Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .container-fluid {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .card-body {
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="card shadow mb-4 flex-fill">
            <div class="card-header py-3 d-flex justify-content-between">
                <h4 class="m-0 font-weight-bold text-primary">Email Master</h4>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmailModal">
                    <i class="fas fa-plus-circle"></i> Add New Email
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="emailTable" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Email</th>
                                <th>Host Folder</th>
                                <th>Description</th>
                                <th>Type</th>
                                <th>Username</th>
                                <th>Password</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be populated here by DataTables -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add New Email Modal -->
    <div class="modal fade" id="addEmailModal" tabindex="-1" aria-labelledby="addEmailModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addEmailModalLabel"><i class="fas fa-envelope"></i> Add New Email</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addEmailForm">
                        <div class="mb-3">
                            <label for="emailTitle" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="emailTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="emailAddress" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="emailAddress" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="emailHostFolder" class="form-label">Host Folder <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="emailHostFolder" name="host_folder" required>
                        </div>
                        <div class="mb-3">
                            <label for="emailDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="emailDescription" name="description"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="emailType" class="form-label">Type <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="emailType" name="type" required>
                        </div>
                        <div class="mb-3">
                            <label for="emailUsername" class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="emailUsername" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="emailPassword" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="emailPassword" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="emailStatus" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="emailStatus" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Email Modal -->
    <div class="modal fade" id="editEmailModal" tabindex="-1" aria-labelledby="editEmailModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editEmailModalLabel"><i class="fas fa-envelope"></i> Edit Email</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editEmailForm">
                        <input type="hidden" id="editEmailId" name="id">
                        <div class="mb-3">
                            <label for="editEmailTitle" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editEmailTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEmailAddress" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="editEmailAddress" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEmailHostFolder" class="form-label">Host Folder <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editEmailHostFolder" name="host_folder" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEmailDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editEmailDescription" name="description"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editEmailType" class="form-label">Type <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editEmailType" name="type" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEmailUsername" class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editEmailUsername" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEmailPassword" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="editEmailPassword" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEmailStatus" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="editEmailStatus" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS (including Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <!-- DataTables Buttons JS -->
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
    <!-- JSZip -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <!-- PDFMake -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/2.5.1/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/2.5.1/vfs_fonts.js"></script>
    <!-- DataTables Buttons CSS -->
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.print.min.js"></script>

    <script>
        $(document).ready(function() {
            var table = $('#emailTable').DataTable({
                ajax: {
                    url: 'email_master.php?action=get_data',
                    dataSrc: 'data'
                },
                columns: [
                    { data: 'id' },
                    { data: 'title' },
                    { data: 'email' },
                    { data: 'host_folder' },
                    { data: 'description' },
                    { data: 'type' },
                    { data: 'username' },
                    { data: 'password' },
                    { data: 'status' },
                    {
                        data: 'id',
                        render: function(data) {
                            return `
                                <button class="btn btn-info btn-sm edit-email" data-id="${data}">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-danger btn-sm delete-email" data-id="${data}">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </button>
                                <a href="access_inbox.php?id=${data}" class="btn btn-info btn-sm">
                                    <i class="fas fa-envelope"></i> Access Inbox
                                </a>
                            `;
                        }
                    }
                ]
            });

            // Add Email
            $('#addEmailForm').submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: 'email_master.php',
                    type: 'POST',
                    data: $(this).serialize() + '&action=add_email',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#addEmailModal').modal('hide');
                            table.ajax.reload();
                        } else {
                            alert(response.message);
                        }
                    }
                });
            });

            // Edit Email
            $('#emailTable').on('click', '.edit-email', function() {
                var id = $(this).data('id');
                $.get('email_master.php', { id: id }, function(response) {
                    if (response.status === 'success') {
                        var email = response.data;
                        $('#editEmailId').val(email.id);
                        $('#editEmailTitle').val(email.title);
                        $('#editEmailAddress').val(email.email);
                        $('#editEmailHostFolder').val(email.host_folder);
                        $('#editEmailDescription').val(email.description);
                        $('#editEmailType').val(email.type);
                        $('#editEmailUsername').val(email.username);
                        $('#editEmailPassword').val(email.password);
                        $('#editEmailStatus').val(email.status);
                        $('#editEmailModal').modal('show');
                    } else {
                        alert(response.message);
                    }
                });
            });

            $('#editEmailForm').submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: 'email_master.php',
                    type: 'POST',
                    data: $(this).serialize() + '&action=edit_email',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#editEmailModal').modal('hide');
                            table.ajax.reload();
                        } else {
                            alert(response.message);
                        }
                    }
                });
            });

            // Delete Email
            $('#emailTable').on('click', '.delete-email', function() {
                var id = $(this).data('id');
                if (confirm('Are you sure you want to delete this email?')) {
                    $.ajax({
                        url: 'email_master.php',
                        type: 'POST',
                        data: { id: id, action: 'delete_email' },
                        success: function(response) {
                            if (response.status === 'success') {
                                table.ajax.reload();
                            } else {
                                alert(response.message);
                            }
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>

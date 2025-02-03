<?php
include('db.php');
include('security.php');
include("includes/header.php"); 
include("includes/navbar.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickets</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/v/bs5/jszip-3.10.1/dt-2.1.3/b-3.1.1/b-colvis-3.1.1/b-html5-3.1.1/b-print-3.1.1/datatables.min.css" rel="stylesheet">
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
                <!-- <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTicketModal">
                    <i class="fas fa-plus"></i> Add Ticket
                </button> -->
                <button class="btn btn-danger" id="deleteSelectedBtn">
                    <i class="fas fa-trash-alt"></i> Delete Selected
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="ticketsTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
                                <th>Ticket OID</th>
                                <th>Message OID</th>
                                <th>Activity</th>
                                <th>Activity Type</th>
                                <th>Activity Value</th>
                                <th>Priority</th>
                                <th>Priority Requester OID</th>
                                <th>Title</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT * FROM ticket";
                            $result = mysqli_query($mysqli, $query);

                            if (!$result) {
                                die('Error fetching tickets: ' . mysqli_error($mysqli));
                            }

                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr data-id='{$row['ticket_oid']}'>
                                        <td><input type='checkbox' class='select-item' value='{$row['ticket_oid']}'></td>
                                        <td>{$row['ticket_oid']}</td>
                                        <td>{$row['message_oid']}</td>
                                        <td>{$row['activity_oid']}</td>
                                        <td>{$row['activity_type_oid']}</td>
                                        <td>{$row['activity_value_oid']}</td>
                                        <td>{$row['priority_oid']}</td>
                                        <td>{$row['priority_requester_oid']}</td>
                                        <td>{$row['title']}</td>
                                        <td>
                                            <button class='btn btn-primary btn-sm view-btn' data-id='{$row['ticket_oid']}'>
                                                <i class='fas fa-eye'></i> View
                                            </button>
                                            <button class='btn btn-warning btn-sm edit-btn' data-id='{$row['ticket_oid']}'>
                                                <i class='fas fa-edit'></i> Edit
                                            </button>
                                            <button class='btn btn-danger btn-sm delete-btn' data-id='{$row['ticket_oid']}'>
                                                <i class='fas fa-trash-alt'></i> Delete
                                            </button>
                                        </td>
                                    </tr>";
                            }

                            mysqli_close($mysqli);
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Ticket Modal -->
    <div class="modal fade" id="addTicketModal" tabindex="-1" aria-labelledby="addTicketModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addTicketModalLabel">Add New Ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addTicketForm">
                        <div class="mb-3">
                            <label for="ticketPriority" class="form-label">Priority</label>
                            <select class="form-select" id="ticketPriority" name="ticketPriority" required>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="activityOID" class="form-label">Activity OID</label>
                            <input type="number" class="form-control" id="activityOID" name="activity_oid" required>
                        </div>
                        <div class="mb-3">
                            <label for="activityTypeOID" class="form-label">Activity Type OID</label>
                            <input type="number" class="form-control" id="activityTypeOID" name="activity_type_oid" required>
                        </div>
                        <div class="mb-3">
                            <label for="activityValueOID" class="form-label">Activity Value OID</label>
                            <input type="number" class="form-control" id="activityValueOID" name="activity_value_oid" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- View Ticket Modal -->
    <div class="modal fade" id="viewTicketModal" tabindex="-1" aria-labelledby="viewTicketModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewTicketModalLabel">View Ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="viewTicketDetails">
                    <!-- Ticket details will be loaded here dynamically -->
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Ticket Modal -->
    <div class="modal fade" id="editTicketModal" tabindex="-1" aria-labelledby="editTicketModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTicketModalLabel">Edit Ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editTicketForm">
                        <input type="hidden" id="editTicketID" name="ticket_oid">
                        <div class="mb-3">
                            <label for="editTicketPriority" class="form-label">Priority</label>
                            <select class="form-select" id="editTicketPriority" name="priority_oid" required>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editActivityOID" class="form-label">Activity OID</label>
                            <input type="number" class="form-control" id="editActivityOID" name="activity_oid" required>
                        </div>
                        <div class="mb-3">
                            <label for="editActivityTypeOID" class="form-label">Activity Type OID</label>
                            <input type="number" class="form-control" id="editActivityTypeOID" name="activity_type_oid" required>
                        </div>
                        <div class="mb-3">
                            <label for="editActivityValueOID" class="form-label">Activity Value OID</label>
                            <input type="number" class="form-control" id="editActivityValueOID" name="activity_value_oid" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Ticket Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteConfirmationModalLabel">Delete Confirmation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the selected tickets?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/v/bs5/jszip-3.10.1/dt-2.1.3/b-3.1.1/b-colvis-3.1.1/b-html5-3.1.1/b-print-3.1.1/datatables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

    <script>
    $(document).ready(function () {
    var table = $('#ticketsTable').DataTable();

    $('#addTicketForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: 'process_tickets.php',
            type: 'POST',
            data: $(this).serialize() + '&action=add',
            success: function (response) {
                $('#addTicketModal').modal('hide');
                table.ajax.reload();
            },
            error: function (xhr, status, error) {
                console.error('Error adding ticket:', error);
            }
        });
    });

    $('#ticketsTable').on('click', '.edit-btn', function () {
        var ticketID = $(this).data('id');
        $.ajax({
            url: 'process_tickets.php',
            type: 'GET',
            data: { action: 'get', ticket_oid: ticketID },
            success: function (response) {
                var ticket = JSON.parse(response);
                $('#editTicketID').val(ticket.ticket_oid);
                $('#editTicketPriority').val(ticket.priority_oid);
                $('#editActivityOID').val(ticket.activity_oid);
                $('#editActivityTypeOID').val(ticket.activity_type_oid);
                $('#editActivityValueOID').val(ticket.activity_value_oid);
                $('#editTicketModal').modal('show');
            },
            error: function (xhr, status, error) {
                console.error('Error fetching ticket details:', error);
            }
        });
    });

    $('#editTicketForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: 'process_tickets.php',
            type: 'POST',
            data: $(this).serialize() + '&action=edit',
            success: function (response) {
                $('#editTicketModal').modal('hide');
                table.ajax.reload();
            },
            error: function (xhr, status, error) {
                console.error('Error editing ticket:', error);
            }
        });
    });

    $('#ticketsTable').on('click', '.view-btn', function () {
        var ticketID = $(this).data('id');
        $.ajax({
            url: 'process_tickets.php',
            type: 'GET',
            data: { action: 'get', ticket_oid: ticketID },
            success: function (response) {
                var ticket = JSON.parse(response);
                $('#viewTicketDetails').html(
                    '<p><strong>Ticket OID:</strong> ' + ticket.ticket_oid + '</p>' +
                    '<p><strong>Message OID:</strong> ' + ticket.message_oid + '</p>' +
                    '<p><strong>Activity OID:</strong> ' + ticket.activity_oid + '</p>' +
                    '<p><strong>Activity Type OID:</strong> ' + ticket.activity_type_oid + '</p>' +
                    '<p><strong>Activity Value OID:</strong> ' + ticket.activity_value_oid + '</p>' +
                    '<p><strong>Priority:</strong> ' + ticket.priority_oid + '</p>' +
                    '<p><strong>Priority Requester OID:</strong> ' + ticket.priority_requester_oid + '</p>' +
                    '<p><strong>Title:</strong> ' + ticket.title + '</p>'
                );
                $('#viewTicketModal').modal('show');
            },
            error: function (xhr, status, error) {
                console.error('Error fetching ticket details:', error);
            }
        });
    });

    $('#ticketsTable').on('click', '.delete-btn', function () {
        var ticketID = $(this).data('id');
        $('#confirmDeleteBtn').data('id', ticketID);
        $('#deleteConfirmationModal').modal('show');
    });

    $('#confirmDeleteBtn').on('click', function () {
        var ticketID = $(this).data('id');
        $.ajax({
            url: 'process_tickets.php',
            type: 'POST',
            data: { action: 'delete', ticket_oid: ticketID },
            success: function (response) {
                $('#deleteConfirmationModal').modal('hide');
                table.ajax.reload();
            },
            error: function (xhr, status, error) {
                console.error('Error deleting ticket:', error);
            }
        });
    });

    $('#selectAll').on('click', function () {
        var isChecked = $(this).is(':checked');
        $('.select-item').prop('checked', isChecked);
    });

    $('#deleteSelectedBtn').on('click', function () {
        var selectedIDs = [];
        $('.select-item:checked').each(function () {
            selectedIDs.push($(this).val());
        });

        if (selectedIDs.length > 0) {
            $.ajax({
                url: 'process_tickets.php',
                type: 'POST',
                data: { action: 'delete_multiple', ids: selectedIDs },
                success: function (response) {
                    $('#deleteConfirmationModal').modal('hide');
                    table.ajax.reload();
                },
                error: function (xhr, status, error) {
                    console.error('Error deleting tickets:', error);
                }
            });
        } else {
            alert('No tickets selected for deletion.');
        }
    });
});
</script>
</body>
</html>

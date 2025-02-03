<?php
include('db.php');
include('security.php');

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    handleAjaxRequests($mysqli);
    exit;
}

// Include HTML headers and content
include("includes/header.php");
include("includes/navbar.php");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Activity Management</title>
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
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addActivityModal">
                    <i class="fas fa-plus"></i> Add Master Activity
                </button>
                <button class="btn btn-danger" id="deleteSelectedBtn">
                    <i class="fas fa-trash-alt"></i> Delete Selected
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
                                <th>Sr. No.</th>
                                <th>Description</th>
                                <th>Level</th>
                                <th>Parent</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Fetch data from database
                            $query = "SELECT * FROM master_activity";
                            $result = mysqli_query($mysqli, $query);

                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr data-id='{$row['id']}'>
                                        <td><input type='checkbox' class='select-item' value='{$row['id']}'></td>
                                        <td>{$row['id']}</td>
                                        <td>{$row['description']}</td>
                                        <td>{$row['level']}</td>
                                        <td>{$row['parent']}</td>
                                        <td>
                                            <button class='btn btn-primary btn-sm edit-btn' data-bs-toggle='modal' data-bs-target='#editActivityModal' data-id='{$row['id']}' data-description='{$row['description']}' data-level='{$row['level']}' data-parent='{$row['parent']}'>
                                                <i class='fas fa-edit'></i> Edit
                                            </button>
                                            <button class='btn btn-danger btn-sm delete-btn' data-id='{$row['id']}'>
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

    <!-- Add Activity Modal -->
    <div class="modal fade" id="addActivityModal" tabindex="-1" aria-labelledby="addActivityModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addActivityModalLabel">Add New Master Activity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addActivityForm">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label for="activityLevel" class="form-label">Level</label>
                            <select class="form-select" id="activityLevel" name="activityLevel" required>
                                <option value="" disabled selected>Select Level</option>
                                <option value="0">Level 0</option>
                                <option value="1">Level 1</option>
                                <option value="2">Level 2</option>
                                <!-- Add more levels as needed -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="parentActivity" class="form-label">Parent Activity</label>
                            <select class="form-select" id="parentActivity" name="parentActivity" required>
                                <option value="" disabled selected>Select Parent Activity</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="activityDescription" class="form-label">Description</label>
                            <input type="text" class="form-control" id="activityDescription" name="activityDescription" required>
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

    <!-- Edit Activity Modal -->
    <div class="modal fade" id="editActivityModal" tabindex="-1" aria-labelledby="editActivityModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editActivityModalLabel">Edit Master Activity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editActivityForm">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="activityId" id="editActivityId">
                        <div class="mb-3">
                            <label for="editActivityLevel" class="form-label">Level</label>
                            <select class="form-select" id="editActivityLevel" name="activityLevel" required>
                                <option value="" disabled>Select Level</option>
                                <option value="0">Level 0</option>
                                <option value="1">Level 1</option>
                                <option value="2">Level 2</option>
                                <!-- Add more levels as needed -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editParentActivity" class="form-label">Parent Activity</label>
                            <select class="form-select" id="editParentActivity" name="parentActivity" required>
                                <option value="" disabled>Select Parent Activity</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editActivityDescription" class="form-label">Description</label>
                            <input type="text" class="form-control" id="editActivityDescription" name="activityDescription" required>
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

    <!-- JavaScript Dependencies -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/v/bs5/jszip-3.10.1/dt-2.1.3/b-3.1.1/b-colvis-3.1.1/b-html5-3.1.1/b-print-3.1.1/datatables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

    <!-- Custom Script -->
    <script>
        $(document).ready(function() {
            $('#dataTable').DataTable();

            // Handle dynamic Parent Activity dropdown
            function updateParentActivityDropdown(level, parentDropdown) {
                if (level === "0") {
                    parentDropdown.empty().append('<option value="base">Base Class</option>');
                } else {
                    $.ajax({
                        url: 'get_parent_activities.php',
                        type: 'POST',
                        data: { level: level },
                        dataType: 'json',
                        success: function(response) {
                            parentDropdown.empty();
                            if (response.length > 0) {
                                response.forEach(function(activity) {
                                    parentDropdown.append('<option value="' + activity.id + '">' + activity.description + '</option>');
                                });
                            } else {
                                parentDropdown.append('<option value="">No Parent Activities Available</option>');
                            }
                        }
                    });
                }
            }

            // Handle Add Activity Form submission
            $('#addActivityForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: '',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#addActivityModal').modal('hide');
                            location.reload();
                        } else {
                            alert(response.message);
                        }
                    }
                });
            });

            // Populate Edit Activity Modal
            $('#dataTable').on('click', '.edit-btn', function() {
                var id = $(this).data('id');
                var description = $(this).data('description');
                var level = $(this).data('level');
                var parent = $(this).data('parent');

                $('#editActivityId').val(id);
                $('#editActivityDescription').val(description);
                $('#editActivityLevel').val(level).change();
                updateParentActivityDropdown(level, $('#editParentActivity')).done(function() {
                    $('#editParentActivity').val(parent);
                });
            });

            // Handle Edit Activity Form submission
            $('#editActivityForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: '',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#editActivityModal').modal('hide');
                            location.reload();
                        } else {
                            alert(response.message);
                        }
                    }
                });
            });

            // Handle Delete button
            $('#dataTable').on('click', '.delete-btn', function() {
                var id = $(this).data('id');
                if (confirm('Are you sure you want to delete this activity?')) {
                    $.ajax({
                        url: '',
                        type: 'POST',
                        data: { action: 'delete', activityId: id },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                location.reload();
                            } else {
                                alert(response.message);
                            }
                        }
                    });
                }
            });

            // Handle Delete Selected button
            $('#deleteSelectedBtn').on('click', function() {
                var ids = $('.select-item:checked').map(function() {
                    return $(this).val();
                }).get();

                if (ids.length > 0 && confirm('Are you sure you want to delete the selected activities?')) {
                    $.ajax({
                        url: '',
                        type: 'POST',
                        data: { action: 'delete_multiple', ids: ids },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                location.reload();
                            } else {
                                alert(response.message);
                            }
                        }
                    });
                } else {
                    alert('No activities selected.');
                }
            });

            // Handle Select All checkbox
            $('#selectAll').on('click', function() {
                $('.select-item').prop('checked', this.checked);
            });

            // Update Parent Activity dropdown based on Level
            $('#activityLevel').on('change', function() {
                updateParentActivityDropdown($(this).val(), $('#parentActivity'));
            });

            $('#editActivityLevel').on('change', function() {
                updateParentActivityDropdown($(this).val(), $('#editParentActivity'));
            });
        });
    </script>
</body>
</html>

<?php
// Function to handle AJAX requests
function handleAjaxRequests($mysqli) {
    switch ($_POST['action']) {
        case 'add':
            $description = mysqli_real_escape_string($mysqli, $_POST['activityDescription']);
            $level = mysqli_real_escape_string($mysqli, $_POST['activityLevel']);
            $parent = mysqli_real_escape_string($mysqli, $_POST['parentActivity']);

            // Prepare query for adding activity
            if ($level == '1') {
                // Level 1: Update parent column with the description of the Level 0 activity
                $parentDescription = getActivityDescription($mysqli, $parent);
                $query = "INSERT INTO master_activity (description, level, parent) VALUES ('$description', '$level', '$parentDescription')";
            } elseif ($level == '2') {
                // Level 2: Update parent column with the description of the Level 1 activity
                $parentDescription = getActivityDescription($mysqli, $parent);
                $query = "INSERT INTO master_activity (description, level, parent) VALUES ('$description', '$level', '$parentDescription')";
            } else {
                $query = "INSERT INTO master_activity (description, level, parent) VALUES ('$description', '$level', '$parent')";
            }

            if (mysqli_query($mysqli, $query)) {
                echo json_encode(['status' => 'success', 'message' => 'Activity added successfully.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to add activity. Error: ' . mysqli_error($mysqli)]);
            }
            break;

        case 'edit':
            $id = intval($_POST['activityId']);
            $description = mysqli_real_escape_string($mysqli, $_POST['activityDescription']);
            $level = mysqli_real_escape_string($mysqli, $_POST['activityLevel']);
            $parent = mysqli_real_escape_string($mysqli, $_POST['parentActivity']);

            // Prepare query for updating activity
            if ($level == '1') {
                $parentDescription = getActivityDescription($mysqli, $parent);
                $query = "UPDATE master_activity SET description='$description', level='$level', parent='$parentDescription' WHERE id=$id";
            } elseif ($level == '2') {
                $parentDescription = getActivityDescription($mysqli, $parent);
                $query = "UPDATE master_activity SET description='$description', level='$level', parent='$parentDescription' WHERE id=$id";
            } else {
                $query = "UPDATE master_activity SET description='$description', level='$level', parent='$parent' WHERE id=$id";
            }

            if (mysqli_query($mysqli, $query)) {
                echo json_encode(['status' => 'success', 'message' => 'Activity updated successfully.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update activity. Error: ' . mysqli_error($mysqli)]);
            }
            break;

        case 'delete':
            $id = intval($_POST['activityId']);
            $query = "DELETE FROM master_activity WHERE id=$id";
            if (mysqli_query($mysqli, $query)) {
                echo json_encode(['status' => 'success', 'message' => 'Activity deleted successfully.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete activity. Error: ' . mysqli_error($mysqli)]);
            }
            break;

        case 'delete_multiple':
            $ids = $_POST['ids'];
            $idList = implode(',', array_map('intval', $ids));
            $query = "DELETE FROM master_activity WHERE id IN ($idList)";
            if (mysqli_query($mysqli, $query)) {
                echo json_encode(['status' => 'success', 'message' => 'Selected activities deleted successfully.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete selected activities. Error: ' . mysqli_error($mysqli)]);
            }
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
            break;
    }

    mysqli_close($mysqli);
}

// Function to get the description of an activity by ID
function getActivityDescription($mysqli, $activityId) {
    $query = "SELECT description FROM master_activity WHERE id = " . intval($activityId);
    $result = mysqli_query($mysqli, $query);
    $row = mysqli_fetch_assoc($result);
    return $row ? $row['description'] : '';
}
?>

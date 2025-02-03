<?php
include('db.php');
include('security.php');
include("includes/header.php");
include("includes/navbar.php");

$response = ['status' => 'error', 'message' => 'Failed to process request.'];

// Fetch level 1, level 2, and activity type descriptions
$level1Options = '';
$level2Options = '';
$activityTypeOptions = '';

$mysqli = new mysqli('localhost', 'root', '', 'keywordconfigmaster');

$level1Query = "SELECT description FROM master_activity WHERE level = '1'";
$level2Query = "SELECT description FROM master_activity WHERE level = '2'";
$activityTypeQuery = "SELECT description FROM master_activity WHERE level = '0'"; // Level 0 query

$level1Result = mysqli_query($mysqli, $level1Query);
$level2Result = mysqli_query($mysqli, $level2Query);
$activityTypeResult = mysqli_query($mysqli, $activityTypeQuery);

while ($row = mysqli_fetch_assoc($level1Result)) {
    $description = htmlspecialchars($row['description']);
    $level1Options .= "<option value=\"$description\">$description</option>";
}

while ($row = mysqli_fetch_assoc($level2Result)) {
    $description = htmlspecialchars($row['description']);
    $level2Options .= "<option value=\"$description\">$description</option>";
}

while ($row = mysqli_fetch_assoc($activityTypeResult)) {
    $description = htmlspecialchars($row['description']);
    $activityTypeOptions .= "<option value=\"$description\">$description</option>";
}

mysqli_close($mysqli);

// Handle AJAX requests for add, edit, and delete operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mysqli = new mysqli('localhost', 'root', '', 'keywordconfigmaster');
    
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        if ($action === 'add' || $action === 'edit') {
            // Add/Edit
            $position = $mysqli->real_escape_string($_POST['position']);
            $keywordIn = $mysqli->real_escape_string($_POST['keywordIn']);
            $assignAttribute = $mysqli->real_escape_string($_POST['assignAttribute']);
            $attributeValue = $mysqli->real_escape_string($_POST['attributeValue']);
            $activityTypeOid = $mysqli->real_escape_string($_POST['activityTypeOid']);
            $rank = (int)$_POST['rank']; // Ensure rank is an integer
            
            if ($action === 'add') {
                $sql = "INSERT INTO keyword (position, keyword_in, assign_attribute, attribute_value, activity_type_oid, rank) 
                        VALUES ('$position', '$keywordIn', '$assignAttribute', '$attributeValue', '$activityTypeOid', $rank)";
            } elseif ($action === 'edit') {
                $id = intval($_POST['id']);
                $sql = "UPDATE keyword 
                        SET position='$position', keyword_in='$keywordIn', assign_attribute='$assignAttribute', attribute_value='$attributeValue', activity_type_oid='$activityTypeOid', rank=$rank 
                        WHERE id=$id";
            }
            
            if ($mysqli->query($sql) === TRUE) {
                $response = ['status' => 'success', 'message' => $action === 'add' ? 'Keyword added successfully.' : 'Keyword updated successfully.'];
            } else {
                $response['message'] = 'Error: ' . $mysqli->error;
            }
        } elseif ($action === 'delete') {
            // Single Delete
            $id = intval($_POST['id']);
            $sql = "DELETE FROM keyword WHERE id=$id";
            if ($mysqli->query($sql) === TRUE) {
                $response = ['status' => 'success', 'message' => 'Keyword deleted successfully.'];
            } else {
                $response['message'] = 'Error: ' . $mysqli->error;
            }
        } elseif ($action === 'delete_multiple') {
            // Multiple Delete
            $ids = $_POST['ids'];
            if (!is_array($ids)) {
                $ids = explode(',', $ids);
            }
            $ids = array_map('intval', $ids);
            $idsList = implode(',', $ids);
            $sql = "DELETE FROM keyword WHERE id IN ($idsList)";
            if ($mysqli->query($sql) === TRUE) {
                $response = ['status' => 'success', 'message' => 'Keywords deleted successfully.'];
            } else {
                $response['message'] = 'Error: ' . $mysqli->error;
            }
        }
    }
    $mysqli->close();
    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keyword Configuration</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/v/bs5/jszip-3.10.1/dt-2.1.3/b-3.1.1/b-colvis-3.1.1/b-html5-3.1.1/b-print-3.1.1/datatables.min.css" rel="stylesheet">
    <style>
        .container-fluid {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .card-body {
            flex: 1;
        }
        .btn-icon {
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="card shadow mb-4 flex-fill">
            <div class="card-header py-3 d-flex justify-content-between">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#keywordModal">
                    <i class="fas fa-plus"></i> Add/Edit Keyword
                </button>
                <button class="btn btn-danger" id="deleteSelectedBtn">
                    <i class="fas fa-trash"></i> Delete Selected
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
                                <th>sr.no</th>
                                <th>Position</th>
                                <th>Keyword-in</th>
                                <th>Assign Attribute</th>
                                <th>Attribute Value</th>
                                <th>Activity Type</th>
                                <th>Rank</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $mysqli = new mysqli('localhost', 'root', '', 'keywordconfigmaster');
                            $query = "SELECT * FROM keyword";
                            $result = mysqli_query($mysqli, $query);

                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr data-id='{$row['id']}'>
                                        <td><input type='checkbox' class='selectRow' data-id='{$row['id']}'></td>
                                        <td>{$row['id']}</td>
                                        <td>{$row['position']}</td>
                                        <td>{$row['keyword_in']}</td>
                                        <td>{$row['assign_attribute']}</td>
                                        <td>{$row['attribute_value']}</td>
                                        <td>{$row['activity_type_oid']}</td>
                                        <td>{$row['rank']}</td>
                                        <td>
                                            <button class='btn btn-primary btn-sm edit-btn' data-bs-toggle='modal' data-bs-target='#keywordModal' 
                                                    data-id='{$row['id']}' data-position='{$row['position']}' data-keywordin='{$row['keyword_in']}' 
                                                    data-assignattribute='{$row['assign_attribute']}' data-attributevalue='{$row['attribute_value']}' 
                                                    data-activitytypeoid='{$row['activity_type_oid']}' data-rank='{$row['rank']}'>
                                                <i class='fas fa-edit btn-icon'></i>
                                            </button>
                                            <button class='btn btn-danger btn-sm delete-btn' data-id='{$row['id']}'>
                                                <i class='fas fa-trash btn-icon'></i>
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

    <!-- Keyword Modal -->
    <div class="modal fade" id="keywordModal" tabindex="-1" aria-labelledby="keywordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="keywordModalLabel">Add/Edit Keyword</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="keywordForm">
                        <input type="hidden" name="action" id="action" value="add">
                        <input type="hidden" name="id" id="keywordId">
                        <div class="mb-3">
                            <label for="position" class="form-label">Position</label>
                            <select class="form-select" name="position" id="position" required>
                                <option value="To">To</option>
                                <option value="From">From</option>
                                <option value="Subject">Subject</option>
                                <option value="CC">CC</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="keywordIn" class="form-label">Keyword-in</label>
                            <input type="text" class="form-control" id="keywordIn" name="keywordIn" required>
                        </div>
                        <div class="mb-3">
                            <label for="assignAttribute" class="form-label">Assign Attribute</label>
                            <select class="form-select" name="assignAttribute" id="assignAttribute" required>
                                <?= $level1Options; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="attributeValue" class="form-label">Attribute Value</label>
                            <select class="form-select" name="attributeValue" id="attributeValue" required>
                                <?= $level2Options; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="activityTypeOid" class="form-label">Activity Type</label>
                            <select class="form-select" name="activityTypeOid" id="activityTypeOid" required>
                                <?= $activityTypeOptions; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="rank" class="form-label">Rank</label>
                            <input type="number" class="form-control" id="rank" name="rank" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Include JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/v/bs5/jszip-3.10.1/dt-2.1.3/b-3.1.1/b-colvis-3.1.1/b-html5-3.1.1/b-print-3.1.1/datatables.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#dataTable').DataTable(); // Initialize DataTables

            // Add/Edit keyword
            $('#keywordForm').on('submit', function(e) {
                e.preventDefault();
                var formData = $(this).serialize();
                $.ajax({
                    type: 'POST',
                    url: 'keywordconfigmaster.php',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        alert(response.message);
                        if (response.status === 'success') {
                            location.reload();
                        }
                    }
                });
            });

            // Populate modal for editing keyword
            $('.edit-btn').on('click', function() {
                var id = $(this).data('id');
                var position = $(this).data('position');
                var keywordIn = $(this).data('keywordin');
                var assignAttribute = $(this).data('assignattribute');
                var attributeValue = $(this).data('attributevalue');
                var activityTypeOid = $(this).data('activitytypeoid');
                var rank = $(this).data('rank');
                
                $('#action').val('edit');
                $('#keywordId').val(id);
                $('#position').val(position);
                $('#keywordIn').val(keywordIn);
                $('#assignAttribute').val(assignAttribute);
                $('#attributeValue').val(attributeValue);
                $('#activityTypeOid').val(activityTypeOid);
                $('#rank').val(rank);
            });

            // Handle delete keyword
            $('.delete-btn').on('click', function() {
                if (confirm('Are you sure you want to delete this keyword?')) {
                    var id = $(this).data('id');
                    $.ajax({
                        type: 'POST',
                        url: 'keywordconfigmaster.php',
                        data: { action: 'delete', id: id },
                        dataType: 'json',
                        success: function(response) {
                            alert(response.message);
                            if (response.status === 'success') {
                                location.reload();
                            }
                        }
                    });
                }
            });

            // Handle multiple delete
            $('#deleteSelectedBtn').on('click', function() {
                var ids = [];
                $('.selectRow:checked').each(function() {
                    ids.push($(this).data('id'));
                });
                if (ids.length > 0) {
                    if (confirm('Are you sure you want to delete the selected keywords?')) {
                        $.ajax({
                            type: 'POST',
                            url: 'keywordconfigmaster.php',
                            data: { action: 'delete_multiple', ids: ids },
                            dataType: 'json',
                            success: function(response) {
                                alert(response.message);
                                if (response.status === 'success') {
                                    location.reload();
                                }
                            }
                        });
                    }
                } else {
                    alert('No keywords selected.');
                }
            });

            // Select/Deselect all checkboxes
            $('#selectAll').on('click', function() {
                $('.selectRow').prop('checked', $(this).prop('checked'));
            });

            // Uncheck 'Select All' if one of the checkboxes is unchecked
            $('.selectRow').on('change', function() {
                if (!$(this).prop('checked')) {
                    $('#selectAll').prop('checked', false);
                }
            });

            // Reset form on modal hide
            $('#keywordModal').on('hidden.bs.modal', function() {
                $('#keywordForm')[0].reset();
                $('#action').val('add');
                $('#keywordId').val('');
            });
        });
    </script>
</body>
</html>

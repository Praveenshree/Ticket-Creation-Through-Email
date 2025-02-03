<?php
session_start();
include("includes/header.php");
include("includes/navbar.php");
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Admin Profile</h6>
        </div>
        <div class="card-body">
            <?php
            $connection = mysqli_connect("localhost", "root", "", "admin");
            $query = "SELECT * FROM register";
            $query_run = mysqli_query($connection, $query);

            if (mysqli_num_rows($query_run) > 0) 
            {
                foreach ($query_run as $row) 
                {
                    ?>
                    <div class="row mb-3">
                        <div class="col">
                            <p><?php echo $row['first_name'] . " " . $row['last_name']; ?></p>
                        </div>
                        <div class="col text-right">
                            <button type="button" class="btn btn-primary editBtn" data-id="<?php echo $row['id']; ?>"
                                    data-firstname="<?php echo $row['first_name']; ?>"
                                    data-lastname="<?php echo $row['last_name']; ?>"
                                    data-email="<?php echo $row['email']; ?>"
                                    data-password="<?php echo $row['password']; ?>">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "No records found";
            }
            ?>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="editModal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Admin Profile</h4>
                <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form action="code.php" method="POST">

                    <input type="hidden" name="edit_id" id="edit_id">
                    
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="edit_firstname" id="edit_firstname" class="form-control" placeholder="Enter First Name">
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="edit_lastname" id="edit_lastname" class="form-control" placeholder="Enter Last Name">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="edit_email" id="edit_email" class="form-control" placeholder="Enter Email">
                        <small class="error_email" style="color: red;"></small>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="edit_password" id="edit_password" class="form-control" placeholder="Enter Password">
                    </div>
                    <button type="submit" name="updatebtn" class="btn btn-primary"><i class="fas fa-save"></i> UPDATE</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<?php include("includes/scripts.php"); ?>
<?php include("includes/footer.php"); ?>

<script>
    $(document).ready(function() {
        $('.editBtn').on('click', function() {
            // Get data attributes from the button clicked
            var id = $(this).data('id');
            var firstname = $(this).data('firstname');
            var lastname = $(this).data('lastname');
            var email = $(this).data('email');
            var password = $(this).data('password');

            // Set the values in the modal
            $('#edit_id').val(id);
            $('#edit_firstname').val(firstname);
            $('#edit_lastname').val(lastname);
            $('#edit_email').val(email);
            $('#edit_password').val(password);

            // Show the modal
            $('#editModal').modal('show');
        });
    });
</script>

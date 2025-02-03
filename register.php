<?php
include('security.php');
include("includes/header.php"); 
include("includes/navbar.php");
?>


<!-- Modal for adding admin profile -->
<div class="modal fade" id="addadminprofile" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add Admin Data</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="code.php" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label> FirstName </label>
                        <input type="text" name="FirstName" class="form-control" placeholder="Enter FirstName">
                    </div>
                    <div class="form-group">
                        <label> LastName </label>
                        <input type="text" name="LastName" class="form-control" placeholder="Enter LastName">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control checking_email" placeholder="Enter Email">
                        <small class="error_email" style="color: red;"></small>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Enter Password">
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirmpassword" class="form-control" placeholder="Confirm Password">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class='fas fa-close'></i>Close</button>
                    <button type="submit" name="registerbtn" class="btn btn-primary"><i class='fas fa-save'></i>Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Admin Profile Table -->
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <!-- <h6 class="m-0 font-weight-bold text-primary">Admin Profile -->
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addadminprofile">
            <i class='fas fa-add'></i> Add Admin Profile
            </button>
        </div>
       </h6>
        <div class="card-body">
            <?php
            if(isset($_SESSION['success']) && $_SESSION['success'] !='') {
                echo '<h2 class = "bg-primary text-white">'.$_SESSION['success'].'</h2>';
                unset($_SESSION['success']);
            }
            if(isset($_SESSION['status']) && $_SESSION['status'] !='') {
                echo '<h2 class = "bg-danger">'.$_SESSION['status'].'</h2>';
                unset($_SESSION['status']);
            }
            ?>
            <div class="table-responsive">
                <?php
                $connection = mysqli_connect("localhost", "root", "", "admin");
                $query = "SELECT * FROM register";
                $query_run = mysqli_query($connection, $query);
                ?>
                <table class="table table-boarder" id="datatable0" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>FirstName</th>
                            <th>LastName</th>
                            <th>Email</th>
                            <th>Password</th>
                            <th>EDIT</th>
                            <th>DELETE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if(mysqli_num_rows($query_run) > 0) {
                            while($row = mysqli_fetch_assoc($query_run)) {
                        ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo $row['first_name']; ?></td>
                                    <td><?php echo $row['last_name']; ?></td>
                                    <td><?php echo $row['email']; ?></td>
                                    <td><?php echo $row['password']; ?></td>
                                    <td>
                                        <form action="register_edit.php" method="post">
                                            <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="edit_btn" class="btn btn-success"> <i class='fas fa-edit'></i>EDIT</button>
                                        </form>
                                    </td>
                                    <td>
                                        <form action="code.php" method="post" class="delete_form">
                                            <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                            <button type="button" class="btn btn-danger delete_btn"> <i class='fas fa-trash-alt'></i>DELETE</button>
                                        </form>
                                    </td>
                                </tr>
                        <?php
                            }
                        } else {
                            echo 'No record';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<?php 
include("includes/scripts.php"); ?>


<script>
$(document).ready(function() {
    $('.delete_btn').click(function(e) {
        e.preventDefault();
        var delete_id = $(this).closest('form').find('input[name="delete_id"]').val();
        
        if (confirm("Are you sure you want to delete this record?")) {
            $.ajax({
                type: "POST",
                url: "code.php",
                data: { delete_btn: delete_id }, // Ensure this matches with your PHP script
                success: function(response) {
                    if (response.trim() === 'success') {
                        alert("Record deleted successfully.");
                        window.location.href = 'register.php'; // Redirect after deletion
                    } else {
                        alert("Error deleting record.");
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error: " + status + error); // Log errors to console
                    alert("An unexpected error occurred. Please try again.");
                }
            });
        }
    });
});

</script>

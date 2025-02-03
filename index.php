<?php
include('security.php');
include("includes/header.php");
include("includes/navbar.php");
?>

<style>
    /* Container Background */
    .container-fluid {
        background: linear-gradient(to right, #ece9e6, #ffffff);
        border-radius: 10px;
        padding: 20px;
    }

    /* Card Customizations */
    .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease-in-out;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    .card .card-body {
        padding: 1.5rem;
    }

    .card .text-xs {
        font-size: 0.875rem;
        font-weight: bold;
        color: #5a5c69;
    }

    .card .h5 {
        font-size: 1.5rem;
        font-weight: bold;
    }

    .card .progress {
        height: 8px;
        border-radius: 5px;
        overflow: hidden;
    }

    .progress-bar {
        transition: width 0.6s ease;
    }

    /* Icon Styles */
    .col-auto i {
        color: #6c757d;
        transition: all 0.3s ease;
    }

    .card:hover .col-auto i {
        color: #5a5c69;
    }

    /* Heading and Button Styles */
    .h3.mb-0.text-gray-800 {
        color: #4e73df;
        font-weight: 700;
        letter-spacing: 1px;
    }

    .btn-primary {
        background-color: #4e73df;
        border-color: #4e73df;
    }

    .btn-primary:hover {
        background-color: #2e59d9;
        border-color: #2653d4;
    }

    /* Card-Specific Styles */
    .border-left-primary {
        border-left: 0.25rem solid #4e73df !important;
    }

    .border-left-success {
        border-left: 0.25rem solid #1cc88a !important;
    }

    .border-left-info {
        border-left: 0.25rem solid #36b9cc !important;
    }

    .border-left-warning {
        border-left: 0.25rem solid #f6c23e !important;
    }

    .text-primary {
        color: #4e73df !important;
    }

    .text-success {
        color: #1cc88a !important;
    }

    .text-info {
        color: #36b9cc !important;
    }

    .text-warning {
        color: #f6c23e !important;
    }
</style>



<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading 
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                class="fas fa-download fa-sm text-white-50"></i> Generate Report</a>
    </div>-->

    <!-- Content Row -->
    <div class="row">

        <!-- Total Registered Admin Card Example -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total registered Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"></div>
                            <?php
                            require 'db.php';

                            $query = "SELECT id FROM users ORDER BY id";
                            $query_run = $mysqli->query($query);
                            $row = $query_run->num_rows;
                            echo '<h3>Total Users:' . $row . '</h3>'
                            ?>
                        </div>
                        <div class="col-auto">
    <i class="fas fa-users fa-2x text-gray-300 blinking-icon"></i>
</div>

                    </div>
                </div>
            </div>
        </div>
        
        <!-- Total classified emails Card Example -->
        <div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-primary shadow h-100 py-2">
        <div class="card-body">
            <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                        Total Classified Emails
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        <?php
                        require 'db.php'; // Ensure db.php is correctly included

                        $query = "SELECT id FROM classified_emails ORDER BY id";
                        $query_run = $mysqli->query($query); // Use $mysqli instead of $connection
                        $row = $query_run->num_rows; // Use num_rows to get the count of rows
                        echo '<h3>Total Emails: ' . $row . '</h3>'; // Display the total number of emails
                        ?>
                    </div>
                </div>
                <div class="col-auto">
                    <i class="fas fa-envelope fa-2x text-gray-300 blinking-icon"></i> <!-- Envelope icon for emails -->
                </div>
            </div>
        </div>
    </div>
</div>

        
        <!-- Earnings (Annual) Card Example
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Earnings (Annual)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">$215,000</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->
        <!-- Total Tickets Card Example -->
        <div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-primary shadow h-100 py-2">
        <div class="card-body">
            <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                        Total Tickets
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        <?php
                        require 'db.php'; // Ensure db.php is correctly included

                        $query = "SELECT ticket_oid FROM ticket ORDER BY ticket_oid";
                        $query_run = $mysqli->query($query); // Use $mysqli instead of $connection
                        $row = $query_run->num_rows; // Use num_rows to get the count of rows
                        echo '<h3>Total Tickets: ' . $row . '</h3>'; // Display the total number of emails
                        ?>
                    </div>
                </div>
                <div class="col-auto">
                    <i class="fas fa-ticket-alt fa-2x text-gray-300 blinking-icon"></i> <!-- Ticket icon -->
                </div>
            </div>
        </div>
    </div>
</div>
        <!-- Tasks Card Example 
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Tasks
                            </div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">50%</div>
                                </div>
                                <div class="col">
                                    <div class="progress progress-sm mr-2">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: 50%"
                                            aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>-->
              <!-- Total Tickets Card Example -->
              <div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-primary shadow h-100 py-2">
        <div class="card-body">
            <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                        Total Inbox Emails
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        <?php
                        require 'db.php'; // Ensure db.php is correctly included

                        $query = "SELECT id FROM emails ORDER BY id";
                        $query_run = $mysqli->query($query); // Use $mysqli instead of $connection
                        $row = $query_run->num_rows; // Use num_rows to get the count of rows
                        echo '<h3>Total inbox Emails: ' . $row . '</h3>'; // Display the total number of emails
                        ?>
                    </div>
                </div>
                <div class="col-auto">
                    <i class="fas fa-inbox fa-2x text-gray-300 blinking-icon"></i> <!-- Inbox icon -->
                </div>
            </div>
        </div>
    </div>
</div>

        <!-- Pending Requests Card Example 
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Requests</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">18</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-comments fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>-->

    <!-- Content Row -->


</div>
<!-- /.container-fluid -->

</div>
<!-- End of Main Content -->


<?php include("includes/scripts.php"); ?>
<?php include("includes/footer.php"); ?>

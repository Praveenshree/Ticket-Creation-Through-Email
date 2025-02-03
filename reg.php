<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>
<body>
    <div class="py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h2>Register Page</h2>
                    </div>
                    <form action="registermain.php" method="POST">
                    <div class="card-body">
                        <div class="form-group mt-3">
                        <input type="text" class="form-control form-control-user" id="exampleFirstName" placeholder="First Name">
                        </div>
                        <div class="form-group mt-3">
                        <input type="text" class="form-control form-control-user" id="exampleLastName"
                        placeholder="Last Name">
                        </div>
                        <div class="form-group mt-3">
                        <input type="email" class="form-control form-control-user" id="exampleInputEmail"
                        placeholder="Email Address">
                        </div>
                        <div class="form-group mt-3">
                        <input type="password" class="form-control form-control-user"
                        id="exampleInputPassword" placeholder="Password">
                        </div>
                        <div class="form-group mt-3">
                        <input type="password" class="form-control form-control-user"
                        id="exampleRepeatPassword" placeholder="Repeat Password">
                        </div>
                        <div class="form-group mt-3">
                        <button type="submit" class="form-control" name="save_data">Register here</button>
                        </div>    
                        </form>        
                    </div>
                   
                </div>
            </div>
        </div>
    </div>
</body>
</html>
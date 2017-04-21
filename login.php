<?php
/**
 * The login page for our customers.
 * 
 * @author  Khoa Le
 */

session_start();

require_once('db.php');

$error_message = "";

if (isset($_POST['login'])) {
    $id = $_POST['customer-number'];
    $password = $_POST['password'];

    $res = $conn->query("select * from customer where id="
        . $conn->real_escape_string($id) . " and password='"
        . $conn->real_escape_string($password) . "'");
    
    if ($res->num_rows > 0) {
        $customer = $res->fetch_assoc();

        $_SESSION['customer_id'] = $customer['id'];

        // after successful login, redirect customer to the request page
        header("Location: request.php");
        exit;
    } else {
        $error_message = "Either customer number or password is wrong!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

    <title>ShipOnline System - Login</title>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <!-- jQuery library -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>

    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</head>
<body>
    <div class="jumbotron">
        <div class="container">
            <h3>ShipOnline System Login Page</h3>
        </div>
    </div>

    <div class="container">
        <ul class="breadcrumb">
            <li><a class="breadcrumb-item" href="shiponline.php">Home</a></li>
            <li class="breadcrumb-item active">Login</li>
        </ul>
    </div>
    <div class="container">
        <form class="form-horizontal" method="post" action="">
            <fieldset>
                <legend>Login</legend>
                <div class="form-group">
                    <label for="customer-number" class="col-sm-2 control-label">Customer Number</label>
                    <div class="col-sm-4">
                        <input type="text" id="customer-number" name="customer-number" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password" class="col-sm-2 control-label">Password</label>
                    <div class="col-sm-4">
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-4">
                        <button class="btn btn-primary" type="submit" name="login">Login</button>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-4">
                        <span class="text-danger"><?php echo $error_message; ?></span>
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
</body>
</html>
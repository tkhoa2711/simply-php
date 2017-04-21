<?php
/**
 * Registration page for new customers.
 * 
 * @author  Khoa Le
 */

session_start();

require_once 'db.php';

$confirm_message = "";
$password_confirm_error = $email_error = "";

if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password-confirm'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $customer_number = 0;

    // ensure both passwords match
    if ($password != $password_confirm) {
        $password_confirm_error = "Password does not match!";
    }

    // verify unique email
    $duplicate_emails = $conn->query("select * from customer where email='" . $conn->real_escape_string($email) . "'");
    if ($duplicate_emails->num_rows > 0) {
        $email_error = "This email has been already used!";
    }

    if ($password_confirm_error || $email_error) {
        error_log("Form validation error");
    } else {
        $conn->query("insert into customer (name, password, email, phone) values ('"
            . $conn->real_escape_string($name) . "','"
            . $conn->real_escape_string($password) . "','"
            . $conn->real_escape_string($email) . "','"
            . $conn->real_escape_string($password) . "')");
        
        if ($conn->affected_rows == 0) {
            error_log($conn->error);
            $confirm_message = "Something went wrong";
        } else {
            $result = $conn->query("select id from customer where email='" . $conn->real_escape_string($email) ."'");
            if ($result) {
                $customer_number = $result->fetch_assoc()["id"];
                $confirm_message = "Dear " . $name . ", you are successful registered into ShipOnline, and your customer number is " . $customer_number;
            }
        }
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

    <title>ShipOnline System - Register</title>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

    <!-- jQuery library -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>

    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

    <!-- Font Awesome library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
    <div class="jumbotron">
        <div class="container">
            <h3>ShipOnline System Registration Page</h3>
        </div>
    </div>

    <div class="container">
        <ul class="breadcrumb">
            <li><a class="breadcrumb-item" href="shiponline.php">Home</a></li>
            <li class="breadcrumb-item active">Register</li>
        </ul>
    </div>

    <div class="container">
        <form class="form-horizontal" id="submit" method="post" action="">
            <fieldset>
                <legend>Register</legend>
                <div class="form-group">
                    <label for="name" class="col-sm-2 control-label">Name</label>
                    <div class="col-sm-4">
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password" class="col-sm-2 control-label">Password</label>
                    <div class="col-sm-4">
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="confirm-password" class="col-sm-2 control-label">Confirm Password</label>
                    <div class="col-sm-4">
                        <input type="password" id="password-confirm" name="password-confirm" class="form-control" required>
                        <span class="text-danger"><?php if (isset($password_confirm_error)) { echo $password_confirm_error; } ?></span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="email" class="col-sm-2 control-label">Email Address</label>
                    <div class="col-sm-4">
                        <input type="email" id="email" name="email" class="form-control" required>
                        <span class="text-danger"><?php if (isset($email_error)) { echo $email_error; } ?></span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="phone" class="col-sm-2 control-label">Contact Phone</label>
                    <div class="col-sm-4">
                        <input type="tel" id="phone" name="phone" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-4">
                        <button class="btn btn-primary" type="submit" name="register">Register</button>
                    </div>
                </div>
            </fieldset>
        </form>
    </div>

    <div class="container">
        <p><?php echo $confirm_message; ?></p>
    </div>
</body>
</html>
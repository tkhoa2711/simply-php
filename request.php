<?php
/**
 * This page takes in customer's input for a shipping request.
 * A confirmation email will be sent to the user after a request has been made successfully.
 * 
 * @author  Khoa Le
 */

session_start();

require_once 'db.php';

$DATETIME_FORMAT = 'd/m/Y h:i A';
$DATETIME_FORMAT_MYSQL = 'Y-m-d H:i:s';
$DATE_FORMAT_MYSQL = 'Y-m-d';

/**
 * Send confirmation email to customer for a given request.
 *
 * @param   int $customer_id    the customer ID
 * @param   int $request_id     the request ID
 */
function send_email($customer_id, $request_id) {
    global $conn;

    $CRLF = "\r\n";
    
    // get customer info
    $query = "select * from customer where id = " . $customer_id;
    $result = $conn->query($query);
    if (!$result) { return; }
    $customer = $result->fetch_assoc();

    // get request info
    $query = "select * from request where id = " . $request_id;
    $result = $conn->query($query);
    if (!$result) { return; }
    $request = $result->fetch_assoc();
    
    // format datetime field for further usage
    $request['pickup_time'] = DateTime::createFromFormat($GLOBALS['DATETIME_FORMAT_MYSQL'], $request['pickup_time']);

    $to = $customer['email'];
    $subject = "Shipping request with ShipOnline";
    $msg = "<html><body>"
        . "<p>Dear " . $customer['name'] . ",</p>"
        . "<p>Thank you for using ShipOnline! Your request number is <b>" . $request_id . "</b>. "
        . "The cost is $" . calculate_cost($request['weight']) . ". We will pick up the item at "
        . $request['pickup_time']->format('h:i A') . " on " . $request['pickup_time']->format('d/m/Y') . ".</p>"
        . "</body></html>";
    
    // set required headers for HTML-format email
    // TODO this should be configured properly
    $headers = 'MIME-Version: 1.0' . $CRLF
        . 'Content-Type: text/html; charset=iso-8859-1' . $CRLF;

    // configure bounceback email
    $parameters = "-r test@gmail.com";
    
    $sent = mail($to, $subject, $msg, $headers, $parameters);
    if ($sent) {
        error_log("Sent email to $to");
    } else {
        error_log("Unable to send email");
    }
}

/**
 * Calculate the cost of shipment based on the given weight.
 *
 * @param   double $weight  the weight of item
 * @return  double          the incurred cost
 */
function calculate_cost($weight) {
    if ($weight <= 500) {
        return 2;
    } elseif ($weight <= 3000) {
        return 4;
    } else {
        return 10;
    }
}

// redirect to login page if customer has not logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}

$customer_id = $_SESSION['customer_id'];
$confirm_message = "";
$date_error_message = $time_error_message = $error_message = "";

if (isset($_POST['request'])) {
    $description = $_POST['description'];
    $weight = $_POST['weight'];
    $address = $_POST['address'];
    $suburb = $_POST['suburb'];
    $pickup_date = $_POST['date'];
    $pickup_time = $_POST['time'];
    $receiver_name = $_POST['receiver-name'];
    $receiver_address = $_POST['receiver-address'];
    $receiver_suburb = $_POST['receiver-suburb'];
    $receiver_state = $_POST['receiver-state'];

    // verify pickup date
    $current_datetime = new DateTime();
    $pickup_datetime = DateTime::createFromFormat($DATETIME_FORMAT, $pickup_date . ' ' . $pickup_time);
    $earliest_pickup_datetime = clone $current_datetime;
    $earliest_pickup_datetime->add(new DateInterval('PT24H'));
    if ($pickup_datetime < $earliest_pickup_datetime) {
        $date_error_message = "The pick-up date and time must be at least 24 hours after the current time";
    }

    // verify pickup time
    $lower_time_limit = DateTime::createFromFormat($DATETIME_FORMAT, $pickup_date . ' 7:30 AM');
    $upper_time_limit = DateTime::createFromFormat($DATETIME_FORMAT, $pickup_date . ' 8:30 PM');
    // error_log($pickup_datetime->format($DATETIME_FORMAT));
    // error_log($lower_time_limit->format($DATETIME_FORMAT));
    // error_log($upper_time_limit->format($DATETIME_FORMAT));
    if (($pickup_datetime < $lower_time_limit) ||
        ($pickup_datetime > $upper_time_limit)) {
        $time_error_message = "Pick-up time must be between 7:30 and 20:30";
    }

    if ($date_error_message || $time_error_message) {
        //
    } else {
        $query = "insert into request (customer_id, request_date, description, weight,"
            . " pickup_address, pickup_suburb, pickup_time, receiver_name, receiver_address, receiver_suburb, receiver_state)"
            . " values ("
            . $customer_id . ", '"
            . $current_datetime->format($DATE_FORMAT_MYSQL) . "', '"
            . $description . "', "
            . $weight . ", '"
            . $address . "', '"
            . $suburb . "', '"
            . $pickup_datetime->format($DATETIME_FORMAT_MYSQL) . "', '"
            . $receiver_name . "', '"
            . $receiver_address . "', '"
            . $receiver_suburb . "', '"
            . $receiver_state . "')";
        error_log($query);
        $result = $conn->query($query);
        if ($result) {
            $result = $conn->query("select last_insert_id() as id");
            $request_id = $result->fetch_assoc()['id'];
            
            // TODO check if request_id is 0 or not
            $cost = calculate_cost($weight);
            $confirm_message = "Thank you! Your request number is " . $request_id . ". "
                . "The cost is $" . $cost . ". We will pick up the item at "
                . $pickup_datetime->format('h:i A') . " on " . $pickup_datetime->format('d/m/Y') . ".";
            
            send_email($customer_id, $request_id);
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

    <title>ShipOnline System - Request</title>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <!-- jQuery library -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>

    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

    <!-- Datepicker -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker3.standalone.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>

    <!-- Timepicker -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/less.js/2.5.1/less.min.js"></script>
    <script src="https://jdewit.github.io/bootstrap-timepicker/js/bootstrap-timepicker.js"></script>
    <link rel="stylesheet" href"https://jdewit.github.io/bootstrap-timepicker/css/timepicker.less">
</head>
<body>
    <div class="jumbotron">
        <div class="container">
            <h3>ShipOnline System Request Page</h3>
        </div>
    </div>

    <div class="container">
        <ul class="breadcrumb">
            <li><a class="breadcrumb-item" href="shiponline.php">Home</a></li>
            <li class="breadcrumb-item active">Request</li>
        </ul>
    </div>

    <div class="container">
        <form class="form-horizontal" method="post">
            <fieldset>
                <legend>Item Information</legend>
                <div class="form-group">
                    <label for="description" class="col-sm-2 control-label">Description</label>
                    <div class="col-sm-4">
                        <input type="text" id="description" name="description" class="form-control" required value="<?=(isset($_POST['description'])) ? $_POST['description'] : ''; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="weight" class="col-sm-2 control-label">Weight</label>
                    <div class="col-sm-4">
                        <select class="custom-select form-control" id="weight" name="weight" required>
                            <option value="500">Less than 500g</option>
                            <option value="3000">Up to 3kg</option>
                            <option value="9999">More than 3kg</option>
                        </select>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>Pick-up Information</legend>
                <div class="form-group">
                    <label for="address" class="col-sm-2 control-label">Address</label>
                    <div class="col-sm-4">
                        <input type="text" id="address" name="address" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="suburb" class="col-sm-2 control-label">Suburb</label>
                    <div class="col-sm-4">
                        <input type="text" id="suburb" name="suburb" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="date" class="col-sm-2 control-label">Preferred Date</label>
                    <div class="col-sm-4">
                        <div class="input-group date" data-provide="datepicker" data-date-format="dd/mm/yyyy">
                            <input type="text" id="date" name="date" class="form-control" required>
                            <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                        </div>
                        <span class="text-danger"><?=$date_error_message ?></span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="time" class="col-sm-2 control-label">Preferred Time</label>
                    <div class="col-sm-4">
                        <div class="input-group bootstrap-timepicker timepicker">
                            <input type="text" id="timepicker" name="time" class="form-control" data-provide="timepicker" data-minute-step="1" required>
                            <span class="input-group-addon"><i class="glyphicon glyphicon-time"></i></span>
                        </div>
                        <span class="text-danger"><?=$time_error_message ?></span>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>Delivery Information</legend>
                <div class="form-group">
                    <label for="receiver-name" class="col-sm-2 control-label">Receiver Name</label>
                    <div class="col-sm-4">
                        <input type="text" id="receiver-name" name="receiver-name" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="receiver-address" class="col-sm-2 control-label">Address</label>
                    <div class="col-sm-4">
                        <input type="text" id="receiver-address" name="receiver-address" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="receiver-suburb" class="col-sm-2 control-label">Suburb</label>
                    <div class="col-sm-4">
                        <input type="text" id="receiver-suburb" name="receiver-suburb" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="receiver-state" class="col-sm-2 control-label">State</label>
                    <div class="col-sm-4">
                        <select class="custom-select form-control" id="receiver-state" name="receiver-state" required>
                            <option>ACT</option>
                            <option>NSW</option>
                            <option>NT</option>
                            <option>QLD</option>
                            <option>SA</option>
                            <option>TAS</option>
                            <option>VIC</option>
                            <option>SA</option>
                        </select>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-4">
                        <button class="btn btn-primary" type="submit" name="request">Request</button>
                        <span class="text-danger"><?=$error_message ?></span>
                    </div>
                </div>
            </fieldset>
        </form>
    </div>

    <div class="container">
        <span class="text-info"><?=$confirm_message ?></span>
    </div>
</body>
</html>
<?php
/**
 * This page allows user to query requests basing on different types of date.
 * 
 * @author  Khoa Le
 */

session_start();

require_once 'db.php';

// TODO the following global variable and function are copied from request.php
$DATE_FORMAT_MYSQL = 'Y-m-d';

/**
 * Calculate the cost of shipment based on the given weight.
 *
 * @param   double $weight  the weight of item
 * @return  double          the cost
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

/**
 * Calculate the total revenue of all shipping requests.
 *
 * @param   array $requests a list of requests
 * @return  double          the total revenue from these requests
 */
function calculate_revenue($requests) {
    $total_revenue = 0;
    foreach ($requests as $row) {
        $total_revenue += calculate_cost($row['weight']);
    }
    return $total_revenue;
}

/**
 * Display the result of the search in HTML format
 *
 * @param   double $requests    the list of requests
 * @param   string $date_type   the type of date for this query
 */
function display_result($requests, $date_type) {
    if ($date_type == 'request-date') {
        echo '<hr>';
        echo '<p>Total number of request: ' . count($requests) . '</p>';
        echo '<p>Total revenue: $' . calculate_revenue($requests) . '</p>';
        echo '<table class="table table-bordered table-hover"'
            . '<thread><tr>'
            . '<th>Customer Number</th><th>Request Number</th><th>Item Description</th><th>Weight</th>'
            . '<th>Pick-up Suburb</th><th>Pick-up Date</th><th>Delivery Suburb</th><th>Delivery State</th>'
            . '</tr></thread><tbody>';
        foreach ($requests as $row) {
            echo '<tr><td>'
                . $row['customer_id'] . '</td><td>'
                . $row['id'] . '</td><td>'
                . $row['description'] . '</td><td>'
                . $row['weight'] . '</td><td>'
                . $row['pickup_suburb'] . '</td><td>'
                . $row['pickup_time'] . '</td><td>'
                . $row['receiver_suburb'] . '</td><td>'
                . $row['receiver_state'] . '</td></tr>';
        }
        echo '</tbody></table>';
    } else if ($date_type == 'pickup-date') {
        $total_weight = array_sum(array_map(function ($i) { return $i['weight']; }, $requests));

        echo '<hr>';
        echo '<p>Total number of request: ' . count($requests) . '</p>';
        echo '<p>Total weight: ' . $total_weight . 'g</p>';
        echo '<table class="table table-bordered table-hover"'
            . '<thread><tr>'
            . '<th>Customer Number</th><th>Customer Name</th><th>Contact Phone</th><th>Request Number</th>'
            . '<th>Item Description</th><th>Weight</th><th>Pick-up Address</th>'
            . '<th>Pick-up Suburb</th><th>Pick-up Date</th><th>Delivery Suburb</th><th>Delivery State</th>'
            . '</tr></thread><tbody>';
        foreach ($requests as $row) {
            echo '<tr><td>'
                . $row['customer_id'] . '</td><td>'
                . $row['name'] . '</td><td>'
                . $row['phone'] . '</td><td>'
                . $row['id'] . '</td><td>'
                . $row['description'] . '</td><td>'
                . $row['weight'] . '</td><td>'
                . $row['pickup_address'] . '</td><td>'
                . $row['pickup_suburb'] . '</td><td>'
                . $row['pickup_time'] . '</td><td>'
                . $row['receiver_suburb'] . '</td><td>'
                . $row['receiver_state'] . '</td></tr>';
        }
        echo '</tbody></table>';
    }
}

$error_msg = [];
$total_request_num = $total_revenue = 0;

if (isset($_POST["show"])) {
    // check retrieval date
    $date = isset($_POST["date"]) ? date_create_from_format("d/m/Y", $_POST["date"]) : "";
    if (empty($date)) {
        $error_msg['date'] = "Please select a date!";
        error_log($error_msg['date']);
    }

    // ensure that date type is selected
    $date_type = isset($_POST["date-type"]) ? $_POST["date-type"] : "";
    switch ($date_type) {
        case "request-date":
            $date_column = "request_date";
            break;
        case "pickup-date":
            $date_column = "date(pickup_time)";
            break;
        default:
            $error_msg['date_type'] = "Please specify which type of date you're looking for";
            error_log($error_msg['date_type']);
    }

    // query data
    $has_no_error = (count($error_msg) == 0);
    if ($has_no_error && $date_type == "request-date") {
        $query = "select * from request where " . $date_column . " = '" . $date->format($DATE_FORMAT_MYSQL) . "'";
        $result = $conn->query($query);
        if ($result) {
            $requests = $result->fetch_all(MYSQLI_ASSOC);
        }
    } elseif ($has_no_error && $date_type == "pickup-date") {
        $query = "select c.id as customer_id, c.name, c.phone, r.id, r.description, r.weight"
            . ", r.pickup_address, r.pickup_suburb, r.pickup_time, r.receiver_suburb, r.receiver_state"
            . " from request r left join customer c"
            . " on r.customer_id = c.id"
            . " where date(r.pickup_time) = '" . $date->format($DATE_FORMAT_MYSQL) . "'"
            ;
        $result = $conn->query($query);
        if ($result) {
            $requests = $result->fetch_all(MYSQLI_ASSOC);
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

    <title>ShipOnline System - Admin</title>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <!-- jQuery library -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>

    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

    <!-- Datepicker -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker3.standalone.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
</head>
<body>
    <div class="jumbotron">
        <div class="container">
            <h3>ShipOnline System Administration Page</h3>
        </div>
    </div>

    <div class="container">
        <ul class="breadcrumb">
            <li><a class="breadcrumb-item" href="shiponline.php">Home</a></li>
            <li class="breadcrumb-item active">Admin</li>
        </ul>
    </div>

    <div class="container">
        <form class="form-horizontal" method="post">
            <fieldset>
                <legend>Request Information</legend>
                <div class="form-group">
                    <label for="date" class="col-sm-2 control-label">Date for Retrieval</label>
                    <div class="col-sm-4">
                        <div class="input-group date" data-provide="datepicker" data-date-format="dd/mm/yyyy">
                            <input type="text" id="date" name="date" class="form-control" required>
                            <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                        </div>
                        <span class="text-danger"><?php if(isset($error_msg['date'])) { echo $error_msg['date']; } ?></span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="date-type" class="col-sm-2 control-label">Type of Date</label>
                    <div class="col-sm-4">
                        <div class="input-group">
                            <label class="radio-inline"><input type="radio" name="date-type" value="request-date">Request Date</label>
                            <label class="radio-inline"><input type="radio" name="date-type" value="pickup-date">Pick-up Date</label>
                        </div>
                        <span class="text-danger"><?php if(isset($error_msg['date_type'])) { echo $error_msg['date_type']; } ?></span>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-4">
                        <button class="btn btn-primary" type="submit" name="show">Show</button>
                    </div>
                </div>
            </fieldset>
        </form>
    </div>

    <div class="container">
        <table class="table table-bordered table-hover">
            <thread>
            </thread>
            <tbody>
            </tbody>
        </table>
    </div>

    <div class="container">
        <?php
        if (isset($requests) && !empty($date_type)) {
            display_result($requests, $date_type);
        }
        ?>
    </div>
</body>
</html>
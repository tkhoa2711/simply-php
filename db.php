<?php
/**
 * This module initializes database connection.
 * If errors occur, a message will be shown.
 * 
 * @author  Khoa Le
 */

$config = parse_ini_file('config.ini');

$conn = new mysqli($config['host'], $config['username'], $config['password'], $config['dbname']);

if ($conn->connect_error)
    die("Unable to connect to database");
?>
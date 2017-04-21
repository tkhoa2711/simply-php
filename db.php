<?php
/**
 * This module initializes database connection.
 * If errors occur, a message will be shown.
 * 
 * @author  Khoa Le
 */

$conn = new mysqli("localhost", "root", "", "php_db");

if ($conn->connect_error)
    die("Unable to connect to database");
?>
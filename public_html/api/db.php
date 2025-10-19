<?php
$servername = "localhost";
$username = "examminer";
$password = "YOUR_PASSWPRD";
$dbname = "examminer";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    //die("Connection failed: " . $conn->connect_error);
 die(json_encode(["status" => "error", "message" => "Database connection failed: " . $conn->connect_error]));
}
?>

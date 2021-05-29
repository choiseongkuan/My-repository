<?php
date_default_timezone_set("Asia/Macau");
// THIS PHP FILE IS FOR SERVER CONNECTING

$server_servername = "HOST_SERVER";
$server_username = "HOST_SERVER_USERNAME";
$server_password = "HOST_SERVER_PASSWORD";
$server_dbname = "ffivcco_keangpeng";

$conn = mysqli_connect($server_servername, $server_username, $server_password, $server_dbname);
mysqli_query($conn,'set names utf8mb4');

if (!$conn) {
        // failed
    die("Connection failed: " . mysqli_connect_error());
} else {
        // succeed
}

?>
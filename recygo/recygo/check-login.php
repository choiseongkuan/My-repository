<?php
date_default_timezone_set("Asia/Macau");
// Start session
if ( !isset($_SESSION) ){ session_start(); }
if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']),"apple")) //to prevent cookies for non-apple devices
{
    $cookieLifetime = 365 * 24 * 60 * 60; // A year in seconds
    setcookie("ses_id",session_id(),time()+$cookieLifetime);
}

// Require files
require_once "functions.php";
require_once "conn.php";

// Authentication
// if ( isset($_SESSION["recygo_username"]) ){ header("Location: index.php"); }

// Other actions
header("Content-Type:text/html; charset=utf-8");

// Define
$err_array = [];

// Main code starts here:

if (!exist($_POST['password'])) { $err_array['password'] = 'Please input password.'; }
if (!exist($_POST['username'])) { $err_array['username'] = 'Please input username.'; }

if (count($err_array) > 0) {
    echo json_encode(['error' => true, "content" => $err_array]);
} else {
    $username = check($_POST['username']);
    $password = check($_POST['password']);
    $md5_password = md5($password);

    $sql = "SELECT * FROM `users` WHERE `username` = '$username'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        while ( $row = mysqli_fetch_assoc($result) ){
            if($row['password'] != $md5_password) {
                $err_array['password'] = 'Username or password wrong.';
            }
        }
    } else {
        $err_array['username'] = 'User does not exist.';
    }

    if (count($err_array) > 0) {
        echo json_encode(['error' => true, "content" => $err_array]);
    } else {
        $_SESSION["recygo_username"] = $username;
        echo json_encode(['error' => false]);
    }    
}
?>
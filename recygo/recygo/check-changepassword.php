<?php
date_default_timezone_set("Asia/Macau");
// Start session
if ( !isset($_SESSION) ){ session_start(); }


$cookieLifetime = 365 * 24 * 60 * 60; // A year in seconds
setcookie(session_name(),session_id(),time()+$cookieLifetime);

// Require files
require_once "functions.php";
require_once "conn.php";

// Authentication
// if ( isset($_SESSION["recygo_username"]) ){ header("Location: index.php"); }

// Other actions
header("Content-Type:text/html; charset=utf-8");

$session_username = $_SESSION["recygo_username"];
$user_info_result = mysqli_query($conn, "SELECT * FROM `users` WHERE `username` = '$session_username'");
if ( mysqli_num_rows($user_info_result) > 0 ) { $user_info = mysqli_fetch_assoc($user_info_result); }
$user_id = $user_info["id"];



// Define
$err_array = [];
$filename = "";

// Main code starts here:

    // check password
if (!exist($_POST['current_password']) || !exist($_POST['password']) || !exist($_POST['password_again'])) {
    $err_array['password'] = 'Please enter passwords.';
} else {
    if( $_POST['password'] != $_POST['password_again'] ){
        $err_array['password'] = 'Both passwords don’t match.';
    } else {
        if( md5($user_info["password"]) == $_POST['current_password'] ){
            $password = check($_POST['password']);
            $md5_password = md5($password);
        } else {
            $err_array['password'] = 'Your old password was entered incorrectly.';
        }
    }
}

if (count($err_array) > 0) {
    // require_once 'changepassword.php';
    // echo json_encode("OK, recieved.");
    echo json_encode(['error' => true, "content" => $err_array['password']]);
} else {
    $sql = "UPDATE `users` SET `password` = '$md5_password' WHERE `id` = '$user_id'";
    $result = mysqli_query($conn, $sql);
    echo json_encode(['error' => false]);
    // $_SESSION["recygo_username"] = $username;
    // header("Location: index.php");
    // header("Location: me.php");
}


?>
<?php
date_default_timezone_set("Asia/Macau");
if ( !isset($_SESSION) ){ session_start(); }
$cookieLifetime = 365 * 24 * 60 * 60; // A year in seconds
setcookie(session_name(),session_id(),time()+$cookieLifetime);
if ( !isset($_SESSION["recygo_username"]) ){
    header("Location: login.php");
} else {
    header("Location: dashboard.php");
}

?>
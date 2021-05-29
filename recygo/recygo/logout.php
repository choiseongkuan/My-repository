<?php
date_default_timezone_set("Asia/Macau");
if(!isset($_SESSION)) { session_start(); }

if(isset($_SESSION["recygo_username"])){
    session_destroy();
    session_unset();
}
header("Location: index.php");
?>
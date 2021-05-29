<?php
date_default_timezone_set("Asia/Macau");

// Start session
if ( !isset($_SESSION) ){ session_start(); }


$cookieLifetime = 365 * 24 * 60 * 60; // A year in seconds
setcookie(session_name(),session_id(),time()+$cookieLifetime);

// Require files
require_once "conn.php";
require_once "functions.php";

// Other actions
header("Content-Type:text/html; charset=utf-8");

$now_year  = date("Y", time());
$now_month = date("m", time());


if(exist($_GET["month"])){
    $this_year  = explode("-", $_GET["month"])[0] ;
    $this_month = explode("-", $_GET["month"])[1] ;
} else {
    $this_year  = $now_year;
    $this_month = $now_month;
}

$this_month_day_num = dayNumOf($this_year,$this_month);

$session_username = $_SESSION["recygo_username"];
$user_info_result = mysqli_query($conn, "SELECT * FROM `users` WHERE `username` = '$session_username'");
if ( mysqli_num_rows($user_info_result) > 0 ) { $user_info = mysqli_fetch_assoc($user_info_result); }
$user_id = $user_info["id"];


$sql = "SELECT * FROM `footprint` WHERE `user-id` = '$user_id' AND `time` BETWEEN '".$this_year."-".$this_month."-01' AND '".$this_year."-".$this_month."-".$this_month_day_num."'";
$result = mysqli_query($conn, $sql);
if ( mysqli_num_rows($result) > 0 ) {
    $returnData = ["error" => false, "content" => [] ];
    while($row = mysqli_fetch_assoc($result)){
        
        array_push($returnData["content"], [
            "description" => $row["description"],
            "date" => $row["time"],
            "mark" => ($row["value"]>0) ? "add'>+" : "deduct'>" ,
            "amount" => $row["value"]
        ]);
    }
    $returnData["total"] = round(array_values(mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(`value`) FROM `footprint` WHERE `user-id` = '$user_id' AND `time` BETWEEN '".$this_year."-".$this_month."-01' AND '".$this_year."-".$this_month."-".$this_month_day_num."'")))[0],2) ?? '0';
    // 
} else {
    $returnData = ["error" => true ];
}

echo json_encode($returnData);
?>
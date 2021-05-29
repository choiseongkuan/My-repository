<?php
date_default_timezone_set("Asia/Macau");

// Start session
if ( !isset($_SESSION) ){ session_start(); }


$cookieLifetime = 365 * 24 * 60 * 60; // A year in seconds
setcookie(session_name(),session_id(),time()+$cookieLifetime);

echo session_decode("8371af913bb2a2f2113fb655493ca0f8");

// Require files
require_once "conn.php";
require_once "functions.php";

// Other actions
header("Content-Type:text/html; charset=utf-8");

$this_year  = date("Y", time());
$this_month = date("m", time());
$this_month_day_num = dayNumOf($this_year,$this_month);

$session_username = $_SESSION["recygo_username"];
$user_info_result = mysqli_query($conn, "SELECT * FROM `users` WHERE `username` = '$session_username'");
if ( mysqli_num_rows($user_info_result) > 0 ) { $user_info = mysqli_fetch_assoc($user_info_result); }
$user_id = $user_info["id"];
$user_region_code = $user_info["region"];

$user_on_table = false;
$rank_sql = "SELECT `footprint`.`user-id`, SUM(`footprint`.`value`), `users`.`username`, `users`.`photo_path` FROM `footprint` JOIN `users` ON `footprint`.`user-id` = `users`.`id` AND `users`.`region` = '$user_region_code' AND `footprint`.`time` BETWEEN '".$this_year."-".$this_month."-01' AND '".$this_year."-".$this_month."-".$this_month_day_num."'"." GROUP BY `user-id` ORDER BY SUM(`value`) ASC LIMIT 20";
$rank_result = mysqli_query($conn, $rank_sql);
if( mysqli_num_rows($rank_result) > 0 ){
    $returnData = ["error" => false, "content" => []];
    $rank_counter = 1;
    while( $rank_row = mysqli_fetch_assoc($rank_result) ){
        $focus = "";
        if($rank_row["user-id"] == $user_info["id"] ){
            $user_on_table = true;
            $focus = "focus";
        }
        array_push($returnData["content"], [
            "number" => $rank_counter,
            "username" => $rank_row["username"],
            "photo_path" => $rank_row["photo_path"],
            "focus" => $focus,
            "footprint" => round($rank_row["SUM(`footprint`.`value`)"],2),
        ]);
        $rank_counter++;
    }
    if($user_on_table == true){
        $returnData["user_on_table"] = true;
    } else {
        $returnData["user_on_table"] = false;;
        
        $returnData["this_user"] = [
            "number" => "",
            "username" => $user_info["username"],
            "photo_path" => $user_info["photo_path"],
            "footprint" => round($rank_row["SUM(`footprint`.`value`)"],2),
        ];
    }
} else {
    $returnData = ["error" => true ];
}

echo json_encode($returnData);
?>
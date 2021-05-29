<?php

// Require files
require_once "conn.php";
require_once "functions.php";

// Other actions
header("Content-Type:text/html; charset=utf-8");

// define $user_region_code

$now_year  = date("Y", time());
$now_month = date("m", time());
$now_month_name = date("M", time());
$this_year  = $_GET["y"] ?? $now_year;
$this_month = $_GET["m"] ?? $now_month;
$this_month_day_num = dayNumOf($this_year,$this_month);

$footprint = array_values(mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(`value`) FROM `footprint` WHERE `user-id` = '$user_id' AND `time` BETWEEN '".$this_year."-".$this_month."-01' AND '".$this_year."-".$this_month."-".$this_month_day_num."'")))[0];

$user_region_code = $_GET["region"];
$userId = $_GET["user"] ?? 0;
$user_region_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `regions` WHERE `id` = '$user_region_code'"));

$data = [];

$rank_sql = "SELECT `footprint`.`user-id`, SUM(`footprint`.`value`), `users`.`username`, `users`.`nickname`, `users`.`photo_path` FROM `footprint` JOIN `users` ON `footprint`.`user-id` = `users`.`id` AND `users`.`region` = '$user_region_code' AND `footprint`.`time` BETWEEN '".$this_year."-".$this_month."-01' AND '".$this_year."-".$this_month."-".$this_month_day_num."'"." GROUP BY `user-id` ORDER BY SUM(`value`) ASC LIMIT 25";
$rank_result = mysqli_query($conn, $rank_sql);
if( mysqli_num_rows($rank_result) > 0 ){
    $rank_counter = 1;
    while( $rank_row = mysqli_fetch_assoc($rank_result) ){
        // var_dump($rank_row["user-id"]);
        $isThisUser = ( $userId == $rank_row["user-id"] ) ? true : false ;
        array_push($data, [
            "number" => $rank_counter,
            "url" => $rank_row["photo_path"],
            "username" => $rank_row["username"],
            "nickname" => $rank_row["nickname"],
            "footprint" => "" . round($rank_row["SUM(`footprint`.`value`)"],2),
            "isThisUser" => $isThisUser
        ]);
        $rank_counter++;
    }
}

$data = ["footprint" => $data];

$regionArray = [
    "code" => $user_region_info["code"],
    "name" => $user_region_info["region_name"],
    "city" => $user_region_info["city_name"],
    "flag" => $user_region_info["flagname"],
];

$regionArray = ["region" => $regionArray];

$data = array_merge($data, $regionArray); 

$json = json_encode($data);

echo $json;

?>
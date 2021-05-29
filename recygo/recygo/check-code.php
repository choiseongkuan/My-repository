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

// Define
$err_array = [];
$this_year  = date("Y", time());
$this_month = date("m", time());
$session_username = $_SESSION["recygo_username"];
$user_info_result = mysqli_query($conn, "SELECT * FROM `users` WHERE `username` = '$session_username'");
if ( mysqli_num_rows($user_info_result) > 0 ) { $user_info = mysqli_fetch_assoc($user_info_result); }
$user_id = $user_info["id"];


// Main code starts here:

if (!exist($_POST['code'])) { $err_array['code'] = 'Please enter your code.'; }

if (count($err_array) > 0) {
    echo json_encode(['error' => true, "content" => $err_array]);
} else {
    $code = check($_POST['code']);
    $codeArray = str_split($code);
    
    $codeYear = intval( "20" . $codeArray[0] . $codeArray[1] );
    $codeMonth =   intval( $codeArray[2] . $codeArray[3] );
    $codePaper =   floatval( $codeArray[6] . $codeArray[7] . $codeArray[8] . "." . $codeArray[9] . $codeArray[10] );
    $codePlastic = floatval( $codeArray[12] . $codeArray[13] . $codeArray[14] . "." . $codeArray[15] . $codeArray[16] );
    $codeBattery = floatval( $codeArray[18] . $codeArray[19] . $codeArray[20] . "." . $codeArray[21] . $codeArray[22] );
    
    $weightPaper =   number_format($codePaper, 2);
    $weightPlastic = number_format($codePlastic, 2);
    $weightBattery = number_format($codeBattery, 2);
    
    $carbonValPaper = $weightPaper * 1.55;
    $carbonValPlastic = $weightPlastic * 0.19;
    $carbonValBattery = $weightBattery * 1.14;
    
    
    if( $this_year != $codeYear || $this_month != $codeMonth){
        $err_array["code"] = "There is a problem with your code. Please check it again.";
    }
    
    // $err_array["code"] = number_format($totalCarbonVal, 2);
    if (count($err_array) > 0) {
        echo json_encode(['error' => true, "content" => $err_array]);
    } else {
        if( $carbonValPaper > 0 ) {
            $msgPaper = "Recycled " . $carbonValPaper . " kg of paper";
            $carbonValPaper = 0 - $carbonValPaper;
            $sql = "INSERT INTO `footprint` (`id`, `user-id`, `time`, `value`, `description`) VALUES (null, '$user_id', NOW(), '$carbonValPaper', '$msgPaper')";
            $result = mysqli_query($conn, $sql);
        }
        
        if( $carbonValPlastic > 0 ) {
            $msgPlastic = "Recycled " . $carbonValPlastic . " kg of plastic";
            $carbonValPlastic = 0 - $carbonValPlastic;
            $sql = "INSERT INTO `footprint` (`id`, `user-id`, `time`, `value`, `description`) VALUES (null, '$user_id', NOW(), '$carbonValPlastic', '$msgPlastic')";
            $result = mysqli_query($conn, $sql);
        }
        
        if( $carbonValBattery > 0 ) {
            $msgBattery = "Recycled " . $carbonValBattery . " kg of plastic";
            $carbonValBattery = 0 - $carbonValBattery;
            $sql = "INSERT INTO `footprint` (`id`, `user-id`, `time`, `value`, `description`) VALUES (null, '$user_id', NOW(), '$carbonValBattery', '$msgBattery')";
            $result = mysqli_query($conn, $sql);
        }
        echo json_encode(['error' => false]);
        
    }    
}
?>
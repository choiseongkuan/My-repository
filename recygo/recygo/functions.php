<?php
$cookieLifetime = 365 * 24 * 60 * 60; // A year in seconds
setcookie(session_name(),session_id(),time()+$cookieLifetime);
date_default_timezone_set("Asia/Macau");
function check($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function to2digs($data){
    if ( $data<10 ){ $data = "0".$data; }
    return $data;
}

function exist($data) {
    if (!isset($data) || empty($data)){
        return false;
    } else {
        if( $data == " " ){
            return false;
        } else {
            return true;
        }
    }
}

function dayNumOf($data1, $data2) {
    switch($data2){
        case "1":   return "31";
                    break;
        case "2":   if (($data1 % 4 == 0 && $data1 % 100 != 0) || ($data1 % 400 == 0 && $data1 % 3200 != 0)):
                        return "29";
                    else:
                        return "28";
                    endif;
                    break;
        case "3":   return "31";
                    break;
        case "4":   return "30";
                    break;
        case "5":   return "31";
                    break;
        case "6":   return "30";
                    break;
        case "7":   return "31";
                    break;
        case "8":   return "31";
                    break;
        case "9":   return "30";
                    break;
        case "10":  return "31";
                    break;
        case "11":  return "30";
                    break;
        case "12":  return "31";
                    break;
    }
}

?>
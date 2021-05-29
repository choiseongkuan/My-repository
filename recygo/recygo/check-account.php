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
// if ( !isset($_SESSION["recygo_username"]) ){ header("Location: index.php"); }

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

    // check usermame
if (!exist($_POST['username'])) {
    $err_array['username'] = 'Please input username.';
} else {
    $username = check($_POST['username']);
    $sql = "SELECT * FROM `users` WHERE `username` = '$username'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) == 1) {
        if( $user_id !=  mysqli_fetch_assoc($result)["id"] ){
            $err_array['username'] = 'Username used.';
        }
    }
    
}

    // check email
if (!exist($_POST['email'])) {
    $err_array['email'] = 'Please input an email address.';
} else {
    $email = check($_POST['email']);
    $sql = "SELECT * FROM `users` WHERE `email` = '$email'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) == 1) {
        if( $user_info["id"] !=  mysqli_fetch_assoc($result)["id"] ){
            $err_array['email'] = 'Email adress used.';
        } else {
            if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                $err_array['email'] = 'Please input a correct email address.';
            }
        }
    } else {
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $err_array['email'] = 'Please input a correct email address.';
        }
    }
}

    // check nickname
if (!exist($_POST['nickname'])) {
    $err_array['nickname'] = 'Please input your nickname.';
} else {
    $nickname = check($_POST['nickname']);
    
}



    // check region
if (!exist($_POST['region'])) {
    $err_array['region'] = 'Please select your region.';
} else {
    $region = check($_POST['region']);
}



    // check profile picture
if(isset($_FILES['profile_pic'])){
    if(is_uploaded_file($_FILES['profile_pic']['tmp_name'])){
        $uploaddir = dirname(__FILE__) . '/img/user/';
        $filename =  uniqid() . basename($_FILES['profile_pic']['name']);
        $uploadfile = $uploaddir . $filename;
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $uploadfile)) {
                // File is valid, and was successfully uploaded
            $img_folder_path = __DIR__ . "/img/user/";
            $photo_path = $img_folder_path . $filename;
            list($width, $height, $type) = getimagesize($photo_path);
            if($width == 0 || $height == 0){
                $err_array['photo'] = 'Please upload a image file.';
            } else {
                if ($width > $height) {
                    $new_height = 300;
                    $shortest = $height;
                    $new_width = abs($new_height * $width / $height); 
                } else {
                    $new_width = 300;
                    $shortest = $width;
                    $new_height = abs($new_width * $height / $width); 
                }
                $image_p = imagecreatetruecolor(300, 300);
                if($type == 2){
                    $image = imagecreatefromjpeg($photo_path);
                    imagecopyresampled($image_p, $image, 0, 0, ($width-$shortest)/2, ($height-$shortest)/2, $new_width, $new_height, $width, $height);
                    imagejpeg($image_p, $img_folder_path . $filename);
                } elseif ($type == 3){
                    $image = imagecreatefrompng($photo_path);
                    imagecopyresampled($image_p, $image, 0, 0, ($width-$shortest)/2, ($height-$shortest)/2, $new_width, $new_height, $width, $height);
                    imagepng($image_p, $img_folder_path . $filename);
                } elseif ($type == 1){
                    $image = imagecreatefromgif($photo_path);
                    imagecopyresampled($image_p, $image, 0, 0, ($width-$shortest)/2, ($height-$shortest)/2, $new_width, $new_height, $width, $height);
                    imagegif($image_p, $img_folder_path . $filename);
                } else {
                    // TYPE NOT SUPPORTTED
                    $err_array['photo'] = 'This photo type is not supported.';
                }
            }
            
        } else {
            // echo "Possible file upload attack!\n";
        }
    } else {
        $filename = $user_info["photo_path"];
    }
} else {
    $filename = $user_info["photo_path"];
}


if (count($err_array) > 0) {
    echo json_encode(['error' => true, "content" => $err_array]);
} else {
    $sql = "UPDATE `users` SET `username` = '$username', `photo_path` = '$filename', `email` = '$email', `nickname` = '$nickname', `region` = '$region' WHERE `id` = '$user_id'";
    $result = mysqli_query($conn, $sql);
    $_SESSION["recygo_username"] = $username;
    // header("Location: index.php");
    echo json_encode(['error' => false]);
}
?>
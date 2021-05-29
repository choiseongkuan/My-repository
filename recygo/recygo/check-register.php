<?php
date_default_timezone_set("Asia/Macau");
// Start session
if ( !isset($_SESSION) ){ session_start(); }

// Require files
require_once "functions.php";
require_once "conn.php";

// Authentication
if ( isset($_SESSION["recygo_username"]) ){ header("Location: index.php"); }

// Other actions
header("Content-Type:text/html; charset=utf-8");

// Define
$err_array = [];
$filename = "";

// Main code starts here:

    // check password
if (!exist($_POST['password']) || !exist($_POST['password_again'])) {
    $err_array['password'] = 'Please input password.';
} else {
    if( $_POST['password'] != $_POST['password_again'] ){
        $err_array['password'] = 'The 2 passwords are different.';
    } else {
        $password = check($_POST['password']);
        $md5_password = md5($password);
    }
}

    // check usermame
if (!exist($_POST['username'])) {
    $err_array['username'] = 'Please input username.';
} else {
    $username = check($_POST['username']);
    $sql = "SELECT * FROM `users` WHERE `username` = '$username'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        $err_array['username'] = 'Username used.';
    }
}

    // check email
if (!exist($_POST['email'])) {
    $err_array['email'] = 'Please input an email address.';
} else {
    $email = check($_POST['email']);
    $sql = "SELECT * FROM `users` WHERE `email` = '$email'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        $err_array['email'] = 'Email adress used.';
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
        $err_array['photo'] = 'Please upload photo.';
    }
} else {
    $err_array['photo'] = 'Please upload photo.';
}

if (count($err_array) > 0) {
    echo json_encode(['error' => true, "content" => $err_array]);
} else {
    $token = uniqid() . md5($username);
    // var_dump($username, $password, $nickname, $region, $email, $filename, $token);
    
    $sql = "INSERT INTO `users` (`id`, `username`, `password`, `nickname`, `region`, `email`, `photo_path`, `token`) VALUES (null, '$username', '$md5_password', '$nickname', '$region', '$email', '$filename', '$token')";
    $result = mysqli_query($conn, $sql);
    $_SESSION["recygo_username"] = $username;
    echo json_encode(['error' => false]);
}
?>
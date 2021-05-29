<?php
date_default_timezone_set("Asia/Macau");
// Start session
if ( !isset($_SESSION) ){ session_start(); }

// Require files
require_once "conn.php";
require_once "functions.php";

// Authentication
if ( isset($_SESSION["recygo_username"]) ){ header("Location: index.php"); }

// Other actions
header("Content-Type:text/html; charset=utf-8");
    
?>

<!DOCTYPE html>
<html>
<head>
    <?php require_once "headsettings.php"; ?>
    <title>Register</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div id="register" class="app main-app-register-page">
        <header class="">
            <a class="left" href="login.php" >
                <svg class="back-button" version="1.1" x="0px" y="0px" viewBox="0 0 300 513.2" style="enable-background:new 0 0 300 513.2;" xml:space="preserve">
                    <path d="M289.6,452.9c-2.4-2.4-4.9-4.7-7.3-7.1c-10.5-10.4-21-20.7-31.5-31.1c-11.3-11.2-22.6-22.3-33.8-33.5
                    c-11.1-11-22.2-21.9-33.3-32.9c-11-10.8-21.9-21.7-32.9-32.5c-11-10.8-21.9-21.6-32.9-32.5c-8.4-8.3-16.8-16.7-25.1-25
                    c-0.5-0.5-1-1-1.6-1.8c0.7-0.7,1.1-1.3,1.6-1.8c3.6-3.5,7.2-7,10.8-10.5c11-10.9,22-21.8,33-32.6c11.5-11.3,22.9-22.7,34.4-34
                    c11-10.9,22-21.8,33-32.6c11.7-11.6,23.4-23.1,35.1-34.7c10.6-10.5,21.2-20.9,31.8-31.4c6.3-6.2,12.6-12.5,18.9-18.8
                    c8-8.1,11.3-17.9,9.9-29.1C298.4,20.2,293,11.8,284,5.9c-8-5.3-16.9-7-26.4-5.3c-7.2,1.3-13.4,4.5-18.6,9.7
                    c-3.3,3.3-6.6,6.5-9.9,9.7c-11.2,11.1-22.4,22.1-33.6,33.2c-10.6,10.5-21.3,21-31.9,31.5c-12.6,12.4-25.2,24.9-37.7,37.3
                    c-9.8,9.7-19.7,19.5-29.5,29.2c-12.1,11.9-24.1,23.9-36.2,35.8c-10,9.9-20,19.7-30,29.6c-7.8,7.7-15.6,15.4-23.3,23.2
                    c-5.9,6-7.7,13.3-6.4,21.5c0.9,5.2,3.6,9.3,7.2,13c2.7,2.7,5.5,5.4,8.3,8.1c11,10.9,22,21.7,33,32.6c11.2,11.1,22.5,22.2,33.7,33.3
                    c11.1,11,22.2,21.9,33.3,32.9c11.4,11.2,22.8,22.5,34.1,33.7c10.8,10.7,21.6,21.4,32.5,32.1c11.4,11.3,22.9,22.6,34.3,33.9
                    c6.5,6.4,13,12.9,19.5,19.3c1.6,1.6,3.2,3.1,4.9,4.5c8.3,7.1,18,9.5,28.7,8c7.5-1,14-4.4,19.5-9.7c7-6.8,10.6-15,10.7-24.6
                    C300.2,468.5,296.6,460,289.6,452.9z"/>
               </svg>
               <span class="description"></span>
            </a>
            <h1>Register</h1>
            <a class="right"></a>
        </header>
        <main>
            <form id="register-form" enctype="multipart/form-data" method="POST" action="check-register.php" class="important-form">
                <input type="file" name="profile_pic" accept="image">
                <input type="text" id="username" name="username" value="" placeholder="Username">
                <input type="email" id="email" name="email" value="" placeholder="Email">
                <input type="text" id="nickname" name="nickname" value="" placeholder="Nickname">
                <div class="select-box">
                    <select name="region">
                        <option selected disabled>Country / Region</option>
                        <?php
                            $country_result = mysqli_query($conn, "SELECT * FROM `regions` ORDER BY `code`, `city_name`");
                            while($country_row = mysqli_fetch_assoc($country_result)){
                                echo "<option value='";
                                echo $country_row["id"] ;
                                echo "'>";
                                echo $country_row["code"] ;
                                echo " - ";
                                echo $country_row["city_name"];
                                echo ", ";
                                echo $country_row["region_name"];
                                echo "</option>";
                            }
                        ?>
                    </select>
                    <div class="select__arrow"></div>
                </div>
                <input type="password" id="password" name="password" value="" placeholder="Password">
                <input type="password" id="password_again" name="password_again" value="" placeholder="Password Again">
                <input type="submit" value="register">
            </form>
        </main>
    </div>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <?php
    if (exist($err_array)){
        echo "<script>";
        echo "$( document ).ready( function(){ alert('";
        echo $err_array["username"] ?? $err_array["password"] ?? $err_array["email"] ?? $err_array["photo"] ?? $err_array["repassword"] ?? $err_array["nickname"];
        echo "')})";
        echo "</script>";
    }
    
    ?>

</body>
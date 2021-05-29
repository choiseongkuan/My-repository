<?php
// Start session

if($_COOKIE['ses_id']){
    session_id($_COOKIE['ses_id']);
}

if ( !isset($_SESSION) ){ session_start(); }


$cookieLifetime = 365 * 24 * 60 * 60; // A year in seconds
setcookie(session_name(),session_id(),time()+$cookieLifetime);

// Require files
require_once "conn.php";
require_once "functions.php";

// Authentication
if ( !isset($_SESSION["recygo_username"]) ){ header("Location: index.php"); }

// Other actions
header("Content-Type:text/html; charset=utf-8");
$activePage = 1;

$session_username = $_SESSION["recygo_username"];
$user_info_result = mysqli_query($conn, "SELECT * FROM `users` WHERE `username` = '$session_username'");
if ( mysqli_num_rows($user_info_result) > 0 ) { $user_info = mysqli_fetch_assoc($user_info_result); }
$token = $user_info["token"];
$user_id = $user_info["id"];
$now_year  = date("Y", time());
$now_month = date("m", time());
$now_month_name = date("M", time());
$this_year  = $now_year;
$this_month = $now_month;
$this_month_day_num = dayNumOf($this_year,$this_month);
$footprint = array_values(mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(`value`) FROM `footprint` WHERE `user-id` = '$user_id' AND `time` BETWEEN '".$this_year."-".$this_month."-01' AND '".$this_year."-".$this_month."-".$this_month_day_num."'")))[0];

$user_region_code = mysqli_fetch_assoc( mysqli_query($conn, "SELECT * FROM `users` WHERE `username` = '$session_username'") )["region"];
$user_region_result = mysqli_query($conn, "SELECT * FROM `regions` WHERE `id` = '$user_region_code'");
$user_region_info = mysqli_fetch_assoc($user_region_result);

?>

<!DOCTYPE html>
<html>
<head>
    <?php require_once "headsettings.php"; ?>
    <title>RecyGo</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <style>
        form#account-settings-form.important-form input[type="file"] {
            background-image: url("img/user/<?= $user_info["photo_path"] ?>");
            background-size: 86px 86px;
        }
    </style>
    <link rel="manifest" href="manifest.json">
    <?php require "javascript.php"; ?>
</head>

<body id="main-app">
    <div id="recycle"   class="app active main-app-recycle-page normal no-footer">
        <header class="white"><h1 class="">Recycle</h1></header>
        <main>
            <!--<div id="qrcode-box">-->
            <!--    <div id="qrcode-topbar"></div>-->
            <!--    <div id="qrcode"></div>-->
            <!--    <div id="qrcode-guide">-->
            <!--        <p>Go to a RecyGo machine.</p>-->
            <!--        <p>Scan your Code to the machine.</p>-->
            <!--        <p>Follow the instruction to continue</p>-->
            <!--        <p>Finish recycling.</p>-->
            <!--        <p>Points will go into your account later.</p>-->
            <!--    </div>-->
            <!--</div>-->
            <div id="qr-card">
                <div class="flip">
                    <div class="front">
                        <div id="qrcode"></div>
                    </div>
                    <div class="back"></div>
                </div>
            </div>
            <script>
                $("#qr-card").on("click", ()=>{
                    $("#qr-card").toggleClass("active");
                })
            </script>
        </main>
        <?php
        require "dash-footer.php";
        ?>
        <script>
            $(document).ready(()=>{
                var qrcode = new QRCode("qrcode");
                qrcode.makeCode('<?= $token  ?>');
            })
        </script>
    </div>
    <div id="ranking"   class="app main-app-ranking-page normal">
        <header class="havebar">
            <a class="left"></a>
            <h1>Ranking</h1>
            <a class="right ranking-city">
                <img id="city-flag" src="img/flag/<?= $user_region_info["flagname"] ?>.png" alt="This is the flag of <?= $user_region_info["region_name"] ?>">
            </a>
        </header>
        <main>
            <ul class="ranking-list">
                <?php
                    $user_on_table = false;
                    $rank_sql = "SELECT `footprint`.`user-id`, SUM(`footprint`.`value`), `users`.`username`,`users`.`nickname`, `users`.`photo_path` FROM `footprint` JOIN `users` ON `footprint`.`user-id` = `users`.`id` AND `users`.`region` = '$user_region_code' AND `footprint`.`time` BETWEEN '".$this_year."-".$this_month."-01' AND '".$this_year."-".$this_month."-".$this_month_day_num."'"." GROUP BY `user-id` ORDER BY SUM(`value`) ASC LIMIT 20";
                    $rank_result = mysqli_query($conn, $rank_sql);
                    if( mysqli_num_rows($rank_result) > 0 ){
                        $rank_counter = 1;
                        while( $rank_row = mysqli_fetch_assoc($rank_result) ){
                            echo "<li class='ranking-list-item ";
                            if($rank_row["user-id"] == $user_info["id"] ): echo "focus"; $user_on_table = true; endif;
                            echo "'>";
                                echo "<h3 class='number'>" . $rank_counter . "</h3>";
                                echo "<img alt='The profile picture of " . $rank_row["username"] . "' src='img/user/" . $rank_row["photo_path"] . "'>";
                                echo "<div id='ranking-user-info-block'><h3 class='nickname'>" . $rank_row["nickname"] . "</h3>";
                                echo "<h4 class='username'>@" . $rank_row["username"] . "</h4></div>";
                                echo "<h3 class='footprint'>" . round($rank_row["SUM(`footprint`.`value`)"],2) . "</h3>";
                            echo "</li>";
                            $rank_counter++;
                        }
                    }
                    if($user_on_table == false){
                        echo "<li class='ranking-list-item focus'>";
                            echo "<h3 class='number'>" . "". "</h3>";
                            echo "<img alt='The profile picture of " . $user_info["username"] . "' src='img/user/" . $user_info["photo_path"] . "'>";
                            echo "<div id='ranking-user-info-block'><h3 class='nickname'>" . $user_info["nickname"] . "</h3>";
                            echo "<h4 class='username'>@" . $user_info["username"] . "</h4></div>";
                            echo "<h3 class='footprint'>" . round($rank_row["SUM(`footprint`.`value`)"],2) . "</h3>";
                        echo "</li>";
                    }
                ?>
            </ul> 
        </main>
        <?php
        require "dash-footer.php";
        ?>
    <script>
        $("#city-flag").on("click", ()=>{
            alert("Your city: <?= $user_region_info["city_name"].", ".$user_region_info["region_name"]?> ");
        })
    </script>
</div>
    <div id="redeem"    class="app main-app-account-page normal">
        <header class="havebar">
            <h1>Redeem a code</h1>
        </header>
        <main>
            <form id="redeem-form" method="POST" class="important-form">
                <ul class="user-list">
                    <li id="redeem-instruction-block">
                        <img src="img/icon/redeem_instruction.png" alt="Redeem Instruction"/>
                    </li>
                    <li id="code-box">
                        RCG
                        <span class="hyphen-seperator">-</span>
                        <input style="" type="tel" class="info" id="code" name="code" value="" placeholder="000000000000000000000000">
                    </li>
                </ul>
                <ul class="user-list">
                    <li><a id="code-submit-btn">Redeem</a></li>
                </ul>
            </form>
        </main>
        <?php
        require "dash-footer.php";
        ?>
    </div>
    <div id="me"        class="app main-app-me-page normal">
        <header><h1 class="hide">Me</h1></header>
        <main>
            <div class="user-info">
                <img src="img/user/<?= $user_info["photo_path"] ?>">
                <h4><?= $user_info["nickname"] ?></h4>
                <span>@<?= $user_info["username"] ?></span>
            </div>
            <ul class="user-list">
                <li><a onclick="openSidePage('account')">
                    <div class="icon" id="me-acs-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 56 56">
                            <path d="M 28.0117 28.0234 C 33.5898 28.0234 38.1367 23.0547 38.1367 17.0078 C 38.1367 11.0078 33.6367 6.2969 28.0117 6.2969 C 22.4570 6.2969 17.8867 11.1016 17.8867 17.0547 C 17.9101 23.0782 22.4570 28.0234 28.0117 28.0234 Z M 28.0117 24.4844 C 24.5898 24.4844 21.6601 21.2031 21.6601 17.0547 C 21.6601 12.9766 24.5430 9.8360 28.0117 9.8360 C 31.5274 9.8360 34.3633 12.9297 34.3633 17.0078 C 34.3633 21.1563 31.4805 24.4844 28.0117 24.4844 Z M 13.2930 49.7031 L 42.7305 49.7031 C 46.6211 49.7031 48.4726 48.5313 48.4726 45.9531 C 48.4726 39.8125 40.7383 30.9297 28.0117 30.9297 C 15.2852 30.9297 7.5274 39.8125 7.5274 45.9531 C 7.5274 48.5313 9.3789 49.7031 13.2930 49.7031 Z M 12.1679 46.1641 C 11.5586 46.1641 11.3008 46.0000 11.3008 45.5078 C 11.3008 41.6641 17.2539 34.4687 28.0117 34.4687 C 38.7695 34.4687 44.6992 41.6641 44.6992 45.5078 C 44.6992 46.0000 44.4648 46.1641 43.8555 46.1641 Z"/>
                        </svg>
                    </div>
                    <div class="info">
                        <span class="title">Account Settings</span>
                        <span></span>
                        <svg version="1.1" x="0px" y="0px" viewBox="0 0 25 25" style="enable-background:new 0 0 25 25;" xml:space="preserve"> <polygon points="7.5,0 20,12.5 7.5,25 5,22.4 14.9,12.5 4.9,2.5 "/></svg>
                    </div>
                </a></li>
                <li><a onclick="openSidePage('footprint')">
                    <div class="icon" id="me-fpt-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 56 56">
                        	<g>
                        		<path d="M38.8,20.8c-0.9-1.5-2.3-2.7-3.7-3.6c-3.4-2.2-7-4.2-11.2-4.5c-4.1-0.3-7.6,0.8-10.4,4c-1.2,1.4-2.4,2.8-3.5,4.3
                        			c-0.7,1.3-1.5,2.7-2.2,4c-0.4,1.1-0.8,2.2-1.2,3.4c-0.8,2.8-1.5,5.7-1.4,8.7c0.1,4.9,2,9,5.3,12.6c0.8,0.4,1.5,0.8,2.3,1.3
                        			c1.2,0.2,2.5,0.3,3.5-0.5c2.7-2.2,3.9-5,3.3-8.5c-0.2-1-0.3-1.9-0.3-2.9c0.2-4.6,2.4-7.9,7.1-8.7c2-0.3,4-0.4,6-0.3
                        			c1.5,0.1,3-0.2,4.3-1C39.8,27.1,40.9,24.3,38.8,20.8z M34.9,26.2c-0.6,0.4-1.3,0.5-2.1,0.5c0,0-0.2,0-0.3,0c-0.6,0-1.2,0-1.8,0
                        			c-1.8,0-3.3,0.1-4.9,0.4c-6.1,1-9.7,5.3-9.9,11.8c0,1.4,0.2,2.6,0.4,3.6c0.4,2.1-0.2,3.7-2,5.2c0,0,0,0-0.1,0c-0.1,0-0.2,0-0.4,0
                        			L12.5,47c-2.7-3-4-6.3-4.1-10c-0.1-2.8,0.6-5.4,1.3-7.7c0.2-0.7,0.5-1.4,0.7-2.1c0.1-0.3,0.2-0.5,0.3-0.8l1.9-3.5
                        			c1.1-1.3,2.1-2.7,3.2-3.9c1.8-2,3.9-2.9,6.6-2.9c0.3,0,0.7,0,1.1,0c3.5,0.3,6.7,2.1,9.6,4c1.3,0.8,2.2,1.6,2.7,2.5
                        			C36.9,24.1,36.9,24.9,34.9,26.2z Z M50,11.8c-0.4-0.2-0.8-0.4-1.2-0.7c-1.9-0.3-3.7,0.2-5.2,1.4c-1.5,1.2-2.5,2.8-2.4,4.8c0.1,1.2,1,2.3,2.1,2.6
                        			c2.6,0.7,4.5-0.5,6.2-2.3C51.2,15.8,51.3,13.8,50,11.8z M47.1,15.5c-1.3,1.4-2,1.4-2.3,1.4c0,0,0,0,0,0c-0.1,0-0.2,0-0.3,0
                        			c0-0.4,0.2-1,1.2-1.7c0.6-0.5,1.2-0.7,1.9-0.7C47.6,14.7,47.5,15,47.1,15.5z Z M35.9,13.5c0.8,0.9,1.9,1,3.1,0.6c1.3-0.4,2.2-1.2,2.9-2.3c0.4-1,0.8-2.1,0.1-3.1c-0.8-1-1.8-1.1-3-0.8
                        			c-1.5,0.3-2.6,1.3-3.2,2.6C35.3,11.4,35.1,12.5,35.9,13.5z Z M35.9,13.5c0.8,0.9,1.9,1,3.1,0.6c1.3-0.4,2.2-1.2,2.9-2.3c0.4-1,0.8-2.1,0.1-3.1c-0.8-1-1.8-1.1-3-0.8
                        			c-1.5,0.3-2.6,1.3-3.2,2.6C35.3,11.4,35.1,12.5,35.9,13.5z Z M32.1,11.1c1.5,0,2.4-0.8,3.1-2c0.3-0.6,0.5-1.1,0.5-1.8c-0.1-1.1-0.9-1.9-2-1.8c-2,0.1-3.8,2.3-3.3,4.3
                        			C30.5,10.6,31.1,11.1,32.1,11.1z Z M32.1,11.1c1.5,0,2.4-0.8,3.1-2c0.3-0.6,0.5-1.1,0.5-1.8c-0.1-1.1-0.9-1.9-2-1.8c-2,0.1-3.8,2.3-3.3,4.3
                        			C30.5,10.6,31.1,11.1,32.1,11.1z Z M26.8,9.5c0.7,0.4,1.7,0.1,2.5-0.8c0.1-0.1,0.2-0.3,0.3-0.4c0.7-1.1,0.5-2.6-0.4-3.1c-0.9-0.5-2.1,0-2.8,1.2
                        			c0,0.1-0.1,0.2-0.1,0.3C25.8,7.8,26,9.1,26.8,9.5z Z M26.8,9.5c0.7,0.4,1.7,0.1,2.5-0.8c0.1-0.1,0.2-0.3,0.3-0.4c0.7-1.1,0.5-2.6-0.4-3.1c-0.9-0.5-2.1,0-2.8,1.2
                        			c0,0.1-0.1,0.2-0.1,0.3C25.8,7.8,26,9.1,26.8,9.5z Z M23.6,9.8c0.6-0.2,1.1-0.6,1.4-1.1c0.1-0.2,0.2-0.3,0.2-0.5c0.2-0.6,0.2-1.2-0.3-1.6c-0.4-0.4-1-0.3-1.5-0.1
                        			c-0.9,0.3-1.3,1-1.5,1.9C21.7,9.3,22.6,10.1,23.6,9.8z Z M23.6,9.8c0.6-0.2,1.1-0.6,1.4-1.1c0.1-0.2,0.2-0.3,0.2-0.5c0.2-0.6,0.2-1.2-0.3-1.6c-0.4-0.4-1-0.3-1.5-0.1
                        			c-0.9,0.3-1.3,1-1.5,1.9C21.7,9.3,22.6,10.1,23.6,9.8z"/>
                        	</g>
                        </svg>
                    </div>
                    <div class="info">
                        <span class="title">My Footprint</span>
                        <span>
                            <?= round($footprint, 2) ?> kg
                            </span>
                        <svg version="1.1" x="0px" y="0px" viewBox="0 0 25 25" style="enable-background:new 0 0 25 25;" xml:space="preserve"> <polygon points="7.5,0 20,12.5 7.5,25 5,22.4 14.9,12.5 4.9,2.5 "/></svg>
                    </div>
                </a></li>
                <li><a onclick="openSidePage('tax')">
                    <div class="icon" id="me-cal-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 56 56">
                            <path d="M 14.4180 50.1367 C 15.2149 50.1367 15.6367 49.8086 16.2696 48.9414 L 29.9805 28.5742 L 43.3633 8.8398 C 43.6680 8.3945 43.7852 7.9961 43.7852 7.5273 C 43.7852 6.5898 42.9649 5.8633 42.0274 5.8633 C 41.2071 5.8633 40.7618 6.1211 40.0821 7.0820 L 26.6992 26.9805 L 13.0118 47.1133 C 12.7071 47.5586 12.5430 47.9336 12.5430 48.4492 C 12.5430 49.4570 13.3867 50.1367 14.4180 50.1367 Z M 12.3555 19.1524 L 12.3555 24.2149 C 12.3555 25.3164 13.0587 25.9492 14.0430 25.9492 C 15.0040 25.9492 15.7305 25.3164 15.7305 24.2149 L 15.7305 19.1524 L 20.4883 19.1524 C 21.5899 19.1524 22.1758 18.4492 22.1758 17.5117 C 22.1758 16.5976 21.5899 15.8476 20.4883 15.8476 L 15.7305 15.8476 L 15.7305 10.7851 C 15.7305 9.6836 15.0040 9.0273 14.0430 9.0273 C 13.0587 9.0273 12.3555 9.6836 12.3555 10.7851 L 12.3555 15.8476 L 7.6211 15.8476 C 6.5196 15.8476 5.9102 16.5976 5.9102 17.5117 C 5.9102 18.4492 6.5196 19.1524 7.6211 19.1524 Z M 34.4571 39.8945 C 34.4571 40.8555 35.0899 41.5352 36.1914 41.5352 L 48.3556 41.5352 C 49.4807 41.5352 50.0898 40.8555 50.0898 39.8945 C 50.0898 38.9571 49.4807 38.2305 48.3556 38.2305 L 36.1914 38.2305 C 35.0899 38.2305 34.4571 38.9571 34.4571 39.8945 Z"/>
                        </svg>
                    </div>
                    <div class="info">
                        <span class="title">Tax Calculator</span>
                        <span>HK$<?= round($footprint * 0.18, 2) ?></span>
                        <svg version="1.1" x="0px" y="0px" viewBox="0 0 25 25" style="enable-background:new 0 0 25 25;" xml:space="preserve"> <polygon points="7.5,0 20,12.5 7.5,25 5,22.4 14.9,12.5 4.9,2.5 "/></svg>
                    </div>
                </a></li>
            </ul>
            <ul class="user-list">
                <li><a>
                    <div class="icon" id="me-set-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 56 56">
                            <g transform="translate(4 4)">
                                <path d="M31.3077432,43.5279548 C31.8779091,43.3144137 32.4380042,43.0759167 32.9866081,42.8132437 C32.9594643,37.2979481 37.2937012,32.9592904 42.8132466,32.9866016 C43.0759195,32.4379975 43.3144164,31.8779023 43.5279572,31.3077365 C39.529445,27.3629141 39.5883149,21.0793847 43.7144475,17.2041904 C43.5389218,16.6945104 43.3436515,16.1922996 43.1291474,15.6985291 C37.3020253,15.9020692 32.7011862,11.246815 33.0002714,5.39113813 L33.010482,5.1912368 C32.6079555,4.99787406 32.1991992,4.8175235 31.7847781,4.65050071 L31.6289348,4.82312057 C27.6647921,9.21400566 21.0299908,9.2177092 17.0625045,4.82312057 L16.7256456,4.44999867 C16.3680614,4.5830738 16.0143918,4.72596655 15.6649839,4.87849119 L15.6911683,5.3911466 C15.9929207,11.2990434 11.3040265,15.9931752 5.39114661,15.6911683 L4.8784912,15.6649838 C4.72596656,16.0143918 4.58307381,16.3680614 4.44999868,16.7256456 L4.82312058,17.0625044 C9.21400567,21.0266472 9.2177092,27.6614485 4.82312058,31.6289348 L4.65050066,31.7847781 C4.8175234,32.1991992 4.99787387,32.6079553 5.19123643,33.0104816 L5.39114661,33.000271 C11.2419278,32.7014358 15.9022991,37.2972177 15.69853,43.1291478 C16.1923007,43.3436519 16.6945117,43.5389223 17.2041918,43.7144479 C21.0763433,39.5916597 27.3599325,39.5262017 31.3077432,43.5279548 Z M18.7917819,47.4333262 C16.3366849,46.8900976 14.0228998,45.970889 11.9139205,44.7391942 L12.0377719,42.3143528 C12.2066839,39.007289 9.65396742,36.4637608 6.35564535,36.6322262 L3.67513117,36.7691365 C2.41991367,34.7753833 1.4499386,32.5839645 0.822478633,30.2521549 L2.92622342,28.3528693 C5.38410946,26.1338609 5.3776124,22.5302717 2.92622342,20.3171289 L0.682944646,18.2918704 C1.23975257,16.0094974 2.12293759,13.8550207 3.28024752,11.8806926 L6.35564535,12.0377719 C9.66270916,12.2066839 12.2062374,9.65396742 12.0377719,6.35564535 L11.8806926,3.28024752 C13.8550207,2.12293759 16.0094974,1.23975257 18.2918704,0.682944646 L20.3171289,2.92622342 C22.5361373,5.38410946 26.1397265,5.3776124 28.3528693,2.92622342 L30.2521549,0.822478633 C32.5839645,1.4499386 34.7753833,2.41991367 36.7691418,3.67513451 L36.6322262,6.35564535 C36.4633143,9.66270916 39.0160307,12.2062374 42.3143528,12.0377719 L44.7391942,11.9139205 C45.970889,14.0228998 46.8900976,16.3366849 47.4333262,18.7917819 L45.7437747,20.3171289 C43.2858887,22.5361373 43.2923858,26.1397265 45.7437747,28.3528693 L47.3039836,29.7614443 C46.6873969,32.2638048 45.6782881,34.6116668 44.3457025,36.735985 L42.3143528,36.6322262 C39.007289,36.4633143 36.4637608,39.0160307 36.6322262,42.3143528 L36.7359799,44.3457057 C34.6116668,45.6782881 32.2638048,46.6873969 29.7614443,47.3039836 L28.3528693,45.7437747 C26.1338609,43.2858887 22.5302717,43.2923858 20.3171289,45.7437747 L18.7917819,47.4333262 Z"/>
                                <path d="M24,30 C27.3137085,30 30,27.3137085 30,24 C30,20.6862915 27.3137085,18 24,18 C20.6862915,18 18,20.6862915 18,24 C18,27.3137085 20.6862915,30 24,30 Z M24,33 C19.0294373,33 15,28.9705627 15,24 C15,19.0294373 19.0294373,15 24,15 C28.9705627,15 33,19.0294373 33,24 C33,28.9705627 28.9705627,33 24,33 Z"/>
                            </g>
                        </svg>
                    </div>
                    <div class="info">
                        <span class="title">App Settings</span>
                        <span></span>
                        <svg version="1.1" x="0px" y="0px" viewBox="0 0 25 25" style="enable-background:new 0 0 25 25;" xml:space="preserve"> <polygon points="7.5,0 20,12.5 7.5,25 5,22.4 14.9,12.5 4.9,2.5 "/></svg>
                    </div>
                </a></li>
                <li><a onclick="openSidePage('about')">
                    <div class="icon" id="me-abt-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 56 56">
                            <path d="M 24.3320 13.2461 C 24.3320 15.1211 25.8320 16.6211 27.7070 16.6211 C 29.6055 16.6211 31.0820 15.1211 31.0586 13.2461 C 31.0586 11.3477 29.6055 9.8477 27.7070 9.8477 C 25.8320 9.8477 24.3320 11.3477 24.3320 13.2461 Z M 18.5195 44.2305 C 18.5195 45.3789 19.3399 46.1523 20.5820 46.1523 L 35.4179 46.1523 C 36.6601 46.1523 37.4805 45.3789 37.4805 44.2305 C 37.4805 43.1055 36.6601 42.3320 35.4179 42.3320 L 30.7070 42.3320 L 30.7070 24.4492 C 30.7070 23.1836 29.8867 22.3399 28.6680 22.3399 L 21.2383 22.3399 C 20.0195 22.3399 19.1992 23.0899 19.1992 24.2148 C 19.1992 25.3867 20.0195 26.1602 21.2383 26.1602 L 26.3711 26.1602 L 26.3711 42.3320 L 20.5820 42.3320 C 19.3399 42.3320 18.5195 43.1055 18.5195 44.2305 Z"/>
                        </svg>
                    </div>
                    <div class="info">
                        <span class="title">About</span>
                        <span>Version 1.2.1</span>
                        <svg version="1.1" x="0px" y="0px" viewBox="0 0 25 25" style="enable-background:new 0 0 25 25;" xml:space="preserve"> <polygon points="7.5,0 20,12.5 7.5,25 5,22.4 14.9,12.5 4.9,2.5 "/></svg>
                    </div>
                </a></li>
            </ul>
            <ul class="user-list">
                <li>
                    <a class="logout" href="logout.php" onclick="startLoading()">Log out</a>
                </li>
            </ul>
        </main>
        <?php
        require "dash-footer.php";
        ?>
    </div>
    <div id="account"   class="app side main-app-account-page normal no-footer">
        <header class="">
            <a class="left" onclick="closeSidePage('account'); clearPage('account')" >
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
            <h1>Account Settings</h1>
            <div class="right">
                <a id="account-submit-btn">Save</a>
            </div>
        </header>
        <main>
            <form id="account-settings-form" enctype="multipart/form-data" method="POST" class="important-form">
                <div class="user-info">
                    <input id="profile-pic" type="file" name="profile_pic" accept="image">
                    <span>Tap to change profile photo</span>
                </div>
                
                <ul class="user-list">
                    <li>
                        <div class="icon"><span>Username</span></div>
                        <input type="text" class="info" id="username" name="username" value="<?= $user_info["username"] ?>" placeholder="Username">
                    </li>
                    <li>
                        <div class="icon"><span>Email</span></div>
                        <input type="email" class="info"  id="email" name="email" value="<?= $user_info["email"] ?>" placeholder="Email">
                    </li>
                    <li>
                        <div class="icon"><span>Nickname</span></div>
                        <input type="text" class="info" id="nickname" name="nickname" value="<?= $user_info["nickname"] ?>" placeholder="Nickname">
                    </li>
                    <li>
                        <div class="icon"><span>Region</span></div>
                        <div class="select-box">
                            <select id="region" name="region" class="info">
                                <option disabled>Country / Region</option>
                                <?php
                                    $country_result = mysqli_query($conn, "SELECT * FROM `regions` ORDER BY `code`, `city_name`");
                                    while($country_row = mysqli_fetch_assoc($country_result)){
                                        echo "<option value='";
                                        echo $country_row["id"] ;
                                        echo "'";
                                        if ($user_region_code == $country_row["id"]) {
                                            echo " selected";
                                        }
                                        echo ">";
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
                    </li>
                </ul>
                <ul class="user-list">
                    <li><a onclick="openSidePage('password')" id="change-password-link">Change Password</a></li>
                </ul>
            </form>
        </main>
    </div>
    <div id="password"  class="app side main-app-password-page normal no-footer">
        <header class="">
            <a class="left" onclick="closeSidePage('password'); clearPage('password')" >
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
            <h1>Change Password</h1>
            <div class="right">
                <a id="password-submit-btn">Save</a>
            </div>
        </header>
        <main>
            <form id="password-settings-form" method="POST" class="important-form">
                <input type="password" id="new_password" name="password" value="" placeholder="New password">
                <input type="password" id="new_password_again" name="password_again" value="" placeholder="New password again">
                <input type="password" id="current_password" name="current_password" value="" placeholder="Current password">
            </form>
        </main>
    </div>
    <div id="about"     class="app side main-app-about-page normal no-footer">
        <header class="havebar">
            <a class="left" onclick="closeSidePage('about')" >
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
               <span class="description">Me</span>
            </a>
            <h1>About</h1>
            <a class="right"></a>
        </header>
        <main>
            <img id="login-main-icon" src="img/icon/main-icon.png" alt="Main icon"/>
            <h2>Carbon RecyGo</h2>
            <h4>Version 1.2 beta</h4>
            <div class="bottom-options">
                <span>Copyright © 2019 Goldfish Inc. All rights reserved</span>
            </div>
        </main>
        
    </div>
    <div id="tax"       class="app side main-app-tax-page normal">
        <header class="havebar">
            <a class="left" onclick="closeSidePage('tax')">
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
               <span class="description">Me</span>
            </a>
            <h1>Tax</h1>
            <div class="right"></div>
        </header>
        <main>
            <div class="user-info">
                <h3><?= $now_month_name . " " . $now_year?></h3>
                <h2 class="amount">
                    HK$<?= round($footprint * 0.18,2) ?? '0.00' ?>
                </h2>
                <p>HK$0.18 for 1 kg carbon dioxide</p>
                <p class="usage">You‘ve produced <?= round($footprint,2) ?? "0" ?>kg CO<sub>2</sub> this month</p>
            </div>
            
                <?php
                    for ( ; $this_year >= 2019 ; $this_year--){
                        echo "<ul class='user-list'>";
                        echo "<li class='ul-title'>".$this_year."</li>";
                        for( ; $this_month >= 1 ; $this_month-- ){
                            if($this_year == 2019 && $this_month == 8){ break 2; }
                            $this_month_day_num = dayNumOf($this_year,$this_month);
                            $footprint = array_values(mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(`value`) FROM `footprint` WHERE `user-id` = '$user_id' AND `time` BETWEEN '".$this_year."-".$this_month."-01' AND '".$this_year."-".$this_month."-".$this_month_day_num."'")))[0] ?? 0;
                            echo "<li><div class='info'><div class='user-list-info-left'><span class='title'>";
                            echo date("F", strtotime($this_year."-".$this_month));
                            echo "</span><span class='date'>";
                            echo round($footprint,2);
                            echo "kg Carbon Dioxide</span></div><span class='amount'>HK$";
                            echo round($footprint * 0.18, 2);
                            echo "</span></div></li>";
                        }
                        $this_month = 12;
                        echo "</ul>";
                    }
                ?>
        </main>
    </div>
    <div id="footprint" class="app side main-app-footprint-page normal">
        <?php
        $this_year  = $now_year;
        $this_month = $now_month;
        ?>
        <header class="havebar">
            <a class="left" onclick="closeSidePage('footprint')" >
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
               <span class="description">Me</span>
            </a>
            <h1>My Footprint</h1>
            <div class="right"></div>
        </header>
        <main>
            <ul class="user-list">
                <li class="month-selector">
                    <form method="GET" action="footprint.php">
                        <input id="month" type="month" name="month" value="<?= $this_year."-".$this_month ?>" max="<?= $now_year ."-".$now_month ?>" min="2019-09">
                        <a id="footprint-submit-btn">Search</a>
                    </form>
                </li>
                <li>
                    <div class="info">
                        <div class="user-list-info-left">
                            <span class="title">Footprints of this month</span>
                            <span class='date'>Counted in kilograms</span>
                        </div>
                        <span id="footprint-total-value" class="amount"><?= round(array_values(mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(`value`) FROM `footprint` WHERE `user-id` = '$user_id' AND `time` BETWEEN '".$this_year."-".$this_month."-01' AND '".$this_year."-".$this_month."-".$this_month_day_num."'")))[0],2) ?? '0' ?></span>
                    </div>
                </li>
            </ul>
            <ul class="user-list" id="footprint-list">
                <?php
                $sql = "SELECT * FROM `footprint` WHERE `user-id` = '$user_id' AND `time` BETWEEN '".$this_year."-".$this_month."-01' AND '".$this_year."-".$this_month."-".$this_month_day_num."'";
                $result = mysqli_query($conn, $sql);
                if ( mysqli_num_rows($result) > 0 ) {
                    while($row = mysqli_fetch_assoc($result)){
                        echo "<li><div class='info'><div class='user-list-info-left'><span class='title'>";
                        echo $row["description"];
                        echo "</span><span class='date'>";
                        echo $row["time"];
                        echo "</span></div><span class='amount ";
                        if ( $row["value"] > 0 ) : echo "add'>+"; else: echo "deduct'>"; endif;
                        echo $row["value"];
                        echo "</span></div></li>";
                    }
                } else {
                    echo "<li><div class='info'>No result.</div></li>";
                }
                ?>
            </ul>
        </main>
    </div>
    <div id="floting-snipper">
        <svg class="spinner" width="50px" height="50px" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg">
            <circle class="path" fill="none" stroke-width="6" stroke-linecap="round" cx="33" cy="33" r="30"></circle>
        </svg>
    </div>
    <script>
        const setDarkMode = () => {
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                $("body").addClass("black");
                $("head").append(`<meta name="apple-mobile-web-app-status-bar-style" content="black">`);
            } else {
                $("body").removeClass("black");
                $("head").append(`<meta name="apple-mobile-web-app-status-bar-style" content="default">`);
            }
        }
        setDarkMode();
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
            setDarkMode();
        });
    </script>
</body>
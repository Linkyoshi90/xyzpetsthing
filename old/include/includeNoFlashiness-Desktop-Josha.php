<?php
$currentSite = $_SERVER['PHP_SELF'];
$picture = $picturePath . $count . $pictureExt;
include "include/database_connect.php";
session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <script type="text/javascript" src="js/seasons.js"></script>
        <link href="css/bootstrap.css" type="text/css" rel="stylesheet"/>
        <link href="css/bootstrap.min.css" type="text/css" rel="stylesheet"/>
        <link href="css/layout.css" type="text/css" rel="stylesheet"/>
        <link href="css/layout_white.css" type="text/css" rel="stylesheet"/>
        <link href="css/fonts.css" type="text/css" rel="stylesheet"/>
        <link rel="shortcut icon" href="images/favicon.ico">
        <script type="text/javascript" src="http://code.jquery.com/jquery-1.6.4.min.js"></script>
        <script type="text/javascript" src="js/script.js"></script>
        <script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
        <script type="text/javascript" src="js/jquery-ui-1.8.21.custom.min.js"></script>
        <script type="text/javascript" src="js/main.js"></script>
    </head>
    <body>
        <div id="loginbar">
            <a href='gameMenu.php'>
                <img src="images/Home_white.png" height="10%" width="7%" alt="alternative">
            </a>
            <!--
            <div id="username">
                <?php
                include "include/login.php";
                ?>
            </div>
            -->
        </div>
        <div id="bottomBar">
            &copy; Kravel GmbH
        </div>
    </body>
</html>
<?php
include "include/loginHandling.php"
?>
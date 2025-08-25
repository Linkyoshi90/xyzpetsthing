<?php
$currentSite = $_SERVER['PHP_SELF'];

$random = rand(1, 7);

$usernameThis;

if ($random) {

    if ($random === 1) {
        $picturePath = "images/bgs/blue/AnimeSpace";
        $theme = "blue";
    } elseif ($random === 2) {
        $picturePath = "images/bgs/blue/AnimeNight";
        $theme = "blue";
    } elseif ($random === 3) {
        $picturePath = "images/bgs/green/bg";
        $theme = "green";
    } elseif ($random === 4) {
        $picturePath = "images/bgs/sunset/animeSunset";
        $theme = "sunset";
    } elseif ($random === 5) {
        $picturePath = "images/bgs/green/gif";
        $theme = "green";
    } elseif ($random === 6) {
        $picturePath = "images/bgs/sunset/gif";
        $theme = "sunset";
    } elseif ($random === 7) {
        $picturePath = "images/bgs/blue/gif";
        $theme = "blue";
    }
}
if ($picturePath) {

    if ($picturePath === "images/bgs/bg") {
        $count = rand(1, 4);
        $pictureExt = ".jpg";
    } elseif ($picturePath === "images/bgs/blue/night") {
        $count = rand(1, 2);
        $pictureExt = ".jpg";
    } elseif ($picturePath === "images/bgs/blue/AnimeNight") {
        $count = rand(1, 15);
        $pictureExt = ".jpg";
    } elseif ($picturePath === "images/bgs/blue/beachAnimeNight") {
        $count = rand(1, 7);
        $pictureExt = ".jpg";
    } elseif ($picturePath === "images/bgs/elderScrolls") {
        $count = rand(1, 3);
        $pictureExt = ".jpg";
    } elseif ($picturePath === "images/bgs/blue/AnimeSpace") {
        $count = rand(1, 24);
        $pictureExt = ".jpg";
    } elseif ($picturePath === "images/bgs/blue/space") {
        $count = rand(1, 4);
        $pictureExt = ".jpg";
    } elseif ($picturePath === "images/bgs/minecraft") {
        $count = rand(1, 6);
        $pictureExt = ".jpg";
    } elseif ($picturePath === "images/bgs/green/bg") {
        $count = rand(1, 31);
        $pictureExt = ".jpg";
    } elseif ($picturePath === "images/bgs/green/gif") {
        $count = rand(1, 8);
        $pictureExt = ".gif";
    } elseif ($picturePath === "images/bgs/sunset/animeSunset") {
        $count = rand(1, 22);
        $pictureExt = ".jpg";
    } elseif ($picturePath === "images/bgs/sunset/gif") {
        $count = rand(1, 8);
        $pictureExt = ".gif";
    } elseif ($picturePath === "images/bgs/blue/gif") {
        $count = rand(1, 20);
        $pictureExt = ".gif";
    } else {
        $count = rand(1, 2);
    }
}

$picture = $picturePath . $count . $pictureExt;
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <script type="text/javascript" src="js/seasons.js"></script>
        <link href="css/layout_modern.css" type="text/css" rel="stylesheet"/>
        <link rel="shortcut icon" href="images/favicon.ico">
        <link href="css/buttonHover.css" type="text/css" rel="stylesheet"/>
        <script type="text/javascript" src="http://code.jquery.com/jquery-1.6.4.min.js"></script>
        <script type="text/javascript" src="js/script.js"></script>
        <script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
        <script type="text/javascript" src="js/jquery-ui-1.8.21.custom.min.js"></script>
        <script type="text/javascript" src="js/main.js"></script>
    </head>
    <body background="<?php echo $picture; ?>">
        <div id="loginbar">
            <a href="index.php"><button class="pulse">Home</button></a>
            <!--
            <a href="admins.php"><button class="pulse">Admins</button></a>
            <a href="spruche.php"><button class="pulse">Sprüche</button></a>
            -->
            <!--
            <div id="username">
            <?php
            //@session_start();
            if (@$_SESSION['username'] != NULL) {
                echo $_SESSION['username'] . " ";
                echo '<form action="" method="POST">';
                echo '<input type="submit" name="logout" id "logout" value="Logout">';
                echo '</form>
                  </div>
                  <div id="thyme">
                  <div class="clock">
                  <div id="Date"></div>
                  <ul>
                  <li id="hours"></li>
                  <li id="point">:</li>
                  <li id="min"></li>
                  <li id="point">:</li>
                  <li id="sec"></li>
                  </ul>
                  </div>
                  </div>';
            } else {
                echo '<form action="" method="POST">';
                echo ' Username ';
                echo '<input type="text" name="username" required>';
                echo ' Password ';
                echo '<input type="password" name="password" required>';
                echo '<input type="submit" name="login" id "login" value="Login">';
                echo '</form>';
                //echo '<a href=login.php>Login</a>';
                echo ' ';
                echo '<a href=register.php>Register</a></div>';
            }
            ?>
            </div>
            -->
        </div>
    </body>
</html>
<?php
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($database, $_POST['username']);
    $pass = mysqli_real_escape_string($database, $_POST['password']);
    $nameThisUser = $_POST['username'];
    $nameThisPass = $_POST['password'];
    $sel_user = "select * from `user` where `Username`='" . $username . "' AND `Passwort`='" . $pass . "';";
    $run_user = mysqli_query($database, $sel_user);
    $check_user = mysqli_num_rows($run_user);

    if ($check_user > 0) {
        echo "<br>";
        $sql = "SELECT * FROM user WHERE Username = '$username'";
        if (!$result = $database->query($sql)) {
            die('Fehler [' . $database->error . ']' . ' ' . $username . $pass);
        } else {
            $_SESSION['username'] = $username;
            $usernameThis = $username;
            //echo "<script>alert('" . $username . " " . $_SESSION['username'] . " " . $usernameThis . "')</script>";
        }
    } else {
        echo "<script>alert('This is not correct. "
        . "Username = " . $username . " "
        . "Session-Username = " . $_SESSION['username'] . " "
        . "UsernameThis = " . $usernameThis . " "
        . "U = " . $nameThisUser . " "
        . "P = " . $nameThisPass . " "
        . "Haufen = " . $database->error . "')</script>";
    }
}
?>
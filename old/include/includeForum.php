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

/*
  switch ($random) {
  case 1:
  $picturePath = "images/bgs/minecraft";
  case 2:
  $picturePath = "images/bgs/space";
  case 3:
  $picturePath = "images/bgs/AnimeSpace";
  case 4:
  $picturePath = "images/bgs/elderScrolls";
  case 5:
  $picturePath = "images/bgs/beachAnimeNight";
  case 6:
  $picturePath = "images/bgs/AnimeNight";
  case 7:
  $picturePath = "images/bgs/night";
  case 8:
  $picturePath = "images/bgs/green/bg";
  }
 * 
 */

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
        <link href="css/layout.css" type="text/css" rel="stylesheet"/>
        <link href="css/layout_<?php echo $theme; ?>.css" type="text/css" rel="stylesheet"/>
        <link rel="shortcut icon" href="images/favicon.ico">
        <script type="text/javascript" src="http://code.jquery.com/jquery-1.6.4.min.js"></script>
        <script type="text/javascript" src="js/script.js"></script>
        <script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
        <script type="text/javascript" src="js/jquery-ui-1.8.21.custom.min.js"></script>
        <script type="text/javascript" src="js/main.js"></script>
    </head>
    <body background="<?php echo $picture; ?>">
        <div id="loginbar">
            <a href=index.php><img src="images/Home_<?php echo $theme; ?>.png" height="10%" width="7%" alt="alternative"></a>
            <a href=admins.php><img src="images/admins_<?php echo $theme; ?>.png" height="10%" width="7%" alt="alternative"></a>
            <a href=spruche.php><img src="images/quotes_<?php echo $theme; ?>.png" height="10%" width="7%" alt="alternative"></a>
            <a href=secret.php 
               onclick="javascript:window.open(this.href);
                       return false;">
                <img src="images/secret_<?php echo $theme; ?>.png" height="10%" width="7%" alt="alternative">
            </a>
            <div id="username">
<?php
/*
@session_start();
if (@$_SESSION['username'] != NULL) {
    echo @$_SESSION['username'] . " ";
    echo '<form action="" method="POST">';
    echo '<input type="submit" name="logout" id "logout" value="Logout">';
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
    echo '<a href=register.php>Register</a>';
}
 * 
 */
?>
                    </div>
                </div>
            </div>
        </div>
    </body>
<?php
/*
include("database_connect.php");

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($database, $_POST['username']);
    $pass = mysqli_real_escape_string($database, $_POST['password']);
    $passwort = md5($pass);
    $sel_user = "select * from User where Username='$username' AND Passwort='$passwort'";
    $run_user = mysqli_query($database, $sel_user);
    $check_user = mysqli_num_rows($run_user);

    if ($check_user > 0) {
        echo "Erfolgreich";
        echo "<br>";
        $sql = <<<SQL
            SELECT *
            FROM user 
            WHERE Username = '$username'
SQL;
        if (!$result = $database->query($sql)) {
            die('Fehler [' . $database->error . ']');
        } else {
            echo "Username= ";
            echo $username;
            echo "<br>";
            echo "UserID= ";

            while ($row = $result->fetch_assoc()) {
                //echo $row['UserID'];
                $_SESSION['userid'] = $row['UserID'];
                $_SESSION['Admin'] = $row['Administrator'];
            }
            $_SESSION['username'] = $username;
            $userID = $_SESSION['userid'];
            echo '<script type="text/javascript"> document.location = "' . $currentSite . '";</script>';
        }
    } else {
        echo "<script>alert('Name or password is not correct, try again!')</script>";
    }
}
if (isset($_POST['logout'])) {
    $_SESSION['username'] = null;
    $_SESSION['userid'] = null;
    echo '<script type="text/javascript"> document.location = "' . $currentSite . '";</script>';
}
 * 
 */
?>
<?php
if (isset($_POST['login'])) {
    //session_start();
    $username = mysqli_real_escape_string($database, $_POST['username']);
    $pass = mysqli_real_escape_string($database, md5($_POST["password"]));
    $sql = <<<SQL
        SELECT *
        FROM `flashUsers` 
        WHERE `UserName` = "'$username'"
SQL;
    if (!$result = $database->query($sql)) {
        die('Fehler [' . $database->error . ']' . ' ' . $username . $pass);
    } else {
        $_SESSION['username'] = $username;
        $usernameThis = $username;
        //echo "<script>alert('" . $username . " " . $_SESSION['username'] . " " . $usernameThis . "')</script>";
    }
}
if (isset($_POST['logout'])) {
    $_SESSION['username'] = null;
    session_destroy();
    header("Location: flashMenu.php");
}
?>
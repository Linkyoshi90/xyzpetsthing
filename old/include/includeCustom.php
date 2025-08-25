<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <!-- Here is supposed to be a CSS, like in the original include, 
        but each user should define his own CSS! -->
        <link rel="shortcut icon" href="images/favicon.ico">
        <script type="text/javascript" src="http://code.jquery.com/jquery-1.6.4.min.js"></script>
        <script type="text/javascript" src="js/script.js"></script>
    </head>
    <body>
        <div id="loginbar">
            <a href=index.php><img src="images/joshaHome_green.png" height="10%" width="7%" alt="alternative"></a>
            <a href=admins.php><img src="images/admins_green.png" height="10%" width="7%" alt="alternative"></a>
            <a href=spruche.php><img src="images/quotes_green.png" height="10%" width="7%" alt="alternative"></a>
            <div id="username">
                <?php
                ?>
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
            </div>
        </div>
    </body>
     <?php
        include("database_connect.php");

        if(isset($_POST['login'])){
            $username = mysqli_real_escape_string($database,$_POST['username']);
            $pass = mysqli_real_escape_string($database,$_POST['password']);
            $sel_user = "select * from user where Username='$username' AND Passwort='$pass'";
            $run_user = mysqli_query($database, $sel_user);
            $check_user = mysqli_num_rows($run_user);

            if($check_user>0){
                echo "Erfolgreich";
                echo "<br>";
                $sql = <<<SQL
                SELECT *
                FROM user 
                WHERE Username = '$username'
SQL;
                if(!$result = $database->query($sql)){
                    die('Fehler [' . $database->error . ']');
                }
                else{
                    echo "Username= ";
                    echo $username;
                    echo "<br>";
                    echo "UserID= ";

                    while($row = $result->fetch_assoc()){
                            //echo $row['UserID'];
                            $_SESSION['userid'] = $row['UserID'] ;
                            $_SESSION['Admin'] = $row['Administrator'];
                    }
                    $_SESSION['username'] = $username;
                    $userID = $_SESSION['userid'];
                    echo "<audio autoplay>";
                            echo "<source src='sounds/goodmorning1.ogg' type='audio/ogg'>";
                    echo "</audio>";
                    echo '<script type="text/javascript"> document.location = "index.php";</script>';
                }

            }
            else {
                echo "<script>alert('Name or password is not correct, try again!')</script>";
            }
        }
        if(isset($_POST['logout'])){
            $_SESSION['username'] = null;
            $_SESSION['userid'] = null;
            echo "<audio autoplay>";
                    echo "<source src='sounds/goodmorning1.ogg' type='audio/ogg'>";
            echo "</audio>";
            echo '<script type="text/javascript"> document.location = "index.php";</script>';
        }
    ?>
</html>
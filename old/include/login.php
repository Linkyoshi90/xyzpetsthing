<?php
if ($_SESSION['username'] !== null) {
    echo $_SESSION['username'] . " ";
    ?>
    <form action="" method="POST">
        <input type="submit" name="logout" class="btn btn-default" value="Logout">
    </form>
    <?php
} else {
    ?>
    <form action="" method="POST">
        Username
        <input type="text" name="username" required>
        Password
        <input type="password" name="password" required>
        <input type="submit" class="btn btn-primary" name="login" value="Login">
    </form>
    <a href=register.php>Registrieren</a>
    <?php
}
?>
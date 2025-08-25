<?php
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(isset($_POST['action']) && $_POST['action']==='temp'){
    temp_login();
    header('Location: ?pg=main');
    exit;
  }
  $email=trim($_POST['email']??''); $pass=$_POST['pass']??'';
  if(isset($_POST['action']) && $_POST['action']==='register'){
    $user=trim($_POST['username']??'');
    if(register($email,$user,$pass)){ header('Location: ?pg=main'); exit; }
    $err="Registration failed.";
  } else {
    if(login($email,$pass)){ header('Location: ?pg=main'); exit; }
    $err="Invalid login.";
  }
}
?>
<h1>Welcome</h1>
<?php if(!empty($err)) echo "<p class='err'>$err</p>"; ?>
<div class="grid two">
  <form method="post" class="card glass">
    <h2>Login</h2>
    <input name="email" type="email" placeholder="Email" required>
    <input name="pass" type="password" placeholder="Password" required>
    <button>Sign in</button>
  </form>
  <form method="post" class="card glass">
    <h2>Create account</h2>
    <input name="username" placeholder="Username" required>
    <input name="email" type="email" placeholder="Email" required>
    <input name="pass" type="password" placeholder="Password" required>
    <input type="hidden" name="action" value="register">
    <button>Create</button>
  </form>
    <form method="post">
      <input type="hidden" name="action" value="temp">
      <button>Continue as temp user</button>
    </form>
</div>
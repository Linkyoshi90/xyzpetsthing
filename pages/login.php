<?php
require_once __DIR__.'/../lib/input.php';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $action = input_string($_POST['action'] ?? '', 20);
    if($action === 'temp'){
    temp_login();
    header('Location: ?pg=main');
    exit;
  }
  $email = input_email($_POST['email'] ?? '');
  $pass = input_password($_POST['pass'] ?? '');
  if($action === 'register'){
    $user = input_string($_POST['username'] ?? '', 40);
    if(register($email,$user,$pass)){ header('Location: ?pg=main'); exit; }
    $err="Registration failed.";
  } else {
    $remember = !empty($_POST['remember']);
    if(login($email,$pass, $remember)){ header('Location: ?pg=main'); exit; }
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
    <label class="form-check">
      <input type="checkbox" name="remember" value="1"> Remember me
    </label>
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

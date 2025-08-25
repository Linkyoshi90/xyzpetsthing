<?php require_login();
$uid = current_user()['id'];
$species = q("SELECT * FROM species ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
if($_SERVER['REQUEST_METHOD']==='POST'){
  $sp = (int)($_POST['species_id'] ?? 0);
  $name = trim($_POST['name'] ?? '');
  $row = q("SELECT * FROM species WHERE id=?",[$sp])->fetch(PDO::FETCH_ASSOC);
  if($row && $name){
    q("INSERT INTO pets(user_id,name,species_id,color,hp) VALUES(?,?,?,?,?)",
      [$uid,$name,$sp,'default',$row['base_hp']]);
    // starter items
    q("INSERT INTO user_items(user_id,item_id,qty) VALUES(?,?,1) ON DUPLICATE KEY UPDATE qty=qty+1",[$uid,1]);
    q("INSERT INTO user_items(user_id,item_id,qty) VALUES(?,?,2) ON DUPLICATE KEY UPDATE qty=qty+2",[$uid,2]);
    header('Location: ?pg=main'); exit;
  }
  $err="Pick a species and name.";
}
?>
<h1>Create a Pet</h1>
<?php if(!empty($err)) echo "<p class='err'>$err</p>"; ?>
<form method="post" class="card glass form">
  <label>Name <input name="name" required maxlength="40"></label>
  <label>Species
    <select name="species_id" required>
      <option value="">— choose —</option>
      <?php foreach($species as $s): ?>
        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <button>Create</button>
</form>

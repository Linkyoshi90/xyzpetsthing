<?php require_login();
$uid = current_user()['id'];
$species = q("SELECT species_id, species_name, base_hp, base_atk, base_def, base_init FROM pet_species ORDER BY species_name")
    ->fetchAll(PDO::FETCH_ASSOC);
if($_SERVER['REQUEST_METHOD']==='POST'){
  $sp = (int)($_POST['species_id'] ?? 0);
  $name = trim($_POST['name'] ?? '');
  $row = q("SELECT species_id, species_name, base_hp, base_atk, base_def, base_init FROM pet_species WHERE species_id=?",[$sp])
      ->fetch(PDO::FETCH_ASSOC);
  if($row && $name){
    q("INSERT INTO pet_instances (owner_user_id, species_id, nickname, color_id, level, experience, hp_current, atk, def, initiative)
        VALUES (?,?,?,?,?,?,?,?,?,?)",
      [$uid,$sp,$name,1,1,0,$row['base_hp'],$row['base_atk'],$row['base_def'],$row['base_init']]);
    // starter items
    q("INSERT INTO user_inventory(user_id,item_id,quantity) VALUES(?,?,1) ON DUPLICATE KEY UPDATE quantity=quantity+1",[$uid,1]);
    q("INSERT INTO user_inventory(user_id,item_id,quantity) VALUES(?,?,2) ON DUPLICATE KEY UPDATE quantity=quantity+2",[$uid,2]);
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
      <option value=""> choose </option>
      <?php foreach($species as $s): ?>
        <option value="<?= $s['species_id'] ?>"><?= htmlspecialchars($s['species_name']) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <button>Create</button>
</form>
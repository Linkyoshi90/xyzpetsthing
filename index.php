<?php
require_once __DIR__.'/auth.php';
$pg = $_GET['pg'] ?? (current_user() ? 'main' : 'login');
$allowed = ['login','register','logout','main','create_pet','inventory','map'];
if(!in_array($pg,$allowed)) $pg = 'login';
include __DIR__.'/layout/header.php';
include __DIR__.'/pages/'.$pg.'.php';
include __DIR__.'/layout/footer.php';

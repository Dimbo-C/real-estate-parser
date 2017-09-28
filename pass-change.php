<?php
$newpass = $_POST['password'];
$file = 'pass.txt';

$handle = fopen('pass.txt', "w+");
fwrite($handle, $newpass);

$_SESSION['pass'] = $newpass;

$message = 'Password on mirafortis was changed to ' . $newpass;
mail('maxim.semenoff@gmail.com','Password changed', $message);

header("Location: " . $_SERVER['REQUEST_SCHEME'] . '://' .  $_SERVER['HTTP_HOST'] . "/parser/");
exit;
?>
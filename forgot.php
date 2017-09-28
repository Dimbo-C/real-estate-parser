<?php
//maxim.semenoff@gmail.com
$data = $_POST['mail'];
if ($data != 'maxim.semenoff@gmail.com') {
    exit('error');
}

$password = trim(fgets(fopen('pass.txt', 'r')));
$message = 'This is your password: ' . $password;
$result = mail($data, 'Forgotten Password', $message);
exit('success');

?>
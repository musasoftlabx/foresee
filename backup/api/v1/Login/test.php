<?php
http_response_code(200);die();
require '../functions.php';
//$s = ChainPDO("SELECT DateCreated FROM users LIMIT 10")->fetchAll();
//print_r(1);
echo 1;

die();
foreach (ChainPDO("SELECT DateCreated FROM users")->fetchAll() as $k => $v) {
    $unix = $v['DateCreated'];
    $date = date('Y-m-d H:i:s', $unix);
    ChainPDO("UPDATE users SET DateCreated = ? WHERE DateCreated = ?", [$date, $unix]);
}
echo 'done';

die();
include 'password.php';
$pass = user_hash_password('Musa1234');
print_r($pass);

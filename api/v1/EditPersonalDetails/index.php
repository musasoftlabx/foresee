<?php
require '../functions.php';

$_JSON = json_decode(file_get_contents('php://input'), true);
$_JWT  = $_JSON['token'];
Tokenify($_JWT, false);
$username = TokenDeconstruct($_JWT, 'username');
$entity   = $_JSON['entity'];
$value    = $_JSON['value'];

$selection = ChainPDO("UPDATE users SET $entity = ? WHERE username = ?", [$value, $username]);

echo 'Updated was successful!';

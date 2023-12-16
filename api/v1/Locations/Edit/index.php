<?php
require '../../functions.php';

$_JSON = json_decode(file_get_contents('php://input'), true);
$_JWT  = $_JSON['token'];
Tokenify($_JWT, false);
$username = TokenDeconstruct($_JWT, 'username');
$entity   = $_JSON['entity'];
$key      = $_JSON['key'];
$value    = $_JSON['value'];

if (!$value) {
    $value = 0;
}

ChainPDO("UPDATE locations SET $entity = ?, ModifiedBy = ?, ModifiedOn = now() WHERE PK = ?", [$value, $username, $key]);

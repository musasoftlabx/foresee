<?php
require '../../functions.php';

$_JSON = json_decode(file_get_contents('php://input'), true);
$_JWT  = $_JSON['token'];
Tokenify($_JWT, false);
$username = TokenDeconstruct($_JWT, 'username');
$resource = $_JSON['resource'];
$key      = $_JSON['key'];
$entity   = $_JSON['entity'];
$value    = $_JSON['value'];
ChainPDO("UPDATE sections SET $entity = ?, ModifiedBy = ?, ModifiedOn = now() WHERE PK = ?", [$value, $username, $key]);

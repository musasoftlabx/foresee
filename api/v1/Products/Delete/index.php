<?php
require '../../functions.php';

$_JSON = json_decode(file_get_contents('php://input'), true);
$_JWT  = $_JSON['token'];
Tokenify($_JWT, false);
$key = $_JSON['key'];
ChainPDO("DELETE FROM stores WHERE PK = ?", [$key]);

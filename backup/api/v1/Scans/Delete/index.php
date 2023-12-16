<?php
require '../../functions.php';

$_JSON = json_decode(file_get_contents('php://input'), true);
$_JWT  = $_JSON['token'];
Tokenify($_JWT, false);
$keys = $_JSON['keys'];

foreach ($keys as $key) {
	ChainPDO("DELETE FROM scans WHERE PK = ?", [$key]);
}

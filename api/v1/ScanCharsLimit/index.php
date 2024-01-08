<?php
require '../functions.php';
//$token = RetrieveAndValidateTokenFromRequest();
header('Content-Type: application/json');

$_JSON = json_decode(file_get_contents('php://input'), true);

echo json_encode(ChainPDO("SELECT scanCharsLimit, `strict` FROM defaults")->fetch());
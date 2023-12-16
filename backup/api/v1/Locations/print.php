<?php
require '../functions.php';
$token = RetrieveAndValidateTokenFromRequest();
header('Content-Type: application/json');

$_JSON = json_decode(file_get_contents('php://input'), true);

$from = $_JSON['from'];
$to = $_JSON['to'];
$storeId = $_JSON['storeId'];

echo json_encode([
    'data' => ChainPDO("SELECT code FROM locations WHERE storeId = ? AND CONVERT(SUBSTRING(code, 2), SIGNED INTEGER) BETWEEN ? AND ?", [$storeId, $from, $to])->fetchAll()
]);
          
<?php
require '../../functions.php';
$token = RetrieveAndValidateTokenFromRequest();
header('Content-Type: application/json');

$_JSON = json_decode(file_get_contents('php://input'), true);

ChainPDO("INSERT INTO scans VALUES (NULL, ?, ?, ?, now(), ?, ?, NULL)", [
    $_JSON['storeId'],
    $_JSON['location'],
    $_JSON['barcode'],
    'ADDED',
    'BARCODE-CONTROLLED'
]);

http_response_code(201);
echo json_encode([
    'added' => true
]);

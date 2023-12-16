<?php
require '../functions.php';
$token = RetrieveAndValidateTokenFromRequest();
header('Content-Type: application/json');

$_JSON = json_decode(file_get_contents('php://input'), true);
$username = TokenDeconstruct($token, 'username');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $storeId = $_GET['storeId'];
    $query = $_GET['query'];

    echo json_encode(ChainPDO("SELECT SUBSTR(code, 1, 5) AS stripped, code FROM locations WHERE storeId = ? AND code REGEXP ?", [$storeId, $query])->fetchAll());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ChainPDO("INSERT INTO scans VALUES (NULL, ?, ?, ?, now(), ?, ?, NULL)", [
        $_JSON['storeId'],
        $_JSON['code'],
        $_JSON['barcode'],
        'DUPLICATED',
        'BARCODE-CONTROLLED'
    ]);

    http_response_code(201);
    echo json_encode([
        'duplicated' => true
    ]);

    LogToDB(json_encode(
        [
            'logtype'   => 'INFO',
            'operation' => 'DATABASE',
            'service'   => 'Barcode added by ' . $username,
            'endpoint'  => dirname(__FILE__),
            'request'   => file_get_contents('php://input'),
            'response'  => null,
        ]
    ));
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $column = $_JSON['column'];
    ChainPDO("UPDATE scans SET $column = ? WHERE id = ?", [$_JSON['barcode'], $_JSON['id']]);

    http_response_code(201);
    echo json_encode([
        'edited' => true
    ]);

    LogToDB(json_encode(
        [
            'logtype'   => 'INFO',
            'operation' => 'DATABASE',
            'service'   => 'Barcode edited by ' . $username,
            'endpoint'  => dirname(__FILE__),
            'request'   => file_get_contents('php://input'),
            'response'  => null,
        ]
    ));
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    ChainPDO("DELETE FROM scans WHERE id = ?", [$_JSON['id']]);

    http_response_code(201);
    echo json_encode([
        'deleted' => true
    ]);

    LogToDB(json_encode(
        [
            'logtype'   => 'INFO',
            'operation' => 'DATABASE',
            'service'   => 'Barcode deleted by ' . $username,
            'endpoint'  => dirname(__FILE__),
            'request'   => file_get_contents('php://input'),
            'response'  => null,
        ]
    ));
}
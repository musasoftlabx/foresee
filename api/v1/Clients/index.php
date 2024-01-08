<?php
require '../functions.php';
$token = RetrieveAndValidateTokenFromRequest();
header('Content-Type: application/json');

$_JSON = json_decode(file_get_contents('php://input'), true);

$addedBy = TokenDeconstruct($token, 'username');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode(ChainPDO("SELECT `id`, `client`, modifiedBy, DATE_FORMAT(modifiedOn, '%a, %D %m %y at %r') AS modifiedOn FROM clients ORDER BY `id` DESC")->fetchAll());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {
        $exists = ChainPDO("SELECT `client` FROM clients WHERE `client` = ?", [$_JSON['client']])->fetch(PDO::FETCH_COLUMN);

        if (!$exists) {
            ChainPDO("INSERT INTO clients VALUES (NULL, ?, ?, now())", [
                strip_tags($_JSON['client']),
                $addedBy
            ]);
    
            $LastID = $DB->lastInsertId();
    
            echo json_encode(ChainPDO("SELECT `id`, `client`, modifiedBy, DATE_FORMAT(modifiedOn, '%a, %D %m %y at %r') AS modifiedOn FROM clients WHERE id = ?", [$LastID])->fetch());
        } else {
            http_response_code(403);
            echo SERVER_ERROR;

            LogToDB(json_encode(
                [
                    'logtype'   => 'ERROR',
                    'operation' => 'DATABASE',
                    'service'   => basename(dirname(__FILE__)),
                    'request'   => file_get_contents('php://input'),
                    'response'  => json_encode($e),
                ]
            ));
        }

    } catch (PDOException $e) {
        http_response_code(500);
        echo SERVER_ERROR;

        LogToDB(json_encode(
            [
                'logtype'   => 'ERROR',
                'operation' => 'DATABASE',
                'service'   => basename(dirname(__FILE__)),
                'request'   => file_get_contents('php://input'),
                'response'  => json_encode($e),
            ]
        ));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $entity = $_JSON['entity'];
    $value = $_JSON['value'];
    $username = $_JSON['username'];
    ChainPDO("UPDATE users SET $entity = ? WHERE username = ?", [$value, $username]);
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $path = '../../uploads/avatars/' . $_JSON['username'].'.jpg';
    if (file_exists($path)) {
        unlink($path);
    }
    ChainPDO("DELETE FROM users WHERE username = ?", [$_JSON['username']]);
}

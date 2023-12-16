<?php
require '../functions.php';
$token = RetrieveAndValidateTokenFromRequest();
header('Content-Type: application/json');

$_JSON = json_decode(file_get_contents('php://input'), true);

$addedBy = TokenDeconstruct($token, 'username');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode(ChainPDO("SELECT id, firstName, lastName, username, password, role, profilePicture, addedBy, DATE_FORMAT(addedOn, '%a, %D-%m-%y at %r') AS addedOn FROM users ORDER BY id DESC")->fetchAll());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $passkey  = substr(str_shuffle("23456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnopqrstuvwxyz"), 0, 8);
    $password = PasswordHasher($passkey);

    try {
        $count = ChainPDO("SELECT COUNT(id) FROM users WHERE username REGEXP ?", [$_JSON['username']])->fetch(PDO::FETCH_COLUMN);

        if ($count > 0) {
            $username = $$_JSON['username'] . $count;
        }

        ChainPDO("INSERT INTO users VALUES (NULL, ?, ?, ?, ?, ?, 1, ?, now(), ?)", [
            ucwords(strtolower(strip_tags($_JSON['firstName']))),
            ucwords(strtolower(strip_tags($_JSON['lastName']))),
            $_JSON['username'],
            substr($password, 0, 5),
            $_JSON['role'],
            'https://foresee-technologies.com/img/avatar.jpg',
            $addedBy
        ]);

        $LastID = $DB->lastInsertId();

        echo json_encode(ChainPDO("SELECT id, firstName, lastName, username, password, role, addedBy, DATE_FORMAT(addedOn, '%a, %D %m %y at %r') AS addedOn FROM users WHERE id = ?", [$LastID])->fetch());
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

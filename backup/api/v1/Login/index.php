<?php
require '../functions.php';

$_JSON    = json_decode(file_get_contents('php://input'), true);
$domain   = strip_tags(isset($_JSON['domain']) ? $_JSON['domain'] : '');
$username = strip_tags($_JSON['username']);
$password = base64_decode(strip_tags($_JSON['password']));
//$password        = PasswordHasher($password);

$exists = ChainPDO("SELECT COUNT(*) FROM users WHERE username = ? AND password = ?", [$username, $password])->fetchColumn();

if ($exists === 0) {
    http_response_code(401);
    echo json_encode(
        [
            'error'   => 'Invalid Credentials',
            'message' => "Credentials mismatch. Kindly try again.",
        ]
    );

    LogToDB(json_encode(
        [
            'logtype'   => 'WARNING',
            'operation' => 'DATABASE',
            'service'   => basename(dirname(__FILE__)),
            'request'   => "{ username: " . $username . "}",
            'response'  => "Credentials mismatch. Kindly try again.",
        ]
    ));
} else {
    $selection = ChainPDO("SELECT FirstName, LastName, username, role FROM users WHERE username = ? AND password = ?", [$username, $password])->fetch();

    echo json_encode(
        [
            'token' => JWT(json_encode(
                [
                    'FirstName' => $selection['FirstName'],
                    'LastName'  => $selection['LastName'],
                    'username'  => $selection['username'],
                    'role'    => $selection['role'],
                ]
            ))
        ]
    );
}

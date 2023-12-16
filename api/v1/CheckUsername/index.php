<?php
require '../functions.php';

$_JSON    = json_decode(file_get_contents('php://input'), true);
$username = strip_tags($_JSON['username']);

$count = ChainPDO("SELECT COUNT(username) FROM users WHERE username = ?", [$username])->fetchColumn();

if ($count > 0) {
    http_response_code(401);
    echo json_encode(false);
} else {
    echo json_encode(true);
}

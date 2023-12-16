<?php
require '../functions.php';

$_JSON = json_decode(file_get_contents('php://input'), true);
$_JWT  = $_JSON['token'];
Tokenify($_JWT, false);

$EmailAddress        = TokenDeconstruct($_JWT, 'EmailAddress');
$CurrentPassword     = $_JSON['CurrentPassword'];
$NewPassword         = $_JSON['NewPassword'];
$CurrentPasswordHash = PasswordHasher($CurrentPassword);
$NewPasswordHash     = PasswordHasher($NewPassword);

$count = ChainPDO("SELECT COUNT(EmailAddress) FROM users WHERE EmailAddress = ? AND password = ?", [$EmailAddress, $CurrentPasswordHash])->fetchColumn();

if ($count === 0) {
    http_response_code(401);
    echo json_encode([
        'visible' => true,
        'icon'    => 'mdi-lock-alert',
        'color'   => 'error',
        'title'   => 'Invalid password',
        'content' => 'Current password entered is invalid.',
    ]);
} else {
    ChainPDO("UPDATE users SET password = ?, FirstTimeLogin = 0 WHERE EmailAddress = ?", [$NewPasswordHash, $EmailAddress]);
    echo json_encode([
        'task'      => 'show',
        'operation' => 'OK',
        'truth'     => 'OK',
        'falsy'     => 'HIDDEN',
        'icon'      => 'mdi-check-decagram',
        'color'     => 'primary',
        'title'     => 'Password changed!',
        'content'   => 'Your password was successfully changed. We will log you out for you to login with your new password.',
    ]);
}

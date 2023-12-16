<?php
require '../functions.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_JSON               = json_decode(file_get_contents('php://input'), true);
    $UUID                = $_JSON['UUID'];
    $Base64Image         = $_JSON['Base64Image'];
    $Base64ImageMetadata = $_JSON['Base64ImageMetadata'];
    $FormData            = $_JSON['FormData'];
    $username            = $_JSON['username'];

    $doctype  = 'avatars';
    $endpoint = 'https://foresee-technologies.com/api/';

    $_JWT = $FormData['token'];
    Tokenify($_JWT, false);
    $addedBy = TokenDeconstruct($_JWT, 'username');

    $binary   = base64_decode($Base64Image);
    $ext      = strtolower(pathinfo($Base64ImageMetadata['name'], PATHINFO_EXTENSION));
    $filename = $username . '.' . $ext;

    $whitelist = ['png', 'jpeg', 'jpg', 'gif'];

    $path = '../../uploads/' . $doctype . '/';

    if (!is_dir($path) && $doctype === 'avatars') {
        mkdir($path, 0777, true);
    }

    if (in_array($ext, $whitelist)) {
        $path = $path . $filename;
        $URL  = $endpoint . str_replace('../../', '', $path);

        file_put_contents($path, $binary);

        try {
            ChainPDO("UPDATE users SET profilePicture = ? WHERE username = ?", [$URL, $username]);

            echo json_encode([
                'uuid' => $UUID,
                'URL'  => $URL,
            ]);
        } catch (PDOException $e) {
            LogToDB(json_encode(
                [
                    'logtype'   => 'ERROR',
                    'operation' => 'DATABASE',
                    'service'   => basename(dirname(__FILE__)),
                    'endpoint'  => dirname(__FILE__),
                    'request'   => '{ "FilePath": "' . $URL . '" }',
                    'response'  => '{ "PDO Error": "' . $e . '" }',
                ]
            ));
        }
    } else {
        LogToDB(json_encode(
            [
                'logtype'   => 'ERROR',
                'operation' => 'DATABASE',
                'service'   => basename(dirname(__FILE__)),
                'endpoint'  => dirname(__FILE__),
                'request'   => null,
                'response'  => '{ "Error": "Invalid file type!" }',
            ]
        ));
        http_response_code(500);
        echo 'Invalid file type!';
    }
}

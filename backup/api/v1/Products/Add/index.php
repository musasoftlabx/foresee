<?php
require '../../functions.php';

$_JSON = json_decode(file_get_contents('php://input'), true);
$_JWT  = $_JSON['token'];
Tokenify($_JWT, false);
$username    = TokenDeconstruct($_JWT, 'username');

$barcode   = $_JSON['barcode'];

$count = ChainPDO("SELECT COUNT(barcode) FROM products WHERE barcode = ?", [$barcode])->fetchColumn();

if ($count > 0) {
    http_response_code(403);
    echo 'Product already exists.';
} else {
   try
    {
        $DB->beginTransaction();

        ChainPDO("INSERT INTO products VALUES (NULL, ?, NULL, ?, ?, ?, ?, ?, ?, ?, ?, NULL, now())", [
            $_JSON['section'],
            $barcode,
            strip_tags($_JSON['name']),
            strip_tags($_JSON['quantity']),
            strip_tags($_JSON['ColorCode']),
            strip_tags($_JSON['color']),
            strip_tags($_JSON['size']),
            strip_tags($_JSON['length']),
            strip_tags($_JSON['SpecialCode']),
        ]);

        $LastID = $DB->lastInsertId();

        $data = ChainPDO("SELECT *, PK AS id, DATE_FORMAT(LastScannedOn, '%D-%m-%y at %r') AS LastScannedOn FROM products WHERE PK = ?", [$LastID])->fetch();

        $DB->commit();

        echo json_encode($data);

    } catch (PDOException $e) {
        $DB->rollBack();
        http_response_code(500);
        echo SERVER_ERROR;
    } 
}
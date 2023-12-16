<?php
require '../../functions.php';

$_JSON = json_decode(file_get_contents('php://input'), true);
$_JWT  = $_JSON['token'];
Tokenify($_JWT, false);
$username    = TokenDeconstruct($_JWT, 'username');
$CountryCode = $_JSON['country']['code'];
$country     = $_JSON['country']['name'];
$StoreName   = $_JSON['StoreName'];

$count = ChainPDO("SELECT COUNT(*) FROM stores")->fetchColumn();

$count == 0 ? $count = 1 : $count++;

$StoreCode = $CountryCode . str_pad($count, 3, '0', STR_PAD_LEFT);

try
{
    $DB->beginTransaction();

    ChainPDO("INSERT INTO stores VALUES (NULL, ?, ?, ?, ?, now())", [
        $StoreCode,
        $StoreName,
        $country,
        $username,
    ]);

    $DB->commit();

    $data = ChainPDO("SELECT PK AS id, code, name, country, ModifiedBy, ModifiedOn FROM stores ORDER BY PK DESC LIMIT 1")->fetch();

    $data['ModifiedOn'] = date('D, jS \of M Y \a\t H:i:s a', strtotime($data['ModifiedOn']));

    echo json_encode($data);

} catch (PDOException $e) {
    $DB->rollBack();
    http_response_code(500);
    echo SERVER_ERROR;
}

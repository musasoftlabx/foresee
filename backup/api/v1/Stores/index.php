<?php
require '../functions.php';
$token = RetrieveAndValidateTokenFromRequest();
header('Content-Type: application/json');

$_JSON = json_decode(file_get_contents('php://input'), true);

$username = TokenDeconstruct($token, 'username');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['query'])) {
        $query = strtolower($_GET['query']);
        echo json_encode(ChainPDO("SELECT id, code, name, country, client FROM stores WHERE name REGEXP ? ORDER BY id DESC", [$query])->fetchAll());
        die();
    }

    $dataset = ChainPDO("SELECT id, code, name, country, client, modifiedBy, modifiedOn FROM stores ORDER BY id DESC")->fetchAll();

    foreach ($dataset as $key => $data) {
        $dataset[$key]['modifiedOn'] = date('D, jS \of M Y \a\t H:i:s a', strtotime($data['modifiedOn']));
    }

    echo json_encode([
        'workingStore' => ChainPDO("SELECT workingStore, workingDate FROM defaults")->fetch(),
        'stores' => $dataset
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $CountryCode = $_JSON['country']['code'];
    $country     = $_JSON['country']['name'];
    $StoreName   = $_JSON['StoreName'];
    $client   = $_JSON['client'];

    $maxCode = ChainPDO("SELECT COALESCE(MAX(code),0) FROM stores WHERE country = ?", [$country])->fetchColumn();
    $count = preg_replace('/[^0-9]/', '', $maxCode);

    !$maxCode ? $count = 1 : $count++;

    $StoreCode = $CountryCode . str_pad($count, 2, '0', STR_PAD_LEFT);

    try {
        $nameExists = ChainPDO("SELECT COUNT(name) FROM stores WHERE country = ? AND name = ?", [$country, $StoreName])->fetchColumn();

        if ($nameExists > 0) {
            http_response_code(403);
            echo json_encode([
                'title' => 'Store name conflict',
                'content' => '<b>' . $StoreName . '</b>  already exists in <b>' . $country . '</b>. Use a different store name by adding a town to the store name. e.g. <b>' . $StoreName . ' (Town)</b>'
            ]);
        } else {
            $DB->beginTransaction();

            ChainPDO("INSERT INTO stores VALUES (NULL, ?, ?, ?, ?, now(), ?)", [
                $StoreCode,
                ucwords($StoreName),
                $country,
                $client,
                $username,
            ]);

            $DB->commit();

            $data = ChainPDO("SELECT id, code, name, country, client, modifiedBy, modifiedOn FROM stores ORDER BY id DESC LIMIT 1")->fetch();

            $data['modifiedOn'] = date('D, jS \of M Y \a\t H:i:s a', strtotime($data['modifiedOn']));

            http_response_code(201);
            echo json_encode($data);
        }
    } catch (PDOException $e) {
        $DB->rollBack();
        http_response_code(500);
        echo json_encode([
            'title' => 'Server Error',
            'content' => SERVER_ERROR
        ]);

        LogToDB(json_encode(
            [
                'logtype'   => 'ERROR',
                'operation' => 'DATABASE',
                'service'   => basename(dirname(__FILE__)),
                'request'   => file_get_contents('php://input'),
                'response'  => json_encode($e->getMessage()),
            ]
        ));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    ChainPDO("UPDATE defaults SET workingStore = ?, workingDate = ?, workingStoreDate = ?, scanCharsLimit = ?", [$_JSON['workingStore'], $_JSON['workingDate'], $_JSON['workingStoreDate'], $_JSON['scanCharsLimit']]);
    echo json_encode(['success' => true]);
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    ChainPDO("DELETE FROM stores WHERE id = ?", [$_JSON['key']]);

    LogToDB(json_encode(
        [
            'logtype'   => 'INFO',
            'operation' => 'DATABASE',
            'service'   => basename(dirname(__FILE__)),
            'request'   => file_get_contents('php://input'),
            'response'  => 'Store deleted by ' . $username,
        ]
    ));
}

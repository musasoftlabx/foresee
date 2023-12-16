<?php
require '../functions.php';

$_JSON = json_decode(file_get_contents('php://input'), true);

$requests = [];

if ($_JSON['operation'] === 'querify') {
    $keyword = $_JSON['keyword'];

    $SELECT = ChainPDO("SELECT * FROM users
        WHERE MATCH(
            ApigeeDeveloperID,
            FirstName,
            LastName,
            EmailAddress,
            username,
            PhoneNumber,
            country,
            AccountType,
            CompanyName,
            OperatingSystem,
            browser,
            DateCreated,
            DateModified
        ) AGAINST('+" . $keyword . "*' IN BOOLEAN MODE) ORDER BY PK DESC"
    )->fetchAll();

    foreach ($SELECT as $key => $value) {
        array_push($requests, [
            '_'               => $value['PK'],
            'credentials'     => [
                'ApigeeDeveloperID' => $value['ApigeeDeveloperID'],
                'FirstName'         => $value['FirstName'],
                'LastName'          => $value['LastName'],
                'EmailAddress'      => $value['EmailAddress'],
                'username'          => $value['username'],
                'PhoneNumber'       => $value['PhoneNumber'],
            ],
            'country'         => $value['country'],
            'AccountType'     => $value['AccountType'],
            'CompanyName'     => $value['CompanyName'],
            'OperatingSystem' => $value['OperatingSystem'],
            'browser'         => $value['browser'],
            'DateCreated'     => $value['DateCreated'],
            'DateModified'    => $value['DateModified'],
        ]);
    }
} else if ($_JSON['operation'] === 'datarize') {
    $limit  = $_JSON['limit'];
    $offset = $_JSON['offset'];

    foreach (ChainPDO("SELECT * FROM users ORDER BY PK DESC LIMIT ?, ?", [$offset, $limit])->fetchAll() as $key => $value) {
        array_push($requests, [
            '_'               => $value['PK'],
            'credentials'     => [
                'ApigeeDeveloperID' => $value['ApigeeDeveloperID'],
                'FirstName'         => $value['FirstName'],
                'LastName'          => $value['LastName'],
                'EmailAddress'      => $value['EmailAddress'],
                'username'          => $value['username'],
                'PhoneNumber'       => $value['PhoneNumber'],
            ],
            'country'         => $value['country'],
            'AccountType'     => $value['AccountType'],
            'CompanyName'     => $value['CompanyName'],
            'OperatingSystem' => $value['OperatingSystem'],
            'browser'         => $value['browser'],
            'DateCreated'     => $value['DateCreated'],
            'DateModified'    => $value['DateModified'],
            'loading'         => [
                'delete' => false,
            ],
        ]);
    }
}

echo json_encode([
    'headers' => [
        ['text' => 'Credentials', 'value' => 'credentials'],
        ['text' => 'Country', 'value' => 'country'],
        ['text' => 'Account Type', 'value' => 'AccountType'],
        ['text' => 'Operating System', 'value' => 'OperatingSystem'],
        ['text' => 'Browser', 'value' => 'browser'],
        ['text' => 'Date Created', 'value' => 'DateCreated'],
        ['text' => 'Date Modified', 'value' => 'DateModified'],
    ],
    'dataset' => $requests,
    'count'   => (int) ChainPDO("SELECT COUNT(PK) FROM users")->fetchColumn(),
]);

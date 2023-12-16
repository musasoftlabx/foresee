<?php
require '../../functions.php';
Tokenify($_GET['token'], false);
echo json_encode(ChainPDO("SELECT *, PK AS id, DATE_FORMAT(LastScannedOn, '%D-%m-%y at %r') AS LastScannedOn FROM products WHERE location = ?", [$_GET['location']])->fetchAll());

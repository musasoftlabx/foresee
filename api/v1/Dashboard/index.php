<?php
require '../functions.php';

RetrieveAndValidateTokenFromRequest();

header('Content-Type: application/json');

echo json_encode([
    'stores'        => ChainPDO("SELECT COUNT(id) FROM stores")->fetch(PDO::FETCH_COLUMN),
    'locations'     => ChainPDO("SELECT COUNT(id) FROM locations")->fetch(PDO::FETCH_COLUMN),
    'products'      => ChainPDO("SELECT COUNT(PK) FROM products")->fetch(PDO::FETCH_COLUMN),
    'scanOperators' => ChainPDO("SELECT COUNT(id) FROM users WHERE role = ?", ['Scanner'])->fetch(PDO::FETCH_COLUMN),
    'discrepancies' => ChainPDO("SELECT SUM(physicalCount) AS physicalCount, SUM(systemCount) AS systemCount FROM locations")->fetch(),
    'scanners'      => ChainPDO("SELECT FirstName, LastName, ProfilePicture, scannedBy, COUNT(scans.id) AS count FROM scans INNER JOIN users ON scannedBy = username GROUP BY scannedBy ORDER BY count DESC LIMIT 3")->fetchAll(),
    'scans'         => ChainPDO("SELECT HOUR(scannedOn) AS date, COUNT(id) AS count, IF(MAX(id) = (SELECT MAX(id) FROM scans WHERE HOUR(scannedOn) = HOUR(now())), 1, 0) AS opacity FROM scans WHERE MONTH(scannedOn) = MONTH(CURRENT_DATE()) GROUP BY HOUR(scannedOn) ORDER BY date")->fetchAll(),
    'scansPerStore' => ChainPDO("SELECT name, CAST(SUM(physicalCount) AS SIGNED) AS physicalCount, CAST(SUM(systemCount) AS SIGNED) AS systemCount FROM locations JOIN stores WHERE storeId = stores.id GROUP BY name ORDER BY physicalCount DESC")->fetchAll()
]);

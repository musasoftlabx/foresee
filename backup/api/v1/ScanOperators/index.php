<?php
require '../functions.php';
$token = RetrieveAndValidateTokenFromRequest();
header('Content-Type: application/json');

$_JSON = json_decode(file_get_contents('php://input'), true);

$defaults = ChainPDO("SELECT workingStore, workingDate FROM defaults")->fetch();

$workingDate = $defaults['workingDate'];
$workingStore = json_decode($defaults['workingStore'], true)['id'];

echo json_encode(ChainPDO("SELECT firstName, lastName, profilePicture, scannedBy, COUNT(scans.id) AS scans, batteryLevel,
    (
        SELECT COUNT(id)
        FROM locations
        WHERE isVerified = 1
        AND lastScannedBy = scans.scannedBy
        AND DATE_FORMAT(lastScannedOn,'%Y-%m-%d') = ?
    ) AS locations
    FROM scans
    INNER JOIN users
    ON scans.scannedBy = users.username
    WHERE scans.storeId = ?
    AND DATE_FORMAT(scannedOn,'%Y-%m-%d') = ?
    GROUP BY scannedBy
    ORDER BY scans DESC", [$workingDate, $workingStore, $workingDate])->fetchAll());
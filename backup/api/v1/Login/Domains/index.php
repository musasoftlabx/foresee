<?php
require '../../functions.php';
echo json_encode(ChainPDO("SELECT domain FROM domains ORDER BY domain")->fetchAll(PDO::FETCH_COLUMN));

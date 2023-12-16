<?php
require 'config.php';

$_HOST     = DATABASE_HOST;
$_USERNAME = DATABASE_USERNAME;
$_PASSWORD = DATABASE_PASSWORD;
$_DATABASE = DATABASE_NAME;
$_CHARSET  = DATABASE_CHARSET;
$_DRIVER   = "mysql:host=$_HOST;dbname=$_DATABASE;charset=$_CHARSET";
$_OPTIONS  = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $DB  = new PDO($_DRIVER, $_USERNAME, $_PASSWORD, $_OPTIONS);
    $LOG = new PDO($_DRIVER, $_USERNAME, $_PASSWORD, $_OPTIONS);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int) $e->getCode());
}

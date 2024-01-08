<?php
require '/var/www/html/api/v1/functions.php';
require '/var/www/html/api/libraries/PHPSpreadSheet/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

//Tokenify($_GET['token'], false);
$endpoint = 'https://foresee-technologies.com/';

$storeId = $_GET['storeId'];

$dataset = ChainPDO("SELECT code, physicalCount, systemCount FROM locations WHERE storeId = ?", [$storeId])->fetchAll();

$count = count($dataset);

$path = API_ROOT . 'docs/generated/';

if (!is_dir($path)) {
    mkdir($path, 0777, true);
}

$filename = basename(dirname(__FILE__)) . '.' . date('d-m-Y.H:i:s') . '.xlsx';
$path     = $path . $filename;

ini_set('memory_limit', '-1');
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Accept-Ranges: bytes');
header('Cache-Control: max-age=0');
header('Content-Disposition: attachment; filename=' . $filename);

$rangeHeaders = 'A1:A3';
$rangeDataset = 'A2:C' . ($count - 1);

$arrayData = [
    [
        'Location',
        'Physical Count',
        'System Count'
    ],
];

for ($i = 0; $i < $count; $i++) {
    array_push($arrayData, [
        $dataset[$i]['code'],
        $dataset[$i]['physicalCount'],
        $dataset[$i]['systemCount']
    ]);
}

$spreadsheet = new Spreadsheet();
$spreadsheet->getActiveSheet()->fromArray($arrayData, null, 'A1');
$spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(20);
$spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(12);
$spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(12);
$spreadsheet->getActiveSheet()->setAutoFilter($rangeHeaders);

$writer = new Xlsx($spreadsheet);
$writer->save($path);
readfile($path);
<?php
require '../../../functions.php';
Tokenify($_GET['token'], false);
$dataset = ChainPDO("SELECT barcode, COUNT(barcode) AS quantity FROM scans WHERE storeId = ? GROUP BY barcode ORDER BY quantity DESC", [$_GET['storeId']])->fetchAll();

if ($_GET['context'] === 'display') {
    echo json_encode($dataset);
} else {
    $filename = 'scanned.txt';
    $myfile   = fopen("$filename", "w") or die("Unable to open file!");
    foreach ($dataset as $key => $value) {
        fwrite($myfile, $value['barcode'] . "\t" . $value['quantity'] . "\n");
    }
    fclose($myfile);
    header('Content-Description: File Download');
    header('Content-Transfer-Encoding: binary');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($filename)) . ' GMT');
    header('Accept-Ranges: bytes');
    header('Content-Length: ' . filesize($filename));
    header('Content-Encoding: none');
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename=' . $filename);
    readfile($filename);
}

//$filename  = 'scanned.txt'; // . date('d-m-Y H:i:s', time()) . '.txt'; ----- Different filenames
/*foreach (glob($directory . '*.txt*') as $file) { ----- Delete file less than a specified time
if (filemtime($file) < time() - 60) {
unlink($file);
}
}*/

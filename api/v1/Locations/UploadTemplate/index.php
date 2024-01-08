<?php
require '/var/www/html/api/v1/functions.php';
$token = RetrieveAndValidateTokenFromRequest();
header('Content-Type: application/json');
ini_set('memory_limit', '-1');
ini_set('max_execution_time', '300');

require '/var/www/html/api/libraries/PHPSpreadSheet/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$_JSON = json_decode(file_get_contents('php://input'), true);

$username = TokenDeconstruct($token, 'username');

function retrieveExcelFields($filename, $ext) {
    try {
        $inputFileType = ucwords($ext);
        $inputFileName = '../../uploads/excel/'.$filename;
        class MyReadFilter implements IReadFilter
        {
            public function readCell($column, $row, $worksheetName = '')
            {
                if (in_array($column, range('A', 'C'))) {
                    return true;
                }
                return false;
            }
        }
        $filterSubset = new MyReadFilter();
        $reader       = IOFactory::createReader($inputFileType);
        $reader->setReadFilter($filterSubset);
        $reader->setReadEmptyCells(false);
        $spreadsheet = $reader->load($inputFileName);
        $sheetData   = $spreadsheet->getActiveSheet()->toArray(
            '', //Value returned in the array entry if a cell doesn't exist / empty cell is encountered.
            true, //Should formulas be calculated for each cell?
            true, //Should formatting be applied to cell values?
            false//Should the array be indexed by actual row and column IDs (true) or by numbers counting from zero.
        );
        $reader = null;
    } catch(\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
        die('Error loading file: '.$e->getMessage());
        http_response_code(500);
        echo json_encode([
            'title' => 'Error loading file.',
            'content' => $e->getMessage()
        ]);

        LogToDB(json_encode(
            [
                'logtype'   => 'ERROR',
                'operation' => 'FILE SYSTEM',
                'service'   => basename(dirname(__FILE__)),
                'request'   => $filename,
                'response'  => $e->getMessage(),
            ]
        ));
    }

    $dataset = [];

    foreach ($sheetData[0] as $key => $value) {
        array_push($dataset, [
            'field' => $value,
            'sample' => $sheetData[1][$key]
        ]);
    }

    return $dataset;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_GET['Upload'])) {
        $UUID                = $_JSON['UUID'];
        $Base64Image         = $_JSON['Base64Image'];
        $Base64ImageMetadata = $_JSON['Base64ImageMetadata'];
        $FormData            = $_JSON['FormData'];

        $doctype  = 'excel';
        $endpoint = 'https://foresee-technologies.com/api/';

        $binary   = base64_decode($Base64Image);
        $ext      = strtolower(pathinfo($Base64ImageMetadata['name'], PATHINFO_EXTENSION));
        $filename = $UUID . '.' . $ext;

        $whitelist = ['xlsx', 'xls', 'csv'];

        $path = '../../uploads/' . $doctype . '/';

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        if (in_array($ext, $whitelist)) {
            $path = $path . $filename;
            $URL  = $endpoint . str_replace('../../', '', $path);

            file_put_contents($path, $binary);

            $dataset = [];
            $columns = ChainPDO("SHOW COLUMNS FROM locations")->fetchAll(PDO::FETCH_COLUMN);
            $accepted = ['code', 'physicalCount', 'systemCount'];

            foreach ($columns as $key => $value) {
                if (in_array($value, $accepted)) {
                    array_push($dataset, [
                        'column' => $value,
                        'formattedColumn' => ucwords($value),
                        'excelField' => ChainPDO("SELECT excel_field FROM excel_mappings WHERE db_column = ? AND `table` = ?", [$value, 'locations'])->fetch(PDO::FETCH_COLUMN)
                    ]);
                }
            }

            try {
                echo json_encode([
                    'uuid' => $UUID,
                    'filename'  => $filename,
                    'DBcolumns' => $dataset,
                    'excelFields' => retrieveExcelFields($filename, $ext)
                ]);
            } catch (PDOException $e) {
                LogToDB(json_encode(
                    [
                        'logtype'   => 'ERROR',
                        'operation' => 'DATABASE',
                        'service'   => basename(dirname(__FILE__)),
                        'endpoint'  => dirname(__FILE__),
                        'request'   => '{ "FilePath": "' . $URL . '" }',
                        'response'  => '{ "PDO Error": "' . $e . '" }',
                    ]
                ));
            }
        } else {
            LogToDB(json_encode(
                [
                    'logtype'   => 'ERROR',
                    'operation' => 'DATABASE',
                    'service'   => basename(dirname(__FILE__)),
                    'endpoint'  => dirname(__FILE__),
                    'request'   => null,
                    'response'  => '{ "Error": "Invalid file type!" }',
                ]
            ));
            http_response_code(500);
            echo 'Invalid file type!';
        }
    }

    if (isset($_GET['Commit'])) {
        $filename = $_JSON['filename'];
        
        $inputFileType = ucwords(explode('.', $filename)[1]);
        $inputFileName = '../../uploads/excel/'.$filename;
        class MyReadFilter implements IReadFilter
        {
            public function readCell($column, $row, $worksheetName = '')
            {
                if (in_array($column, range('A', 'C'))) {
                    return true;
                }
                return false;
            }
        }
        
        $filterSubset = new MyReadFilter();
        $reader       = IOFactory::createReader($inputFileType);
        $reader->setReadFilter($filterSubset);
        $reader->setReadEmptyCells(false);

        try {
            $spreadsheet = $reader->load($inputFileName);
            $sheetData   = $spreadsheet->getActiveSheet()->toArray(
                '', //Value returned in the array entry if a cell doesn't exist / empty cell is encountered.
                true, //Should formulas be calculated for each cell?
                true, //Should formatting be applied to cell values?
                false//Should the array be indexed by actual row and column IDs (true) or by numbers counting from zero.
            );

            $excelFields = $sheetData[0];
            array_shift($sheetData);

            foreach ($sheetData as $key => $value) {
                if ($key < 100000) {
                    if (count($value) > 0) {
                        ChainPDO("UPDATE locations SET physicalCount = ?, systemCount = ?, modifiedBy = ?, lastScannedOn = NULL WHERE `code` = ?", [$value[1], $value[2], $username, $value[0]]);
                    }
                }
            }

            http_response_code(201);
            echo json_encode([
                "title" => 'Success',
                "content" => 'Location counts updated successfully!'
            ]);
        } catch(\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            echo json_encode([
                "title" => 'Processing error',
                "content" => $e->getMessage()
            ]);

            LogToDB(json_encode(
                [
                    'logtype'   => 'ERROR',
                    'operation' => 'FILE SYSTEM',
                    'service'   => basename(dirname(__FILE__)),
                    'request'   => $filename,
                    'response'  => $e->getMessage(),
                ]
            ));
        }
    }
}
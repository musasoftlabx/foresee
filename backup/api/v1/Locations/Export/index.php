<?php
require '../../functions.php';

require '/var/www/html/api/libraries/PHPSpreadSheet/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

Tokenify($_GET['token'], false);
$endpoint = 'https://foresee-technologies.com/';

    $locations = ChainPDO("SELECT *, ABS(physicalCount - systemCount) AS discrepancy FROM locations WHERE storeId = ?", [$_GET['storeId']])->fetchAll();
    $count = count($locations);

    $dataset = $locations;

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

    $rangeHeaders = 'A3:' . range('A', 'Z')[count($dataset[0])] . '3';
    $rangeDataset = 'A4:' . range('A', 'Z')[count($dataset[0])] . ($count + 3);

    $arrayData = [
        [
            '#',
            'Code',
            'Physical Count',
            'System Count',
            'Discrepancy',
            'Is Active',
            'Is Verified',
            'Created On',
            'Created By',
            'Modified On',
            'Modified By',
            'Last Scanned On',
            'Last Scanned By'
        ],
    ];

    $k = 0;
    for ($i = 0; $i < $count; $i++) {
        array_push($arrayData, [
            $dataset[$i]['id'],
            $dataset[$i]['code'],
            $dataset[$i]['physicalCount'],
            $dataset[$i]['systemCount'],
            $dataset[$i]['discrepancy'],
            $dataset[$i]['isActive'],
            $dataset[$i]['isVerified'],
            $dataset[$i]['createdOn'],
            $dataset[$i]['createdBy'],
            $dataset[$i]['modifiedOn'],
            $dataset[$i]['modifiedBy'],
            $dataset[$i]['lastScannedOn'],
            $dataset[$i]['lastScannedBy']
        ]);
    }

    $styleArrayHeaders = [
        'font'      => [
            'bold' => true,
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
        ],
        'borders'   => [
            'bottom' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                'color'       => ['argb' => '39b54a'],
            ],
        ],
        'fill'      => [
            'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
            'rotation'   => 90,
            'startColor' => [
                'argb' => '39b54a',
            ],
            'endColor'   => [
                'argb' => 'FFFFFFFF',
            ],
        ],
    ];

    $styleArrayDataset = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color'       => ['argb' => '000000'],
            ],
        ],
    ];

    $spreadsheet = new Spreadsheet();
    $drawing     = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
    $drawing->setName('Logo');
    $drawing->setDescription('Logo');
    $drawing->setPath('/var/www/html/img/logo.jpg');
    $drawing->setHeight(50);
    $drawing->setOffsetX(5);
    $drawing->setOffsetY(5);
    $drawing->setWorksheet($spreadsheet->getActiveSheet());
    $spreadsheet->getActiveSheet()->getCell('A2')->setValue("Foresee Technologies
Report: Locations under " . $_GET['store'] . "
Total: $count locations"
    );
    $spreadsheet->getActiveSheet()->getStyle('A2')->getAlignment()->setWrapText(true);
    $spreadsheet->getActiveSheet()->getStyle('A2')->getFont()->setSize(10);
    $spreadsheet->getActiveSheet()->getStyle('A2')->getFont()->setItalic(true);
    $spreadsheet->getActiveSheet()->getStyle('A2')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK);
    $spreadsheet->getActiveSheet()->getStyle('A2')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $spreadsheet->getActiveSheet()->getStyle('D3:J3')->getAlignment()->setWrapText(true);
    $spreadsheet->getActiveSheet()->fromArray($arrayData, null, 'A3');
    $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(3);
    $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(20);
    $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(8);
    $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(8);
    $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(8);
    $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(8);
    $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(8);
    $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(20);
    $spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('J')->setWidth(20);
    $spreadsheet->getActiveSheet()->getColumnDimension('K')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('L')->setWidth(20);
    $spreadsheet->getActiveSheet()->getColumnDimension('M')->setWidth(15);
    $spreadsheet->getActiveSheet()->getStyle($rangeHeaders)->applyFromArray($styleArrayHeaders);
    $spreadsheet->getActiveSheet()->getStyle($rangeDataset)->applyFromArray($styleArrayDataset);
    $spreadsheet->getActiveSheet()->mergeCells('A1:C1');
    $spreadsheet->getActiveSheet()->mergeCells('A2:D2');
    $spreadsheet->getActiveSheet()->setAutoFilter($rangeHeaders);
    $spreadsheet->getActiveSheet()->getRowDimension(1)->setRowHeight(45);
    $spreadsheet->getActiveSheet()->getRowDimension(2)->setRowHeight(60);
    $spreadsheet->getActiveSheet()->getRowDimension(3)->setRowHeight(50);
    $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri');
    $spreadsheet->getDefaultStyle()->getFont()->setSize(11);

    $writer = new Xlsx($spreadsheet);
    $writer->save($path);
    readfile($path);
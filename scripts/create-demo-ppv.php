<?php

declare(strict_types=1);

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

require dirname(__DIR__) . '/vendor/autoload.php';

$target = $argv[1] ?? null;
if (!is_string($target) || trim($target) === '') {
    fwrite(STDERR, "Usage: php scripts/create-demo-ppv.php /path/to/demo.xlsx\n");
    exit(1);
}

$book = new Spreadsheet();
$sheet = $book->getActiveSheet();
$sheet->setTitle('PPV');

$sheet->setCellValue('F11', 'LINHA');
$sheet->setCellValue('H11', 'MODELO');

$periods = [
    ['AM', 'AN', 'AO', 'ABR', 20],
    ['AP', 'AQ', 'AR', 'MAI', 20],
    ['AS', 'AT', 'AU', 'JUN', 13],
];

foreach ($periods as [$prod, $sales, $balance, $name, $days]) {
    $sheet->setCellValue($prod . '10', $name);
    $sheet->setCellValue($sales . '10', $days);
    $sheet->setCellValue($balance . '10', 'DIAS');
    $sheet->setCellValue($prod . '11', 'PROD.');
    $sheet->setCellValue($sales . '11', 'VENDAS');
    $sheet->setCellValue($balance . '11', 'SALDO');
}

$sheet->setCellValue('F12', 'THB 5.0');
$sheet->setCellValue('H12', 'K62H');
$sheet->setCellValue('AM12', 20200);
$sheet->setCellValue('AP12', 18200);
$sheet->setCellValue('AS12', 13500);

$sheet->setCellValue('F13', 'THB 5.0');
$sheet->setCellValue('H13', 'MODELO SEM PARAMETRO');
$sheet->setCellValue('AM13', 1000);
$sheet->setCellValue('AP13', 1200);
$sheet->setCellValue('AS13', 800);

$directory = dirname($target);
if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
    fwrite(STDERR, "Unable to create target directory.\n");
    exit(1);
}

(new Xlsx($book))->save($target);
fwrite(STDOUT, "Demo PPV created: {$target}\n");

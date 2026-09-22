<?php

declare(strict_types=1);

namespace Indiyoin\Tests\Unit;

use Indiyoin\Infrastructure\Spreadsheet\PhpSpreadsheetPpvDemandReader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPUnit\Framework\TestCase;

final class PpvDemandReaderTest extends TestCase
{
    public function test_detects_month_and_productive_days_when_days_are_in_adjacent_column(): void
    {
        $book = new Spreadsheet();
        $sheet = $book->getActiveSheet();
        $sheet->setTitle('PPV');

        $sheet->setCellValue('F11', 'LINHA');
        $sheet->setCellValue('H11', 'MODELO');
        $sheet->setCellValue('AM10', 'ABR');
        $sheet->setCellValue('AN10', 20);
        $sheet->setCellValue('AO10', 'DIAS');
        $sheet->setCellValue('AM11', 'PROD.');
        $sheet->setCellValue('AN11', 'VENDAS');
        $sheet->setCellValue('AO11', 'SALDO');

        $sheet->setCellValue('F12', 'THB 5.0');
        $sheet->setCellValue('H12', 'K62H');
        $sheet->setCellValue('AM12', 20200);

        $path = tempnam(sys_get_temp_dir(), 'indiyoin-ppv-');
        self::assertNotFalse($path);
        $xlsx = $path . '.xlsx';
        @unlink($path);
        (new Xlsx($book))->save($xlsx);

        try {
            $demands = (new PhpSpreadsheetPpvDemandReader())->read($xlsx);
            self::assertCount(1, $demands);
            self::assertSame('THB 5.0', $demands[0]->line);
            self::assertSame('K62H', $demands[0]->model);
            self::assertSame('ABR', $demands[0]->period);
            self::assertSame(20, $demands[0]->productiveDays);
            self::assertSame(20200.0, $demands[0]->demand);
        } finally {
            @unlink($xlsx);
        }
    }
}

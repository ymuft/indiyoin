<?php

declare(strict_types=1);

namespace Indiyoin\Infrastructure\Spreadsheet;

use Indiyoin\Contracts\PpvDemandReader;
use Indiyoin\Domain\DemandPoint;
use PhpOffice\PhpSpreadsheet\IOFactory;

final readonly class PhpSpreadsheetPpvDemandReader implements PpvDemandReader
{
    public function __construct(
        private string $preferredSheetName = 'PPV',
    ) {}

    public function read(string $filePath): array
    {
        if (!is_file($filePath)) {
            throw new \InvalidArgumentException("PPV file not found: {$filePath}");
        }

        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $workbook = $reader->load($filePath);

        $sheet = $workbook->getSheetByName($this->preferredSheetName) ?? $workbook->getActiveSheet();
        $structure = (new PpvStructureDetector())->detect($sheet);
        $lastRow = $sheet->getHighestDataRow();
        $demands = [];

        for ($row = $structure->headerRow + 1; $row <= $lastRow; $row++) {
            $line = trim((string) $sheet->getCell([$structure->lineColumn, $row])->getCalculatedValue());
            $model = trim((string) $sheet->getCell([$structure->modelColumn, $row])->getCalculatedValue());

            if ($line === '' || $model === '') {
                continue;
            }

            foreach ($structure->periods as $period) {
                $rawDemand = $sheet->getCell([$period['demand_column'], $row])->getCalculatedValue();
                if (!is_numeric($rawDemand) || (float) $rawDemand <= 0) {
                    continue;
                }

                $demands[] = new DemandPoint(
                    line: $line,
                    model: $model,
                    period: $period['period'],
                    demand: (float) $rawDemand,
                    productiveDays: $period['productive_days'],
                );
            }
        }

        return $demands;
    }
}

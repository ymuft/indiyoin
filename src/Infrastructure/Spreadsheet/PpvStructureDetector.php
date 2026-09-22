<?php

declare(strict_types=1);

namespace Indiyoin\Infrastructure\Spreadsheet;

use Indiyoin\Domain\Normalizer;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class PpvStructureDetector
{
    public function detect(Worksheet $sheet): PpvStructure
    {
        $highestRow = min($sheet->getHighestDataRow(), 100);
        $highestColumn = min(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestDataColumn()), 250);

        for ($row = 1; $row <= $highestRow; $row++) {
            $lineColumn = null;
            $modelColumn = null;

            for ($column = 1; $column <= $highestColumn; $column++) {
                $value = Normalizer::key((string) $sheet->getCell([$column, $row])->getCalculatedValue());

                if ($value === 'LINHA') {
                    $lineColumn = $column;
                }

                if (in_array($value, ['MODELO', 'MOD'], true)) {
                    $modelColumn = $column;
                }
            }

            if ($lineColumn !== null && $modelColumn !== null) {
                return new PpvStructure(
                    sheetName: $sheet->getTitle(),
                    headerRow: $row,
                    lineColumn: $lineColumn,
                    modelColumn: $modelColumn,
                    periods: $this->detectPeriods($sheet, $row, $highestColumn),
                );
            }
        }

        throw new \RuntimeException('Could not locate LINHA and MODELO headers in PPV sheet.');
    }

    /** @return list<array{period:string, demand_column:int, productive_days:int}> */
    private function detectPeriods(Worksheet $sheet, int $baseHeaderRow, int $highestColumn): array
    {
        $periods = [];

        for ($row = max(1, $baseHeaderRow - 8); $row <= $baseHeaderRow + 8; $row++) {
            for ($column = 1; $column <= $highestColumn; $column++) {
                $value = Normalizer::key((string) $sheet->getCell([$column, $row])->getCalculatedValue());
                if (!in_array($value, ['PROD.', 'PROD', 'PRODUCAO', 'PRODUÇÃO'], true)) {
                    continue;
                }

                $period = $this->findPeriodAbove($sheet, $column, $row);
                $days = $this->findProductiveDaysAbove($sheet, $column, $row);

                if ($period !== null && $days !== null) {
                    $periods[] = [
                        'period' => $period,
                        'demand_column' => $column,
                        'productive_days' => $days,
                    ];
                }
            }
        }

        if ($periods === []) {
            throw new \RuntimeException('PPV demand periods were not detected. Expected PROD. columns with month and productive days nearby.');
        }

        return $periods;
    }

    private function findPeriodAbove(Worksheet $sheet, int $column, int $row): ?string
    {
        for ($r = $row - 1; $r >= max(1, $row - 8); $r--) {
            $value = trim((string) $sheet->getCell([$column, $r])->getCalculatedValue());
            if ($value === '') {
                continue;
            }

            $normalized = Normalizer::key($value);
            if (preg_match('/^(JAN|FEV|MAR|ABR|MAI|JUN|JUL|AGO|SET|OUT|NOV|DEZ)(\/?\d{2,4})?$/u', $normalized) === 1) {
                return $normalized;
            }
        }

        return null;
    }

    private function findProductiveDaysAbove(Worksheet $sheet, int $column, int $row): ?int
    {
        // Nos PPVs reais, o cabecalho costuma ser: MES | N | DIAS, enquanto PROD.
        // fica na primeira coluna do trio. Procuramos primeiro nas colunas adjacentes
        // e depois na propria coluna para nao depender de coordenadas fixas.
        for ($r = $row - 1; $r >= max(1, $row - 8); $r--) {
            foreach ([$column + 1, $column, $column - 1, $column + 2] as $candidateColumn) {
                if ($candidateColumn < 1) {
                    continue;
                }

                $value = $sheet->getCell([$candidateColumn, $r])->getCalculatedValue();
                if (!is_numeric($value)) {
                    continue;
                }

                $days = (int) $value;
                if ($days >= 1 && $days <= 31) {
                    return $days;
                }
            }
        }

        return null;
    }
}

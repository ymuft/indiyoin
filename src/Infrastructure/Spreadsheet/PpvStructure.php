<?php

declare(strict_types=1);

namespace Indiyoin\Infrastructure\Spreadsheet;

final readonly class PpvStructure
{
    /**
     * @param list<array{period:string, demand_column:int, productive_days:int}> $periods
     */
    public function __construct(
        public string $sheetName,
        public int $headerRow,
        public int $lineColumn,
        public int $modelColumn,
        public array $periods,
    ) {}
}

<?php

declare(strict_types=1);

use Indiyoin\Application\CapacityCalculator;
use Indiyoin\Application\GenerateCapacityAnalysis;
use Indiyoin\Infrastructure\Csv\CsvTechnicalCatalog;
use Indiyoin\Infrastructure\Spreadsheet\PhpSpreadsheetPpvDemandReader;

require dirname(__DIR__) . '/vendor/autoload.php';

if ($argc < 2) {
    fwrite(STDERR, "Usage: php bin/analyze.php /path/to/ppv.xlsx\n");
    exit(1);
}

$config = require dirname(__DIR__) . '/config/indiyoin.php';
$useCase = new GenerateCapacityAnalysis(
    new PhpSpreadsheetPpvDemandReader($config['ppv_sheet_name']),
    new CsvTechnicalCatalog($config['technical_catalog']),
    new CapacityCalculator(),
);

$points = $useCase->execute($argv[1]);
$summary = ['MATCHED' => 0, 'AMBIGUOUS' => 0, 'UNRESOLVED' => 0];
foreach ($points as $point) {
    $summary[$point->technical->status]++;
}

printf("Demand points: %d\n", count($points));
printf("Matched: %d | Ambiguous: %d | Unresolved: %d\n", $summary['MATCHED'], $summary['AMBIGUOUS'], $summary['UNRESOLVED']);

foreach (array_slice($points, 0, 20) as $point) {
    printf(
        "%s | %s | %s | %.2f pcs | %s | %s h/day\n",
        $point->demand->line,
        $point->demand->model,
        $point->demand->period,
        $point->demand->demand,
        $point->technical->status,
        $point->requiredHoursDay === null ? '-' : number_format($point->requiredHoursDay, 3, '.', ''),
    );
}

<?php

declare(strict_types=1);

use App\Core\Bootstrap;
use App\Core\Env;
use App\Core\Paths;
use App\Core\Router;
use Indiyoin\Application\AnalysisSummaryBuilder;
use Indiyoin\Application\CapacityCalculator;
use Indiyoin\Application\GenerateCapacityAnalysis;
use Indiyoin\Infrastructure\Csv\CsvTechnicalCatalog;
use Indiyoin\Infrastructure\Spreadsheet\PhpSpreadsheetPpvDemandReader;
use Indiyoin\Web\Controllers\AuthController;
use Indiyoin\Web\Controllers\DashboardController;
use Indiyoin\Web\Controllers\HealthController;
use Indiyoin\Web\Controllers\PpvImportController;

require dirname(__DIR__) . '/vendor/autoload.php';

$root = dirname(__DIR__);
Bootstrap::web($root);

$catalogPath = Paths::resolve(
    Env::get('TECHNICAL_CATALOG_PATH', 'data/technical-parameters.csv') ?? 'data/technical-parameters.csv'
);
$sheetName = Env::get('PPV_SHEET_NAME', 'PPV') ?? 'PPV';
$maxUploadMb = (int) (Env::get('MAX_UPLOAD_MB', '30') ?? '30');

$analysisService = new GenerateCapacityAnalysis(
    new PhpSpreadsheetPpvDemandReader($sheetName),
    new CsvTechnicalCatalog($catalogPath),
    new CapacityCalculator(),
);

$auth = new AuthController();
$dashboard = new DashboardController(Paths::resolve('storage/analysis'));
$import = new PpvImportController(
    $analysisService,
    new AnalysisSummaryBuilder(),
    Paths::resolve('storage/imports'),
    Paths::resolve('storage/analysis'),
    $maxUploadMb,
);
$health = new HealthController();

$router = new Router();
$router->get('/', [$dashboard, 'index']);
$router->get('/login', [$auth, 'showLogin']);
$router->post('/login', [$auth, 'login']);
$router->post('/logout', [$auth, 'logout']);
$router->post('/import', [$import, 'import']);
$router->post('/analysis/clear', [$import, 'clear']);
$router->get('/health', [$health, 'show']);
$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');

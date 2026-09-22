<?php

declare(strict_types=1);

use App\Core\ConfigValidator;
use App\Core\Env;
use App\Core\Router;
use App\Security\SecurityHeaders;
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
Env::load($root . '/.env');
ConfigValidator::assertSafe();
SecurityHeaders::apply();

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.use_trans_sid', '0');
session_name(Env::get('APP_SESSION_NAME', 'indiyoin_session') ?? 'indiyoin_session');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => Env::bool('APP_SECURE_COOKIES', false),
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

$catalogPath = Env::get('TECHNICAL_CATALOG_PATH', $root . '/data/technical-parameters.csv') ?? $root . '/data/technical-parameters.csv';
if (!str_starts_with($catalogPath, '/') && preg_match('/^[A-Za-z]:[\\\\\/]/', $catalogPath) !== 1) {
    $catalogPath = $root . '/' . ltrim($catalogPath, '/\\');
}
$sheetName = Env::get('PPV_SHEET_NAME', 'PPV') ?? 'PPV';
$maxUploadMb = (int) (Env::get('MAX_UPLOAD_MB', '30') ?? '30');

$analysisService = new GenerateCapacityAnalysis(
    new PhpSpreadsheetPpvDemandReader($sheetName),
    new CsvTechnicalCatalog($catalogPath),
    new CapacityCalculator(),
);

$auth = new AuthController();
$dashboard = new DashboardController($root . '/storage/analysis');
$import = new PpvImportController(
    $analysisService,
    new AnalysisSummaryBuilder(),
    $root . '/storage/imports',
    $root . '/storage/analysis',
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

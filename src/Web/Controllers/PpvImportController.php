<?php

declare(strict_types=1);

namespace Indiyoin\Web\Controllers;

use App\Audit\AuditLogger;
use App\Core\Response;
use App\Security\Auth;
use App\Security\Csrf;
use Indiyoin\Application\AnalysisSummaryBuilder;
use Indiyoin\Application\GenerateCapacityAnalysis;
use Throwable;

final readonly class PpvImportController
{
    public function __construct(
        private GenerateCapacityAnalysis $analysis,
        private AnalysisSummaryBuilder $summaryBuilder,
        private string $importDirectory,
        private string $analysisDirectory,
        private int $maxUploadMb = 30,
    ) {}

    public function import(): void
    {
        if (!Auth::check()) {
            Response::redirect('/login');
        }

        $csrf = $_POST['_csrf'] ?? null;
        if (!Csrf::validate(is_string($csrf) ? $csrf : null)) {
            http_response_code(419);
            echo 'Invalid CSRF token';
            return;
        }

        $file = $_FILES['ppv'] ?? null;
        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $this->fail('Selecione um arquivo PPV .xlsx válido.');
        }

        $name = basename((string) ($file['name'] ?? 'ppv.xlsx'));
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $size = (int) ($file['size'] ?? 0);
        $tmp = (string) ($file['tmp_name'] ?? '');

        if ($extension !== 'xlsx') {
            $this->fail('Nesta etapa o Indiyoin aceita somente arquivos .xlsx.');
        }
        if ($size <= 0 || $size > $this->maxUploadMb * 1024 * 1024) {
            $this->fail('O PPV excede o limite de upload ou está vazio.');
        }
        if (!is_uploaded_file($tmp)) {
            $this->fail('O upload não pôde ser validado pelo servidor.');
        }

        $this->ensureDirectory($this->importDirectory);
        $this->ensureDirectory($this->analysisDirectory);

        $id = bin2hex(random_bytes(16));
        $target = $this->importDirectory . '/' . $id . '.xlsx';
        if (!move_uploaded_file($tmp, $target)) {
            $this->fail('Não foi possível armazenar o PPV enviado.');
        }

        try {
            $points = $this->analysis->execute($target);
            $summary = $this->summaryBuilder->build($points);
            $payload = [
                'id' => $id,
                'source_name' => $name,
                'imported_at' => gmdate('c'),
                'summary' => $summary,
            ];

            $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            file_put_contents($this->analysisDirectory . '/' . $id . '.json', $json, LOCK_EX);
            $_SESSION['analysis_id'] = $id;

            $user = Auth::user();
            AuditLogger::record(isset($user['id']) ? (int) $user['id'] : null, 'ppv.import', [
                'filename' => $name,
                'points' => $summary['totals']['points'],
                'matched' => $summary['totals']['matched'],
                'ambiguous' => $summary['totals']['ambiguous'],
                'unresolved' => $summary['totals']['unresolved'],
            ]);
        } catch (Throwable $exception) {
            @unlink($target);
            error_log('Indiyoin PPV import failed: ' . $exception->getMessage());
            $this->fail('Não foi possível interpretar este PPV: ' . $exception->getMessage());
        }

        Response::redirect('/');
    }

    public function clear(): void
    {
        if (!Auth::check()) {
            Response::redirect('/login');
        }
        $csrf = $_POST['_csrf'] ?? null;
        if (!Csrf::validate(is_string($csrf) ? $csrf : null)) {
            http_response_code(419);
            echo 'Invalid CSRF token';
            return;
        }

        unset($_SESSION['analysis_id']);
        Response::redirect('/');
    }

    private function fail(string $message): never
    {
        $_SESSION['flash_error'] = $message;
        Response::redirect('/');
    }

    private function ensureDirectory(string $path): void
    {
        if (!is_dir($path) && !mkdir($path, 0775, true) && !is_dir($path)) {
            throw new \RuntimeException('Unable to create storage directory.');
        }
    }
}

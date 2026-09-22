<?php

declare(strict_types=1);

namespace Indiyoin\Web\Controllers;

use App\Core\Response;
use App\Security\Auth;
use App\Security\Csrf;
use Indiyoin\Web\View;

final readonly class DashboardController
{
    public function __construct(private string $analysisDirectory) {}

    public function index(): void
    {
        if (!Auth::check()) {
            Response::redirect('/login');
        }

        $analysis = $this->loadCurrentAnalysis();
        $selectedLine = trim((string) ($_GET['line'] ?? ''));
        if ($analysis !== null && $selectedLine === '') {
            $selectedLine = (string) ($analysis['summary']['lines'][0]['name'] ?? '');
        }

        View::render('dashboard', [
            'user' => Auth::user(),
            'csrf' => Csrf::token(),
            'analysis' => $analysis,
            'selectedLine' => $selectedLine,
            'flashError' => $_SESSION['flash_error'] ?? null,
        ]);
        unset($_SESSION['flash_error']);
    }

    private function loadCurrentAnalysis(): ?array
    {
        $id = $_SESSION['analysis_id'] ?? null;
        if (!is_string($id) || preg_match('/\A[a-f0-9]{32}\z/', $id) !== 1) {
            return null;
        }

        $path = $this->analysisDirectory . '/' . $id . '.json';
        if (!is_file($path)) {
            unset($_SESSION['analysis_id']);
            return null;
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        return is_array($decoded) ? $decoded : null;
    }
}

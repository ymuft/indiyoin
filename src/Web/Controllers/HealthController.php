<?php

declare(strict_types=1);

namespace Indiyoin\Web\Controllers;

use App\Core\Database;
use App\Core\Response;
use Throwable;

final class HealthController
{
    public function show(): never
    {
        try {
            Database::connection()->query('SELECT 1');
            Response::json(['status' => 'ok', 'app' => 'indiyoin', 'foundation' => 'appfoundry']);
        } catch (Throwable $exception) {
            Response::json(['status' => 'error'], 503);
        }
    }
}

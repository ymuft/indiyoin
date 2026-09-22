<?php

declare(strict_types=1);

namespace Indiyoin\Contracts;

use Indiyoin\Domain\TechnicalResolution;

interface TechnicalCatalog
{
    public function resolve(string $line, string $model): TechnicalResolution;
}

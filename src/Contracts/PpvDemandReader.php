<?php

declare(strict_types=1);

namespace Indiyoin\Contracts;

use Indiyoin\Domain\DemandPoint;

interface PpvDemandReader
{
    /** @return list<DemandPoint> */
    public function read(string $filePath): array;
}

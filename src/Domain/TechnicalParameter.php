<?php

declare(strict_types=1);

namespace Indiyoin\Domain;

final readonly class TechnicalParameter
{
    public function __construct(
        public string $line,
        public string $model,
        public float $cycleTimeSeconds,
        public float $oee,
        public string $status = 'OK',
        public ?int $sourceRow = null,
    ) {
        if ($cycleTimeSeconds <= 0) {
            throw new \InvalidArgumentException('Cycle time must be greater than zero.');
        }

        if ($oee <= 0 || $oee > 1) {
            throw new \InvalidArgumentException('OEE must be a decimal between 0 and 1.');
        }
    }
}

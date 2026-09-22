<?php

declare(strict_types=1);

namespace Indiyoin\Domain;

final readonly class DemandPoint
{
    public function __construct(
        public string $line,
        public string $model,
        public string $period,
        public float $demand,
        public int $productiveDays,
    ) {
        if ($demand < 0) {
            throw new \InvalidArgumentException('Demand cannot be negative.');
        }

        if ($productiveDays <= 0) {
            throw new \InvalidArgumentException('Productive days must be greater than zero.');
        }
    }
}

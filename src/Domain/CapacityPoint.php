<?php

declare(strict_types=1);

namespace Indiyoin\Domain;

final readonly class CapacityPoint
{
    public function __construct(
        public DemandPoint $demand,
        public TechnicalResolution $technical,
        public ?float $requiredHoursMonth,
        public ?float $requiredHoursDay,
    ) {}
}

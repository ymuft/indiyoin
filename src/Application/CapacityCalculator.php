<?php

declare(strict_types=1);

namespace Indiyoin\Application;

use Indiyoin\Domain\DemandPoint;
use Indiyoin\Domain\TechnicalParameter;

final class CapacityCalculator
{
    /** @return array{required_hours_month: float, required_hours_day: float} */
    public function calculate(DemandPoint $demand, TechnicalParameter $technical): array
    {
        $hoursMonth = ($demand->demand * $technical->cycleTimeSeconds)
            / (3600 * $technical->oee);

        return [
            'required_hours_month' => $hoursMonth,
            'required_hours_day' => $hoursMonth / $demand->productiveDays,
        ];
    }
}

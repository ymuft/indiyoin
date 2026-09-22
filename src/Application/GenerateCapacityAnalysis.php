<?php

declare(strict_types=1);

namespace Indiyoin\Application;

use Indiyoin\Contracts\PpvDemandReader;
use Indiyoin\Contracts\TechnicalCatalog;
use Indiyoin\Domain\CapacityPoint;

final readonly class GenerateCapacityAnalysis
{
    public function __construct(
        private PpvDemandReader $ppvReader,
        private TechnicalCatalog $technicalCatalog,
        private CapacityCalculator $calculator,
    ) {}

    /** @return list<CapacityPoint> */
    public function execute(string $ppvPath): array
    {
        $result = [];

        foreach ($this->ppvReader->read($ppvPath) as $demand) {
            $technical = $this->technicalCatalog->resolve($demand->line, $demand->model);
            $parameter = $technical->parameter();

            if ($parameter === null) {
                $result[] = new CapacityPoint($demand, $technical, null, null);
                continue;
            }

            $calculated = $this->calculator->calculate($demand, $parameter);
            $result[] = new CapacityPoint(
                $demand,
                $technical,
                $calculated['required_hours_month'],
                $calculated['required_hours_day'],
            );
        }

        return $result;
    }
}

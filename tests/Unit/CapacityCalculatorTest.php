<?php

declare(strict_types=1);

namespace Indiyoin\Tests\Unit;

use Indiyoin\Application\CapacityCalculator;
use Indiyoin\Domain\DemandPoint;
use Indiyoin\Domain\TechnicalParameter;
use PHPUnit\Framework\TestCase;

final class CapacityCalculatorTest extends TestCase
{
    public function test_calculates_required_hours_per_month_and_day(): void
    {
        $demand = new DemandPoint('THB 5.0', 'K62H', 'ABR', 20200, 20);
        $technical = new TechnicalParameter('THB 5.0', 'K62H', 7.3, 0.8);

        $result = (new CapacityCalculator())->calculate($demand, $technical);

        self::assertEqualsWithDelta(51.2013889, $result['required_hours_month'], 0.0001);
        self::assertEqualsWithDelta(2.5600694, $result['required_hours_day'], 0.0001);
    }
}

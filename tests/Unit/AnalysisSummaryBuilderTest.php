<?php

declare(strict_types=1);

namespace Indiyoin\Tests\Unit;

use Indiyoin\Application\AnalysisSummaryBuilder;
use Indiyoin\Domain\CapacityPoint;
use Indiyoin\Domain\DemandPoint;
use Indiyoin\Domain\TechnicalParameter;
use Indiyoin\Domain\TechnicalResolution;
use PHPUnit\Framework\TestCase;

final class AnalysisSummaryBuilderTest extends TestCase
{
    public function test_aggregates_line_capacity_and_preserves_unresolved_items(): void
    {
        $parameter = new TechnicalParameter('THB 5.0', 'K62H', 7.3, 0.8);
        $matched = new CapacityPoint(
            new DemandPoint('THB 5.0', 'K62H', 'ABR', 20200, 20),
            TechnicalResolution::matched($parameter),
            51.2013889,
            2.5600694,
        );
        $unresolved = new CapacityPoint(
            new DemandPoint('THB 5.0', 'NOVO', 'ABR', 1000, 20),
            TechnicalResolution::unresolved(),
            null,
            null,
        );

        $summary = (new AnalysisSummaryBuilder())->build([$matched, $unresolved]);

        self::assertSame(2, $summary['totals']['points']);
        self::assertSame(1, $summary['totals']['matched']);
        self::assertSame(1, $summary['totals']['unresolved']);
        self::assertEqualsWithDelta(2.5600694, $summary['lines'][0]['peak_hours_day'], 0.0001);
        self::assertSame('NOVO', $summary['issues'][0]['model']);
    }
}

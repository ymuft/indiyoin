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
    public function test_aggregates_line_capacity_and_exposes_partial_coverage(): void
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
        self::assertSame(1, $summary['totals']['issues']);
        self::assertSame('THB 5.0', $summary['overview']['peak_line']);
        self::assertSame('ABR', $summary['overview']['peak_period']);
        self::assertEqualsWithDelta(2.5600694, $summary['lines'][0]['peak_hours_day'], 0.0001);
        self::assertEqualsWithDelta(20200 / 21200 * 100, $summary['totals']['coverage_pct'], 0.0001);
        self::assertEqualsWithDelta(20200 / 21200 * 100, $summary['lines'][0]['periods'][0]['coverage_pct'], 0.0001);
        self::assertSame('NOVO', $summary['issues'][0]['model']);
        self::assertSame(['ABR'], $summary['issues'][0]['periods']);
        self::assertSame(1000.0, $summary['issues'][0]['demand_affected']);
    }

    public function test_aggregates_duplicate_model_rows_in_same_period(): void
    {
        $parameter = new TechnicalParameter('INP', 'KVSP', 16.5, 0.8);

        $first = new CapacityPoint(
            new DemandPoint('INP', 'KVSP', 'MAI', 1000, 20),
            TechnicalResolution::matched($parameter),
            5.7291667,
            0.2864583,
        );
        $second = new CapacityPoint(
            new DemandPoint('INP', 'KVSP', 'MAI', 500, 20),
            TechnicalResolution::matched($parameter),
            2.8645833,
            0.1432292,
        );

        $summary = (new AnalysisSummaryBuilder())->build([$first, $second]);
        $model = $summary['lines'][0]['models'][0];

        self::assertSame(1500.0, $model['demand']);
        self::assertEqualsWithDelta(8.59375, $model['hours_month'], 0.0001);
        self::assertEqualsWithDelta(0.4296875, $model['hours_day'], 0.0001);
        self::assertSame(1, $summary['lines'][0]['matched_model_count']);
        self::assertSame(100.0, $summary['lines'][0]['technical_coverage_pct']);
    }
}

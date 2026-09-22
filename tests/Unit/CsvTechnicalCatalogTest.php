<?php

declare(strict_types=1);

namespace Indiyoin\Tests\Unit;

use Indiyoin\Infrastructure\Csv\CsvTechnicalCatalog;
use PHPUnit\Framework\TestCase;

final class CsvTechnicalCatalogTest extends TestCase
{
    public function test_resolves_unique_parameter(): void
    {
        $catalog = new CsvTechnicalCatalog(dirname(__DIR__, 2) . '/data/technical-parameters.csv');
        $resolution = $catalog->resolve('THB 3.5', 'K1ZH');

        self::assertSame('MATCHED', $resolution->status);
        self::assertSame(32.0, $resolution->parameter()?->cycleTimeSeconds);
        self::assertSame(0.8, $resolution->parameter()?->oee);
    }

    public function test_keeps_known_conflict_ambiguous(): void
    {
        $catalog = new CsvTechnicalCatalog(dirname(__DIR__, 2) . '/data/technical-parameters.csv');
        $resolution = $catalog->resolve('THB 3.5', 'K31A');

        self::assertSame('AMBIGUOUS', $resolution->status);
        self::assertGreaterThan(1, count($resolution->candidates));
    }

    public function test_unknown_model_is_unresolved(): void
    {
        $catalog = new CsvTechnicalCatalog(dirname(__DIR__, 2) . '/data/technical-parameters.csv');
        self::assertSame('UNRESOLVED', $catalog->resolve('INP', 'NAO-EXISTE')->status);
    }
}

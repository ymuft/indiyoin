<?php

declare(strict_types=1);

namespace Indiyoin\Infrastructure\Csv;

use Indiyoin\Contracts\TechnicalCatalog;
use Indiyoin\Domain\Normalizer;
use Indiyoin\Domain\TechnicalParameter;
use Indiyoin\Domain\TechnicalResolution;

final class CsvTechnicalCatalog implements TechnicalCatalog
{
    /** @var array<string, list<TechnicalParameter>> */
    private array $index = [];

    public function __construct(string $csvPath)
    {
        $this->load($csvPath);
    }

    public function resolve(string $line, string $model): TechnicalResolution
    {
        $candidates = $this->index[Normalizer::technicalKey($line, $model)] ?? [];

        if ($candidates === []) {
            return TechnicalResolution::unresolved();
        }

        $unique = [];
        foreach ($candidates as $candidate) {
            $signature = sprintf('%.8F|%.8F', $candidate->cycleTimeSeconds, $candidate->oee);
            $unique[$signature] ??= $candidate;
        }

        $uniqueCandidates = array_values($unique);
        if (count($uniqueCandidates) === 1 && strtoupper($uniqueCandidates[0]->status) !== 'AMBIGUOUS') {
            return TechnicalResolution::matched($uniqueCandidates[0]);
        }

        if (count($uniqueCandidates) === 1) {
            return TechnicalResolution::ambiguous($uniqueCandidates);
        }

        return TechnicalResolution::ambiguous($uniqueCandidates);
    }

    private function load(string $csvPath): void
    {
        $handle = fopen($csvPath, 'rb');
        if ($handle === false) {
            throw new \RuntimeException("Unable to open technical catalog: {$csvPath}");
        }

        try {
            $header = fgetcsv($handle, separator: ';', escape: '');
            if ($header === false) {
                throw new \RuntimeException('Technical catalog is empty.');
            }

            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header[0]) ?? (string) $header[0];
            $columns = array_flip($header);

            foreach (['source_row', 'line', 'model', 'ct_seconds', 'oee_decimal', 'mapping_status'] as $required) {
                if (!isset($columns[$required])) {
                    throw new \RuntimeException("Missing CSV column: {$required}");
                }
            }

            while (($row = fgetcsv($handle, separator: ';', escape: '')) !== false) {
                if ($row === [null] || $row === []) {
                    continue;
                }

                $parameter = new TechnicalParameter(
                    line: trim((string) $row[$columns['line']]),
                    model: trim((string) $row[$columns['model']]),
                    cycleTimeSeconds: (float) $row[$columns['ct_seconds']],
                    oee: (float) $row[$columns['oee_decimal']],
                    status: trim((string) $row[$columns['mapping_status']]),
                    sourceRow: (int) $row[$columns['source_row']],
                );

                $this->index[Normalizer::technicalKey($parameter->line, $parameter->model)][] = $parameter;
            }
        } finally {
            fclose($handle);
        }
    }
}

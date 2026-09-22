<?php

declare(strict_types=1);

namespace Indiyoin\Application;

use Indiyoin\Domain\CapacityPoint;

final class AnalysisSummaryBuilder
{
    /** @param list<CapacityPoint> $points */
    public function build(array $points): array
    {
        $totals = ['points' => count($points), 'matched' => 0, 'ambiguous' => 0, 'unresolved' => 0];
        $periodOrder = [];
        $lines = [];
        $issues = [];

        foreach ($points as $point) {
            $status = strtolower($point->technical->status);
            if (isset($totals[$status])) {
                $totals[$status]++;
            }

            $period = $point->demand->period;
            if (!in_array($period, $periodOrder, true)) {
                $periodOrder[] = $period;
            }

            $line = $point->demand->line;
            $lines[$line] ??= [
                'name' => $line,
                'periods' => [],
                'models' => [],
                'peak_hours_day' => 0.0,
            ];

            $lines[$line]['periods'][$period] ??= [
                'period' => $period,
                'productive_days' => $point->demand->productiveDays,
                'demand' => 0.0,
                'hours_month' => 0.0,
                'hours_day' => 0.0,
                'matched_models' => 0,
            ];
            $lines[$line]['periods'][$period]['demand'] += $point->demand->demand;

            if ($point->technical->status === 'MATCHED' && $point->requiredHoursDay !== null && $point->requiredHoursMonth !== null) {
                $lines[$line]['periods'][$period]['hours_month'] += $point->requiredHoursMonth;
                $lines[$line]['periods'][$period]['hours_day'] += $point->requiredHoursDay;
                $lines[$line]['periods'][$period]['matched_models']++;

                $parameter = $point->technical->parameter();
                $key = $point->demand->model . '|' . $period;
                $lines[$line]['models'][$key] = [
                    'model' => $point->demand->model,
                    'period' => $period,
                    'demand' => $point->demand->demand,
                    'productive_days' => $point->demand->productiveDays,
                    'ct' => $parameter?->cycleTimeSeconds,
                    'oee' => $parameter?->oee,
                    'hours_month' => $point->requiredHoursMonth,
                    'hours_day' => $point->requiredHoursDay,
                ];
            } else {
                $issueKey = $line . '|' . $point->demand->model . '|' . $point->technical->status;
                if (!isset($issues[$issueKey])) {
                    $issues[$issueKey] = [
                        'line' => $line,
                        'model' => $point->demand->model,
                        'status' => $point->technical->status,
                        'candidate_count' => count($point->technical->candidates),
                    ];
                }
            }
        }

        foreach ($lines as &$line) {
            $ordered = [];
            foreach ($periodOrder as $period) {
                if (isset($line['periods'][$period])) {
                    $ordered[] = $line['periods'][$period];
                    $line['peak_hours_day'] = max($line['peak_hours_day'], $line['periods'][$period]['hours_day']);
                }
            }
            $line['periods'] = $ordered;
            $line['models'] = array_values($line['models']);
        }
        unset($line);

        uasort($lines, static fn (array $a, array $b): int => $b['peak_hours_day'] <=> $a['peak_hours_day']);

        return [
            'totals' => $totals,
            'periods' => $periodOrder,
            'lines' => array_values($lines),
            'issues' => array_values($issues),
        ];
    }
}

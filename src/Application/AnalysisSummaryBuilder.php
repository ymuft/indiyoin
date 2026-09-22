<?php

declare(strict_types=1);

namespace Indiyoin\Application;

use Indiyoin\Domain\CapacityPoint;

final class AnalysisSummaryBuilder
{
    /** @param list<CapacityPoint> $points */
    public function build(array $points): array
    {
        $totals = [
            'points' => count($points),
            'matched' => 0,
            'ambiguous' => 0,
            'unresolved' => 0,
            'demand_total' => 0.0,
            'demand_matched' => 0.0,
            'demand_pending' => 0.0,
            'coverage_pct' => 0.0,
            'lines' => 0,
            'issues' => 0,
        ];
        $periodOrder = [];
        $lines = [];
        $issues = [];

        foreach ($points as $point) {
            $status = strtolower($point->technical->status);
            if (isset($totals[$status])) {
                $totals[$status]++;
            }

            $demand = $point->demand->demand;
            $totals['demand_total'] += $demand;

            $period = $point->demand->period;
            if (!in_array($period, $periodOrder, true)) {
                $periodOrder[] = $period;
            }

            $lineName = $point->demand->line;
            $lines[$lineName] ??= [
                'name' => $lineName,
                'periods' => [],
                'models' => [],
                'peak_hours_day' => 0.0,
                'peak_period' => null,
                'demand_total' => 0.0,
                'demand_matched' => 0.0,
                'technical_coverage_pct' => 0.0,
                'matched_points' => 0,
                'pending_points' => 0,
                'matched_model_count' => 0,
            ];

            $line =& $lines[$lineName];
            $line['demand_total'] += $demand;
            $line['periods'][$period] ??= [
                'period' => $period,
                'productive_days' => $point->demand->productiveDays,
                'demand_total' => 0.0,
                'demand_matched' => 0.0,
                'demand_pending' => 0.0,
                'coverage_pct' => 0.0,
                'hours_month' => 0.0,
                'hours_day' => 0.0,
                'matched_points' => 0,
                'pending_points' => 0,
            ];
            $line['periods'][$period]['demand_total'] += $demand;

            if ($point->technical->status === 'MATCHED' && $point->requiredHoursDay !== null && $point->requiredHoursMonth !== null) {
                $totals['demand_matched'] += $demand;
                $line['demand_matched'] += $demand;
                $line['matched_points']++;
                $line['periods'][$period]['demand_matched'] += $demand;
                $line['periods'][$period]['matched_points']++;
                $line['periods'][$period]['hours_month'] += $point->requiredHoursMonth;
                $line['periods'][$period]['hours_day'] += $point->requiredHoursDay;

                $parameter = $point->technical->parameter();
                $key = $point->demand->model . '|' . $period;
                $line['models'][$key] ??= [
                    'model' => $point->demand->model,
                    'period' => $period,
                    'demand' => 0.0,
                    'productive_days' => $point->demand->productiveDays,
                    'ct' => $parameter?->cycleTimeSeconds,
                    'oee' => $parameter?->oee,
                    'hours_month' => 0.0,
                    'hours_day' => 0.0,
                ];
                $line['models'][$key]['demand'] += $demand;
                $line['models'][$key]['hours_month'] += $point->requiredHoursMonth;
                $line['models'][$key]['hours_day'] += $point->requiredHoursDay;
            } else {
                $line['pending_points']++;
                $line['periods'][$period]['pending_points']++;
                $line['periods'][$period]['demand_pending'] += $demand;

                $issueKey = $lineName . '|' . $point->demand->model . '|' . $point->technical->status;
                $issues[$issueKey] ??= [
                    'line' => $lineName,
                    'model' => $point->demand->model,
                    'status' => $point->technical->status,
                    'candidate_count' => count($point->technical->candidates),
                    'demand_affected' => 0.0,
                    'periods' => [],
                ];
                $issues[$issueKey]['demand_affected'] += $demand;
                if (!in_array($period, $issues[$issueKey]['periods'], true)) {
                    $issues[$issueKey]['periods'][] = $period;
                }
                $issues[$issueKey]['candidate_count'] = max(
                    $issues[$issueKey]['candidate_count'],
                    count($point->technical->candidates)
                );
            }

            unset($line);
        }

        foreach ($lines as &$line) {
            $orderedPeriods = [];
            foreach ($periodOrder as $period) {
                if (!isset($line['periods'][$period])) {
                    continue;
                }

                $periodData = $line['periods'][$period];
                $periodData['coverage_pct'] = $periodData['demand_total'] > 0
                    ? 100 * $periodData['demand_matched'] / $periodData['demand_total']
                    : 100.0;

                if ($periodData['hours_day'] > $line['peak_hours_day']) {
                    $line['peak_hours_day'] = $periodData['hours_day'];
                    $line['peak_period'] = $period;
                }

                $orderedPeriods[] = $periodData;
            }

            $line['periods'] = $orderedPeriods;
            $line['technical_coverage_pct'] = $line['demand_total'] > 0
                ? 100 * $line['demand_matched'] / $line['demand_total']
                : 100.0;

            $line['models'] = array_values($line['models']);
            $line['matched_model_count'] = count(array_unique(array_column($line['models'], 'model')));
            usort(
                $line['models'],
                static fn (array $a, array $b): int =>
                    array_search($a['period'], $periodOrder, true) <=> array_search($b['period'], $periodOrder, true)
                    ?: ($b['hours_day'] <=> $a['hours_day'])
            );
        }
        unset($line);

        $totals['demand_pending'] = max(0.0, $totals['demand_total'] - $totals['demand_matched']);
        $totals['coverage_pct'] = $totals['demand_total'] > 0
            ? 100 * $totals['demand_matched'] / $totals['demand_total']
            : 100.0;

        uasort($lines, static fn (array $a, array $b): int => $b['peak_hours_day'] <=> $a['peak_hours_day']);
        uasort($issues, static fn (array $a, array $b): int => $b['demand_affected'] <=> $a['demand_affected']);

        $lineValues = array_values($lines);
        $issueValues = array_values($issues);
        $totals['lines'] = count($lineValues);
        $totals['issues'] = count($issueValues);

        $peakLine = $lineValues[0] ?? null;

        return [
            'totals' => $totals,
            'overview' => [
                'peak_line' => $peakLine['name'] ?? null,
                'peak_period' => $peakLine['peak_period'] ?? null,
                'peak_hours_day' => $peakLine['peak_hours_day'] ?? 0.0,
            ],
            'periods' => $periodOrder,
            'lines' => $lineValues,
            'issues' => $issueValues,
        ];
    }
}

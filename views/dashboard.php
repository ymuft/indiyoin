<?php
$pageTitle = 'Capacidade · Indiyoin';
$headerTitle = $analysis ? 'Visão de capacidade' : 'Importar PPV';
$summary = $analysis['summary'] ?? null;
$lineData = null;
if (is_array($summary)) {
    foreach ($summary['lines'] as $candidate) {
        if ($candidate['name'] === $selectedLine) {
            $lineData = $candidate;
            break;
        }
    }
}

$coverage = is_array($summary) ? (float)($summary['totals']['coverage_pct'] ?? 0) : 0.0;
$overview = is_array($summary) ? ($summary['overview'] ?? []) : [];
?>
<section class="content-area">
    <?php if (!empty($flashError)): ?>
        <div class="alert alert-error page-alert">
            <strong>Importação não concluída.</strong>
            <?= htmlspecialchars((string)$flashError, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if (!$analysis): ?>
        <div class="welcome-grid">
            <div class="intro-card">
                <span class="status-chip">PPV → Capacity</span>
                <h2>Transforme demanda produtiva em necessidade de capacidade.</h2>
                <p>O PPV fornece linha, modelo, demanda e dias produtivos. O Indiyoin cruza esses dados com o catálogo técnico de CT/OEE e calcula a necessidade por modelo, mês e linha.</p>
                <div class="flow-row">
                    <span>PPV.xlsx</span><i>→</i><span>Demanda</span><i>→</i><span>CT/OEE</span><i>→</i><span>h/dia</span>
                </div>
            </div>

            <form class="upload-card" method="post" action="/import" enctype="multipart/form-data" id="ppv-upload-form">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string)$csrf, ENT_QUOTES, 'UTF-8') ?>">
                <label class="dropzone" id="ppv-dropzone" for="ppv-file">
                    <span class="upload-icon">↑</span>
                    <strong>Selecione ou arraste o PPV</strong>
                    <span>Arquivo Excel .xlsx · até 30 MB</span>
                    <input id="ppv-file" name="ppv" type="file" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                </label>
                <div class="selected-file" id="selected-file">Nenhum arquivo selecionado</div>
                <button class="primary-button full" type="submit" id="analyze-button">Analisar PPV</button>
            </form>
        </div>

        <div class="principle-card">
            <div><span class="number">01</span><strong>Demanda é entrada</strong><p>O PPV informa o que precisa ser produzido e em quais dias.</p></div>
            <div><span class="number">02</span><strong>Parâmetro é conhecimento</strong><p>CT e OEE vêm do catálogo técnico; conflito não é resolvido por aproximação.</p></div>
            <div><span class="number">03</span><strong>Capacity é resultado</strong><p>O motor converte a demanda em horas/mês e horas/dia.</p></div>
        </div>
    <?php else: ?>
        <div class="analysis-toolbar">
            <div>
                <span class="file-label">PPV ATUAL</span>
                <strong><?= htmlspecialchars((string)$analysis['source_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                <small>Importado em <?= htmlspecialchars(date('d/m/Y H:i', strtotime((string)$analysis['imported_at'])), ENT_QUOTES, 'UTF-8') ?></small>
            </div>
            <form method="post" action="/analysis/clear">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string)$csrf, ENT_QUOTES, 'UTF-8') ?>">
                <button class="secondary-button" type="submit">Carregar outro PPV</button>
            </form>
        </div>

        <?php if ($coverage < 99.95): ?>
            <div class="analysis-notice">
                <div>
                    <strong>Leitura parcial do PPV</strong>
                    <span><?= number_format($coverage, 1, ',', '.') ?>% da demanda possui CT/OEE validado. As horas exibidas não incluem a demanda pendente.</span>
                </div>
                <a href="#technical-issues">Revisar pendências</a>
            </div>
        <?php endif; ?>

        <div class="metric-grid executive">
            <article class="metric-card">
                <span>Linhas analisadas</span>
                <strong><?= number_format((int)$summary['totals']['lines'], 0, ',', '.') ?></strong>
                <small>linhas com demanda produtiva</small>
            </article>
            <article class="metric-card <?= $coverage >= 99.95 ? 'success' : 'warning' ?>">
                <span>Cobertura da demanda</span>
                <strong><?= number_format($coverage, 1, ',', '.') ?>%</strong>
                <small><?= number_format((float)$summary['totals']['demand_matched'], 0, ',', '.') ?> peças calculadas</small>
            </article>
            <article class="metric-card">
                <span>Maior carga calculada</span>
                <strong><?= number_format((float)($overview['peak_hours_day'] ?? 0), 2, ',', '.') ?> h/d</strong>
                <small><?= htmlspecialchars((string)($overview['peak_line'] ?? '-'), ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string)($overview['peak_period'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></small>
            </article>
            <article class="metric-card <?= (int)$summary['totals']['issues'] > 0 ? 'danger' : 'success' ?>">
                <span>Pendências técnicas</span>
                <strong><?= number_format((int)$summary['totals']['issues'], 0, ',', '.') ?></strong>
                <small><?= number_format((float)$summary['totals']['demand_pending'], 0, ',', '.') ?> peças afetadas</small>
            </article>
        </div>

        <div class="resolution-strip">
            <span><i class="dot ok"></i><strong><?= (int)$summary['totals']['matched'] ?></strong> pontos reconhecidos</span>
            <span><i class="dot warn"></i><strong><?= (int)$summary['totals']['ambiguous'] ?></strong> ambíguos</span>
            <span><i class="dot danger"></i><strong><?= (int)$summary['totals']['unresolved'] ?></strong> não resolvidos</span>
        </div>

        <div class="line-tabs" aria-label="Linhas produtivas">
            <?php foreach ($summary['lines'] as $line): ?>
                <a class="line-tab <?= $line['name'] === $selectedLine ? 'active' : '' ?>" href="/?line=<?= rawurlencode((string)$line['name']) ?>">
                    <span><?= htmlspecialchars((string)$line['name'], ENT_QUOTES, 'UTF-8') ?></span>
                    <small>pico <?= number_format((float)$line['peak_hours_day'], 2, ',', '.') ?> h/d · <?= number_format((float)$line['technical_coverage_pct'], 0, ',', '.') ?>%</small>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if ($lineData): ?>
            <?php
                $chartPayload = array_map(
                    static fn(array $p): array => [
                        'period' => $p['period'],
                        'hours' => round((float)$p['hours_day'], 4),
                        'coverage' => round((float)$p['coverage_pct'], 2),
                        'demand' => round((float)$p['demand_total'], 2),
                    ],
                    $lineData['periods']
                );
                $peak = (float)$lineData['peak_hours_day'];
                $lineCoverage = (float)$lineData['technical_coverage_pct'];
                $turnState = $peak > 21.5833 ? 'Acima de 3 turnos' : ($peak > 16.4167 ? 'Até 3 turnos' : ($peak > 8.75 ? 'Até 2 turnos' : 'Até 1 turno'));
                $defaultPeriod = (string)($lineData['peak_period'] ?? ($lineData['periods'][0]['period'] ?? ''));
            ?>

            <div class="dashboard-grid main-grid">
                <article class="chart-card">
                    <div class="card-head">
                        <div>
                            <span class="file-label">NECESSIDADE DE CAPACIDADE</span>
                            <h2><?= htmlspecialchars((string)$lineData['name'], ENT_QUOTES, 'UTF-8') ?></h2>
                            <p>Clique em um mês para abrir a composição por modelo.</p>
                        </div>
                        <div class="peak-badge">
                            <span>Pico calculado</span>
                            <strong><?= number_format($peak, 2, ',', '.') ?> h/d</strong>
                            <small><?= htmlspecialchars($turnState, ENT_QUOTES, 'UTF-8') ?><?= $lineCoverage < 99.95 ? ' · parcial' : '' ?></small>
                        </div>
                    </div>

                    <div
                        class="capacity-chart"
                        data-series="<?= htmlspecialchars(json_encode($chartPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?>"
                        data-default-period="<?= htmlspecialchars($defaultPeriod, ENT_QUOTES, 'UTF-8') ?>"
                        aria-label="Gráfico mensal de horas necessárias por dia"
                    ></div>

                    <div class="legend">
                        <span><i class="legend-line one"></i>1 turno · 8,75h</span>
                        <span><i class="legend-line two"></i>2 turnos · 16,42h</span>
                        <span><i class="legend-line three"></i>3 turnos · 21,58h</span>
                    </div>
                </article>

                <aside class="line-health-card">
                    <span class="file-label">CONFIANÇA DO CÁLCULO</span>
                    <div class="coverage-ring" data-value="<?= number_format($lineCoverage, 1, '.', '') ?>">
                        <strong><?= number_format($lineCoverage, 1, ',', '.') ?>%</strong>
                        <span>da demanda</span>
                    </div>
                    <div class="mini-stat"><span>Modelos calculados</span><strong><?= (int)$lineData['matched_model_count'] ?></strong></div>
                    <div class="mini-stat"><span>Demanda total</span><strong><?= number_format((float)$lineData['demand_total'], 0, ',', '.') ?></strong></div>
                    <div class="mini-stat"><span>Demanda calculada</span><strong><?= number_format((float)$lineData['demand_matched'], 0, ',', '.') ?></strong></div>
                    <?php if ($lineCoverage < 99.95): ?>
                        <p class="coverage-warning">A curva desta linha está incompleta até que os parâmetros pendentes sejam resolvidos.</p>
                    <?php else: ?>
                        <p class="coverage-ok">Toda a demanda desta linha possui parâmetro técnico válido.</p>
                    <?php endif; ?>
                </aside>
            </div>

            <article
                class="period-inspector"
                id="period-inspector"
                data-periods="<?= htmlspecialchars(json_encode($lineData['periods'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?>"
                data-models="<?= htmlspecialchars(json_encode($lineData['models'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?>"
                data-default-period="<?= htmlspecialchars($defaultPeriod, ENT_QUOTES, 'UTF-8') ?>"
            >
                <div class="card-head period-head">
                    <div>
                        <span class="file-label">COMPOSIÇÃO DA CARGA</span>
                        <h3>Detalhe por modelo</h3>
                    </div>
                    <label class="period-select-wrap">
                        <span>Período</span>
                        <select id="period-select">
                            <?php foreach ($lineData['periods'] as $period): ?>
                                <option value="<?= htmlspecialchars((string)$period['period'], ENT_QUOTES, 'UTF-8') ?>" <?= $period['period'] === $defaultPeriod ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string)$period['period'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>

                <div class="period-kpis">
                    <div><span>Demanda total</span><strong id="period-demand-total">—</strong></div>
                    <div><span>Demanda calculada</span><strong id="period-demand-matched">—</strong></div>
                    <div><span>Horas/mês</span><strong id="period-hours-month">—</strong></div>
                    <div><span>Horas/dia</span><strong id="period-hours-day">—</strong></div>
                    <div><span>Cobertura</span><strong id="period-coverage">—</strong></div>
                </div>

                <div class="period-warning" id="period-warning" hidden></div>
                <div class="model-breakdown" id="model-breakdown"></div>
            </article>

            <article class="table-card">
                <div class="card-head compact">
                    <div>
                        <span class="file-label">LINHA <?= htmlspecialchars((string)$lineData['name'], ENT_QUOTES, 'UTF-8') ?></span>
                        <h3>Capacidade mensal</h3>
                        <p>Horas consideram somente a parcela da demanda com CT/OEE resolvido.</p>
                    </div>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th>Período</th>
                            <th>Dias</th>
                            <th>Demanda total</th>
                            <th>Cobertura</th>
                            <th>Horas/mês</th>
                            <th>Horas/dia</th>
                            <th>Leitura</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($lineData['periods'] as $period):
                            $h = (float)$period['hours_day'];
                            $periodCoverage = (float)$period['coverage_pct'];
                        ?>
                            <tr>
                                <td><strong><?= htmlspecialchars((string)$period['period'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td><?= (int)$period['productive_days'] ?></td>
                                <td><?= number_format((float)$period['demand_total'], 0, ',', '.') ?></td>
                                <td>
                                    <span class="coverage-pill <?= $periodCoverage < 99.95 ? 'partial' : 'complete' ?>">
                                        <?= number_format($periodCoverage, 0, ',', '.') ?>%
                                    </span>
                                </td>
                                <td><?= number_format((float)$period['hours_month'], 2, ',', '.') ?> h</td>
                                <td><strong><?= number_format($h, 2, ',', '.') ?> h/d</strong></td>
                                <td>
                                    <span class="load-tag <?= $h > 21.5833 ? 'over' : ($h > 16.4167 ? 'high' : ($h > 8.75 ? 'medium' : 'low')) ?>">
                                        <?= $h > 21.5833 ? '> 3 turnos' : ($h > 16.4167 ? '3 turnos' : ($h > 8.75 ? '2 turnos' : '1 turno')) ?>
                                    </span>
                                    <?php if ($periodCoverage < 99.95): ?><small class="partial-note">parcial</small><?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="table-card line-ranking-card">
                <div class="card-head compact">
                    <div>
                        <span class="file-label">VISÃO GERAL</span>
                        <h3>Pico por linha</h3>
                    </div>
                </div>
                <div class="line-ranking">
                    <?php $rankMax = max(1.0, (float)($summary['lines'][0]['peak_hours_day'] ?? 1)); ?>
                    <?php foreach ($summary['lines'] as $rank => $line): ?>
                        <a class="line-rank-row <?= $line['name'] === $selectedLine ? 'active' : '' ?>" href="/?line=<?= rawurlencode((string)$line['name']) ?>">
                            <span class="rank-index"><?= $rank + 1 ?></span>
                            <span class="rank-name"><?= htmlspecialchars((string)$line['name'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="rank-bar"><i style="width:<?= number_format(min(100, 100 * (float)$line['peak_hours_day'] / $rankMax), 2, '.', '') ?>%"></i></span>
                            <strong><?= number_format((float)$line['peak_hours_day'], 2, ',', '.') ?> h/d</strong>
                            <small><?= number_format((float)$line['technical_coverage_pct'], 0, ',', '.') ?>%</small>
                        </a>
                    <?php endforeach; ?>
                </div>
            </article>
        <?php endif; ?>

        <?php if (!empty($summary['issues'])): ?>
            <article class="table-card" id="technical-issues">
                <div class="card-head compact issue-head">
                    <div>
                        <span class="file-label">QUALIDADE DO CATÁLOGO</span>
                        <h3>Pendências técnicas</h3>
                        <p>Esses modelos ficam fora do cálculo até que CT/OEE sejam resolvidos.</p>
                    </div>
                    <input class="issue-search" id="issue-search" type="search" placeholder="Filtrar modelo ou linha">
                </div>
                <div class="issue-grid" id="issue-grid">
                    <?php foreach ($summary['issues'] as $issue): ?>
                        <div class="issue-item" data-search="<?= htmlspecialchars(strtolower((string)$issue['model'] . ' ' . (string)$issue['line']), ENT_QUOTES, 'UTF-8') ?>">
                            <span class="issue-status <?= strtolower((string)$issue['status']) ?>"><?= htmlspecialchars((string)$issue['status'], ENT_QUOTES, 'UTF-8') ?></span>
                            <strong><?= htmlspecialchars((string)$issue['model'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <small>
                                <?= htmlspecialchars((string)$issue['line'], ENT_QUOTES, 'UTF-8') ?>
                                · <?= number_format((float)$issue['demand_affected'], 0, ',', '.') ?> peças
                                · <?= count($issue['periods']) ?> período(s)
                                <?= $issue['candidate_count'] ? ' · ' . (int)$issue['candidate_count'] . ' candidatos' : '' ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </article>
        <?php endif; ?>
    <?php endif; ?>
</section>

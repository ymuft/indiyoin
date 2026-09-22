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
?>
<section class="content-area">
    <?php if (!empty($flashError)): ?>
        <div class="alert alert-error page-alert"><strong>Importação não concluída.</strong> <?= htmlspecialchars((string)$flashError, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if (!$analysis): ?>
        <div class="welcome-grid">
            <div class="intro-card">
                <span class="status-chip">Primeiro fluxo</span>
                <h2>Carregue um PPV para gerar a visão de capacidade.</h2>
                <p>O arquivo fornece a demanda produtiva e os dias do período. CT e OEE são resolvidos pelo catálogo técnico versionado.</p>
                <div class="flow-row">
                    <span>PPV.xlsx</span><i>→</i><span>Demanda</span><i>→</i><span>CT/OEE</span><i>→</i><span>h/dia</span>
                </div>
            </div>
            <form class="upload-card" method="post" action="/import" enctype="multipart/form-data">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string)$csrf, ENT_QUOTES, 'UTF-8') ?>">
                <label class="dropzone" for="ppv-file">
                    <span class="upload-icon">↑</span>
                    <strong>Selecione o PPV</strong>
                    <span>Arquivo Excel .xlsx · até 30 MB</span>
                    <input id="ppv-file" name="ppv" type="file" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                </label>
                <div class="selected-file" id="selected-file">Nenhum arquivo selecionado</div>
                <button class="primary-button full" type="submit">Analisar PPV</button>
            </form>
        </div>
        <div class="principle-card">
            <div><span class="number">01</span><strong>Demanda é entrada</strong><p>O PPV informa linha, modelo, mês, demanda e dias produtivos.</p></div>
            <div><span class="number">02</span><strong>Parâmetro é conhecimento</strong><p>CT e OEE vêm do catálogo técnico; conflito não é resolvido por chute.</p></div>
            <div><span class="number">03</span><strong>Capacity é resultado</strong><p>O motor transforma demanda em horas/mês e horas/dia por modelo e linha.</p></div>
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

        <div class="metric-grid">
            <article class="metric-card"><span>Pontos de demanda</span><strong><?= number_format((int)$summary['totals']['points'], 0, ',', '.') ?></strong><small>modelo × mês lidos</small></article>
            <article class="metric-card success"><span>Reconhecidos</span><strong><?= number_format((int)$summary['totals']['matched'], 0, ',', '.') ?></strong><small>CT e OEE encontrados</small></article>
            <article class="metric-card warning"><span>Ambíguos</span><strong><?= number_format((int)$summary['totals']['ambiguous'], 0, ',', '.') ?></strong><small>precisam de decisão técnica</small></article>
            <article class="metric-card danger"><span>Não resolvidos</span><strong><?= number_format((int)$summary['totals']['unresolved'], 0, ',', '.') ?></strong><small>sem parâmetro no catálogo</small></article>
        </div>

        <div class="line-tabs" aria-label="Linhas produtivas">
            <?php foreach ($summary['lines'] as $line): ?>
                <a class="line-tab <?= $line['name'] === $selectedLine ? 'active' : '' ?>" href="/?line=<?= rawurlencode((string)$line['name']) ?>">
                    <span><?= htmlspecialchars((string)$line['name'], ENT_QUOTES, 'UTF-8') ?></span>
                    <small>pico <?= number_format((float)$line['peak_hours_day'], 2, ',', '.') ?> h/d</small>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if ($lineData): ?>
            <?php
                $chartPayload = array_map(static fn(array $p): array => ['period' => $p['period'], 'hours' => round((float)$p['hours_day'], 4)], $lineData['periods']);
                $peak = (float)$lineData['peak_hours_day'];
                $turnState = $peak > 21.5833 ? 'Acima de 3 turnos' : ($peak > 16.4167 ? 'Até 3 turnos' : ($peak > 8.75 ? 'Até 2 turnos' : 'Até 1 turno'));
            ?>
            <div class="dashboard-grid">
                <article class="chart-card">
                    <div class="card-head">
                        <div><span class="file-label">NECESSIDADE DE CAPACIDADE</span><h2><?= htmlspecialchars((string)$lineData['name'], ENT_QUOTES, 'UTF-8') ?></h2></div>
                        <div class="peak-badge"><span>Pico</span><strong><?= number_format($peak, 2, ',', '.') ?> h/d</strong><small><?= htmlspecialchars($turnState, ENT_QUOTES, 'UTF-8') ?></small></div>
                    </div>
                    <div class="capacity-chart" data-series="<?= htmlspecialchars(json_encode($chartPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?>" aria-label="Gráfico mensal de horas necessárias por dia"></div>
                    <div class="legend"><span><i class="legend-line one"></i>1 turno · 8,75h</span><span><i class="legend-line two"></i>2 turnos · 16,42h</span><span><i class="legend-line three"></i>3 turnos · 21,58h</span></div>
                </article>
                <article class="health-card">
                    <span class="file-label">LEITURA RÁPIDA</span>
                    <h3>Qualidade do mapeamento</h3>
                    <?php $total = max(1, (int)$summary['totals']['points']); $coverage = 100 * (int)$summary['totals']['matched'] / $total; ?>
                    <div class="coverage-ring" data-value="<?= number_format($coverage, 1, '.', '') ?>"><strong><?= number_format($coverage, 1, ',', '.') ?>%</strong><span>cobertura</span></div>
                    <p>O cálculo da linha considera apenas pontos com correspondência técnica validada.</p>
                    <?php if ((int)$summary['totals']['ambiguous'] + (int)$summary['totals']['unresolved'] > 0): ?>
                        <a href="#technical-issues" class="text-link">Ver pendências técnicas ↓</a>
                    <?php endif; ?>
                </article>
            </div>

            <article class="table-card">
                <div class="card-head compact"><div><span class="file-label">LINHA <?= htmlspecialchars((string)$lineData['name'], ENT_QUOTES, 'UTF-8') ?></span><h3>Capacidade mensal</h3></div></div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Período</th><th>Dias</th><th>Demanda</th><th>Horas/mês</th><th>Horas/dia</th><th>Leitura</th></tr></thead>
                        <tbody>
                        <?php foreach ($lineData['periods'] as $period): $h = (float)$period['hours_day']; ?>
                            <tr>
                                <td><strong><?= htmlspecialchars((string)$period['period'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td><?= (int)$period['productive_days'] ?></td>
                                <td><?= number_format((float)$period['demand'], 0, ',', '.') ?></td>
                                <td><?= number_format((float)$period['hours_month'], 2, ',', '.') ?> h</td>
                                <td><strong><?= number_format($h, 2, ',', '.') ?> h/d</strong></td>
                                <td><span class="load-tag <?= $h > 21.5833 ? 'over' : ($h > 16.4167 ? 'high' : ($h > 8.75 ? 'medium' : 'low')) ?>"><?= $h > 21.5833 ? '> 3 turnos' : ($h > 16.4167 ? '3 turnos' : ($h > 8.75 ? '2 turnos' : '1 turno')) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </article>
        <?php endif; ?>

        <?php if (!empty($summary['issues'])): ?>
            <article class="table-card" id="technical-issues">
                <div class="card-head compact"><div><span class="file-label">QUALIDADE DO CATÁLOGO</span><h3>Pendências técnicas</h3><p>Esses modelos não entram no cálculo até que o parâmetro seja resolvido.</p></div></div>
                <div class="issue-grid">
                    <?php foreach (array_slice($summary['issues'], 0, 18) as $issue): ?>
                        <div class="issue-item"><span class="issue-status <?= strtolower((string)$issue['status']) ?>"><?= htmlspecialchars((string)$issue['status'], ENT_QUOTES, 'UTF-8') ?></span><strong><?= htmlspecialchars((string)$issue['model'], ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars((string)$issue['line'], ENT_QUOTES, 'UTF-8') ?><?= $issue['candidate_count'] ? ' · ' . (int)$issue['candidate_count'] . ' candidatos' : '' ?></small></div>
                    <?php endforeach; ?>
                </div>
            </article>
        <?php endif; ?>
    <?php endif; ?>
</section>

<?php $pageTitle = 'Entrar · Indiyoin'; ?>
<div class="login-shell">
    <section class="login-panel">
        <div class="login-brand"><span class="brand-mark large">I</span><span><strong>Indiyoin</strong><small>capacity intelligence</small></span></div>
        <div class="login-copy">
            <p class="eyebrow">CAPACITY PLANNING</p>
            <h1>Da demanda do PPV para uma leitura clara de capacidade.</h1>
            <p>Importe o PPV, associe cada modelo aos parâmetros técnicos e visualize a necessidade em horas por dia.</p>
        </div>
        <div class="login-feature-grid">
            <div><strong>PPV</strong><span>Demanda produtiva</span></div>
            <div><strong>CT + OEE</strong><span>Catálogo técnico</span></div>
            <div><strong>h/dia</strong><span>Capacidade necessária</span></div>
        </div>
    </section>
    <section class="login-card-wrap">
        <form class="login-card" method="post" action="/login">
            <div class="login-card-head">
                <span class="status-chip">Acesso interno</span>
                <h2>Entrar no sistema</h2>
                <p>Use sua conta autorizada para acessar os dados de planejamento.</p>
            </div>
            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?= htmlspecialchars((string)$error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string)$csrf, ENT_QUOTES, 'UTF-8') ?>">
            <label class="field"><span>E-mail</span><input type="email" name="email" autocomplete="username" required></label>
            <label class="field"><span>Senha</span><input type="password" name="password" autocomplete="current-password" required></label>
            <button class="primary-button full" type="submit">Entrar</button>
            <p class="security-note"><span>●</span> Sessão, CSRF e proteção de acesso fornecidos pelo AppFoundry.</p>
        </form>
    </section>
</div>

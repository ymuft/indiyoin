<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle ?? 'Indiyoin', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="/assets/app.css">
    <link rel="stylesheet" href="/assets/dashboard-v2.css">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <a class="brand" href="/" aria-label="Indiyoin">
            <span class="brand-mark">I</span>
            <span><strong>Indiyoin</strong><small>capacity intelligence</small></span>
        </a>
        <nav class="nav-list" aria-label="Navegação principal">
            <a class="nav-item active" href="/"><span class="nav-icon">◫</span> Visão de capacidade</a>
            <span class="nav-item disabled"><span class="nav-icon">⌁</span> Parâmetros técnicos <em>em breve</em></span>
            <span class="nav-item disabled"><span class="nav-icon">↔</span> Movimentações <em>em breve</em></span>
        </nav>
        <div class="sidebar-foot">
            <span class="foundation-dot"></span>
            <span>Base protegida por <strong>AppFoundry</strong></span>
        </div>
    </aside>
    <main class="main-area">
        <header class="topbar">
            <div>
                <p class="eyebrow">PLANEJAMENTO DE CAPACIDADE</p>
                <h1><?= htmlspecialchars($headerTitle ?? 'PPV Capacity', ENT_QUOTES, 'UTF-8') ?></h1>
            </div>
            <div class="user-actions">
                <div class="user-badge">
                    <span class="avatar"><?= htmlspecialchars(strtoupper(substr((string)($user['name'] ?? 'U'), 0, 1)), ENT_QUOTES, 'UTF-8') ?></span>
                    <span><strong><?= htmlspecialchars((string)($user['name'] ?? 'Usuário'), ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars((string)($user['role'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small></span>
                </div>
                <form method="post" action="/logout">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string)$csrf, ENT_QUOTES, 'UTF-8') ?>">
                    <button class="icon-button" type="submit" title="Sair">↗</button>
                </form>
            </div>
        </header>
        <?= $content ?>
    </main>
</div>
<script src="/assets/dashboard.js" defer></script>
</body>
</html>

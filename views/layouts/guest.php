<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle ?? 'Indiyoin', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="/assets/app.css">
</head>
<body class="guest-body">
<?= $content ?>
</body>
</html>

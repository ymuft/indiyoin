<?php

declare(strict_types=1);

// Interface web propositalmente minima nesta primeira base.
// O fluxo completo sera conectado ao mesmo caso de uso usado pelo CLI.

?><!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Indiyoin</title>
    <style>
        body{font-family:system-ui,sans-serif;max-width:900px;margin:60px auto;padding:0 24px;background:#f6f7f9;color:#17191c}
        main{background:#fff;border:1px solid #e3e5e8;border-radius:16px;padding:32px}
        code{background:#f1f3f5;padding:2px 6px;border-radius:6px}
    </style>
</head>
<body>
<main>
    <h1>Indiyoin</h1>
    <p>Base inicial do motor PPV → demanda → CT/OEE → capacidade.</p>
    <p>O primeiro fluxo executável está disponível via <code>php bin/analyze.php arquivo.xlsx</code>.</p>
    <p>A próxima etapa desta tela será upload, validação do PPV e visualização por linha/modelo/mês.</p>
</main>
</body>
</html>

# AGENTS.md — Indiyoin

Este arquivo é o contrato operacional para qualquer agente de código (Codex ou equivalente) trabalhando neste repositório.

## 1. Missão do projeto

O Indiyoin é um motor independente de capacidade produtiva. Ele transforma a demanda contida em um PPV `.xlsx` em necessidade de capacidade por **linha, modelo e período**.

Regra principal:

> **PPV informa demanda. Catálogo técnico informa como fabricar. O motor calcula capacidade.**

Fluxo esperado:

```text
PPV.xlsx
  -> detecção estrutural
  -> demanda canônica (linha/modelo/período/demanda/dias produtivos)
  -> resolução técnica (CT/OEE)
  -> cálculo
  -> resumo/dashboard
```

## 2. Regras de domínio inegociáveis

### 2.1 PPV não é o motor técnico

O PPV deve ser tratado como fonte de demanda e calendário produtivo. CT/OEE usados no cálculo pertencem ao catálogo técnico.

É aceitável extrair valores históricos de uma planilha para **semear ou validar** o catálogo, mas a execução normal do sistema não deve depender de CT/OEE encontrados no PPV carregado pelo usuário.

### 2.2 Nunca fixar coordenadas de Excel

Não codificar regras como `AM`, `LT`, `LY`, `LZ`, linha 11, linha 13 etc. O leitor deve localizar semanticamente os campos necessários.

O contrato atual procura:

- aba preferencial `PPV`;
- cabeçalho com `LINHA` e `MODELO`/`MOD`;
- blocos mensais com `PROD.`;
- mês/período;
- dias produtivos associados ao período.

Se a estrutura não puder ser reconhecida inequivocamente, falhar com diagnóstico. Não criar fallback silencioso por coordenada.

### 2.3 Nunca relacionar blocos pela posição física da linha

Não assumir que a linha N do bloco de demanda representa o mesmo produto da linha N de outro bloco técnico da planilha. Relações devem ser feitas por identidade/routing explicitamente resolvidos.

### 2.4 Nunca inventar parâmetros técnicos

Estados válidos:

- `MATCHED`: um único conjunto CT/OEE seguro;
- `AMBIGUOUS`: mais de uma variante possível;
- `UNRESOLVED`: não há parâmetro técnico conhecido.

`AMBIGUOUS` e `UNRESOLVED` não podem receber CT/OEE arbitrário apenas para produzir um número.

### 2.5 Fórmula de capacidade

Com CT em segundos/peça e OEE decimal:

```text
required_hours_month = demand * CT_seconds / (3600 * OEE)
required_hours_day   = required_hours_month / productive_days
```

A demanda total e a demanda efetivamente calculada devem permanecer separadas para que a UI consiga informar a cobertura técnica do resultado.

### 2.6 Modelos repetidos devem ser agregados

Se o mesmo `linha + modelo + período` aparecer mais de uma vez no PPV, a composição do dashboard deve somar as contribuições. Não usar uma estrutura que sobrescreva ocorrências anteriores.

## 3. Fronteiras arquiteturais

### `src/AppFoundry/`

Infraestrutura compartilhada: bootstrap, paths, banco, autenticação, sessão, CSRF, rate limit, auditoria, CSP e router.

Não introduzir conceitos como PPV, CT, OEE, linha produtiva ou capacidade dentro dessa camada.

### `src/Domain/` e `src/Application/`

Regras do Indiyoin. Devem permanecer independentes de HTML, sessão, rota HTTP e detalhes do banco sempre que possível.

### `src/Infrastructure/`

Adaptadores de entrada/saída, incluindo leitura XLSX e catálogo técnico CSV.

### `src/Web/`

Controladores, rendering e ligação entre a aplicação web e o núcleo.

### `public/`

Entry point e assets do frontend. A aplicação usa CSP restritiva: preferir assets locais e não introduzir CDN sem uma decisão explícita de arquitetura/segurança.

## 4. Arquivos que devem ser lidos antes de mudanças relevantes

1. `README.md`
2. `docs/ARCHITECTURE.md`
3. `docs/PPV-MAPPING.md`
4. `docs/ROADMAP.md`
5. `docs/REQUIREMENTS.md`
6. `docs/INSTALL.md`

Se a tarefa envolver a fundação web, ler também `docs/APPFOUNDRY.md`.

## 5. Instalação rápida para desenvolvimento

### Docker — caminho recomendado

```bash
cp .env.example .env
docker compose build
docker compose run --rm app php scripts/migrate.php
docker compose run --rm app php scripts/create-admin.php --name="Admin" --email="admin@example.com"
docker compose up -d
```

Aplicação: `http://localhost:8080`

Health check:

```bash
curl -fsS http://localhost:8080/health
```

### PHP local

```bash
composer install
cp .env.example .env
php scripts/migrate.php
php scripts/create-admin.php --name="Admin" --email="admin@example.com"
php -S 127.0.0.1:8080 -t public public/index.php
```

## 6. Comandos obrigatórios de validação

Antes de considerar uma alteração pronta:

```bash
composer validate --strict
composer lint
composer test
node --check public/assets/dashboard.js
docker build -t indiyoin-local .
```

Se a alteração não tocar frontend ou Docker, ainda assim execute o máximo possível e registre qualquer etapa que não pôde ser executada.

Mudanças no parser do PPV devem incluir teste cobrindo a nova estrutura encontrada. Prefira fixture/sheet sintético gerado em teste; PPVs reais e dados corporativos não devem ser commitados.

## 7. Critério de pronto

Uma mudança só está pronta quando:

- preserva as regras de domínio acima;
- não reduz `MATCHED/AMBIGUOUS/UNRESOLVED` a uma escolha silenciosa;
- possui teste para comportamento novo ou correção relevante;
- `composer lint` passa;
- `composer test` passa;
- frontend alterado passa em `node --check`;
- documentação é atualizada quando o contrato ou a instalação mudam;
- nenhum segredo, `.env`, PPV real, banco SQLite ou conteúdo de `storage/` é versionado.

## 8. Segurança de Git

- Não usar `git push --force`.
- Não reescrever histórico sem solicitação explícita.
- Não apagar alterações do usuário para simplificar uma tarefa.
- Não versionar credenciais, senhas, tokens, PPVs reais ou bancos locais.
- `composer.lock` deve permanecer versionado para builds reproduzíveis.

## 9. Prioridade funcional atual

A base já possui login, upload de PPV, cálculo, dashboard, seleção de linha, composição por modelo/período, cobertura da demanda e lista de pendências técnicas.

Próxima frente prioritária:

1. tela de **Parâmetros Técnicos**;
2. visualizar e editar CT/OEE de forma auditável;
3. resolver `UNRESOLVED` e `AMBIGUOUS` sem adivinhação;
4. aliases validados;
5. routing/identidade técnica quando `linha + modelo` não for suficiente;
6. depois, movimentação de modelos e cenários.

Ao implementar essas etapas, preserve o catálogo CSV como adaptador atual até existir uma migração explícita para um catálogo persistente.

# Indiyoin

Motor independente para transformar **demanda produtiva do PPV** em **necessidade de capacidade**, usando um catálogo técnico separado para CT/OEE.

> **PPV informa demanda. Catálogo técnico informa como fabricar. O motor calcula capacidade.**

## Projeto standalone

O **Indiyoin é um projeto independente** e deve ser desenvolvido no próprio repositório `ymuft/indiyoin`.

Ele não encaminha automaticamente trabalho para Capacity Planning ou qualquer outro repositório. Quando a documentação ou o código usam os termos `capacity`, `CapacityCalculator`, `capacity engine` ou "capacidade", eles se referem ao **domínio de cálculo do próprio Indiyoin**.

Outro projeto só deve ser consultado, comparado ou alterado quando isso for pedido explicitamente pelo usuário.

## Fluxo

```text
PPV.xlsx
   ↓
detecção estrutural sem coordenadas fixas
   ↓
demanda canônica por linha/modelo/período
   ↓
catálogo técnico CT/OEE
   ↓
resolução MATCHED / AMBIGUOUS / UNRESOLVED
   ↓
motor de cálculo do Indiyoin
   ↓
horas/mês + horas/dia
   ↓
dashboard
```

## Para Codex / agentes de código

Leia **`AGENTS.md` antes de alterar o projeto** e use `CODEX.md` como entrada rápida.

Eles definem explicitamente que:

- o repositório alvo é `ymuft/indiyoin`;
- tarefas do Indiyoin não devem ser encaminhadas para outro projeto;
- regras de domínio e fronteiras arquiteturais devem ser preservadas;
- instalação e validação devem seguir os comandos documentados.

Depois consulte:

- `docs/ARCHITECTURE.md`
- `docs/PPV-MAPPING.md`
- `docs/REQUIREMENTS.md`
- `docs/INSTALL.md`
- `docs/ROADMAP.md`
- `docs/APPFOUNDRY.md` quando a tarefa envolver a fundação web

## Quick start — Docker

```bash
git clone https://github.com/ymuft/indiyoin.git
cd indiyoin
cp .env.example .env

docker compose build
docker compose run --rm app php scripts/migrate.php
docker compose run --rm app php scripts/create-admin.php \
  --name="Admin" \
  --email="admin@example.com"
docker compose up -d
```

Abra:

```text
http://localhost:8080
```

Health check:

```bash
curl -fsS http://localhost:8080/health
```

Guia completo: `docs/INSTALL.md`.

## Desenvolvimento sem Docker

Com PHP 8.3+, Composer 2 e as extensões listadas em `docs/REQUIREMENTS.md`:

```bash
composer install
cp .env.example .env
php scripts/migrate.php
php scripts/create-admin.php --name="Admin" --email="admin@example.com"
php -S 127.0.0.1:8080 -t public public/index.php
```

## Stack

- PHP 8.3+
- PhpSpreadsheet
- SQLite por padrão
- MySQL opcional
- HTML/CSS/JavaScript local, sem CDN
- PHPUnit 11
- Docker + Apache
- AppFoundry para autenticação, sessão, CSRF, rate limit, auditoria, banco, router, CSP e bootstrap

## Interface atual

A aplicação já cobre:

- login protegido;
- upload de PPV `.xlsx`;
- detecção semântica de `LINHA`, `MODELO`/`MOD`, `PROD.`, meses e dias produtivos;
- resolução CT/OEE pelo catálogo técnico;
- estados `MATCHED`, `AMBIGUOUS` e `UNRESOLVED`;
- filtro/seleção por linha;
- gráfico mensal em horas/dia;
- referências de 1, 2 e 3 turnos;
- seleção de período;
- composição da carga por modelo;
- cobertura da demanda calculada;
- ranking/pico das linhas;
- lista pesquisável de pendências técnicas.

## Fórmula

Com CT em segundos/peça e OEE decimal:

```text
horas_mes = demanda * CT / (3600 * OEE)
horas_dia = horas_mes / dias_produtivos
```

A interface mantém separadas **demanda total** e **demanda calculada**, evitando apresentar uma leitura parcial como se tivesse 100% de cobertura técnica.

## Contrato de leitura do PPV

O leitor não deve depender de letras fixas como `AM`, `LT`, `LY` ou `LZ`.

A importação procura semanticamente:

- aba preferencial `PPV`;
- `LINHA`;
- `MODELO` ou `MOD`;
- meses/períodos;
- `PROD.`;
- dias produtivos.

Saída canônica:

```text
line      model      period      demand      productive_days
THB 5.0   K62H       ABR         20200       20
```

Depois dessa transformação, nenhuma regra de cálculo deve depender da estrutura física do Excel.

## Catálogo técnico

O adaptador atual usa:

```text
data/technical-parameters.csv
```

O catálogo guarda CT/OEE e o estado de resolução técnica. Conflitos são preservados como `AMBIGUOUS`; modelos desconhecidos permanecem `UNRESOLVED`.

O sistema **não escolhe parâmetros arbitrariamente** apenas para concluir um cálculo.

## CLI

O motor também pode ser executado sem interface:

```bash
php bin/analyze.php /caminho/para/ppv.xlsx
```

## Validação antes de entregar mudanças

```bash
composer validate --strict
composer lint
composer test
node --check public/assets/dashboard.js
docker build -t indiyoin-local .
```

## Segurança de dados

Não versionar:

- `.env`;
- PPVs reais;
- `storage/app.sqlite`;
- `storage/imports/`;
- `storage/analysis/`;
- senhas/tokens/credenciais.

Arquivos enviados ficam fora do web root.

## Próxima frente

A prioridade atual é transformar o catálogo técnico em uma funcionalidade administrável pela própria aplicação:

1. tela de **Parâmetros Técnicos**;
2. edição auditável de CT/OEE;
3. resolução de `UNRESOLVED` e `AMBIGUOUS`;
4. aliases validados;
5. routing/identidade técnica quando `linha + modelo` não for suficiente;
6. movimentação de modelos e cenários.

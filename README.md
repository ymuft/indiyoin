# Indiyoin

Protótipo independente para validar o fluxo **PPV → demanda produtiva → CT/OEE → necessidade de capacidade** antes de incorporar a abordagem ao Capacity.

## Princípio

O PPV é tratado como **fonte de demanda**, não como motor de capacidade. CT e OEE pertencem ao catálogo técnico.

```text
PPV.xlsx
   ↓
leitor estrutural
   ↓
demanda por linha/modelo/mês
   ↓
catálogo técnico CSV
   ↓
CT + OEE
   ↓
capacity engine
   ↓
horas/mês + horas/dia
   ↓
dashboard
```

## Fórmula inicial

```text
horas_mes = demanda * CT / (3600 * OEE)
horas_dia = horas_mes / dias_produtivos
```

## Interface atual

A primeira interface já cobre:

- login protegido;
- upload de PPV `.xlsx`;
- detecção semântica de `LINHA`, `MODELO`, `PROD.`, meses e dias produtivos;
- resolução CT/OEE pelo CSV técnico;
- contagem de `MATCHED`, `AMBIGUOUS` e `UNRESOLVED`;
- filtro por linha;
- gráfico mensal em horas/dia;
- referências de 1, 2 e 3 turnos;
- tabela mensal da linha;
- lista de pendências técnicas.

## AppFoundry

O projeto usa componentes do `ymuft/appfoundry` para autenticação, sessão, CSRF, rate limit, auditoria, banco, router, CSP e configuração segura. A integração fica isolada em `src/AppFoundry/`, enquanto a regra do Indiyoin permanece no namespace `Indiyoin\\`.

Veja `docs/APPFOUNDRY.md`.

## Stack

- PHP 8.3+
- PhpSpreadsheet
- SQLite por padrão; MySQL suportado pela fundação AppFoundry
- HTML/CSS/JavaScript local, sem CDN
- PHPUnit
- Docker/Apache

## Catálogo técnico inicial

`data/technical-parameters.csv` contém o mapeamento parcial extraído do PPV histórico. Conflitos são preservados como `AMBIGUOUS`; o sistema não escolhe CT/OEE arbitrariamente.

## Subir localmente com Docker

```bash
cp .env.example .env
docker compose build
docker compose run --rm app php scripts/migrate.php
docker compose run --rm app php scripts/create-admin.php \
  --name="Admin" \
  --email="admin@example.com"
docker compose up -d
```

Abra `http://localhost:8080`.

## Desenvolvimento sem Docker

Com PHP 8.3+ e extensões do PhpSpreadsheet instaladas:

```bash
composer install
cp .env.example .env
php scripts/migrate.php
php scripts/create-admin.php --name="Admin" --email="admin@example.com"
php -S 127.0.0.1:8080 -t public public/index.php
```

## CLI

O motor continua utilizável sem interface:

```bash
php bin/analyze.php /caminho/para/ppv.xlsx
```

## Segurança de dados

Arquivos PPV não são versionados. Uploads ficam em `storage/imports/` e análises derivadas em `storage/analysis/`, ambos ignorados pelo Git.

## Estado

A interface é uma **v0.2 de validação**. O foco agora é validar o parsing contra PPVs reais e ampliar o catálogo técnico antes de adicionar edição de aliases, movimentação de modelos e persistência definitiva.

# Requisitos do Indiyoin

Este documento descreve o ambiente necessário para instalar, executar, testar e desenvolver o Indiyoin.

## 1. Requisitos mínimos de runtime

### PHP

- PHP `8.3+`
- Composer `2.x`

Extensões utilizadas pela aplicação e/ou pelo PhpSpreadsheet:

- `pdo`
- `pdo_sqlite` para SQLite
- `pdo_mysql` quando MySQL for usado
- `mbstring`
- `zip`
- `gd`
- `dom`
- `simplexml`
- `xml`
- `xmlreader`
- `xmlwriter`

A imagem Docker oficial do projeto instala esse conjunto.

### Banco de dados

O ambiente padrão usa SQLite:

```env
DB_DRIVER=sqlite
DB_DATABASE=storage/app.sqlite
```

MySQL também é suportado pela fundação AppFoundry:

```env
DB_DRIVER=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=indiyoin
DB_USERNAME=indiyoin
DB_PASSWORD=troque-esta-senha
```

Não versionar credenciais reais.

## 2. Requisitos recomendados para desenvolvimento

### Opção A — Docker

Recomendado para Codex e desenvolvimento reproduzível:

- Docker Engine atual
- Docker Compose Plugin (`docker compose`)
- Git

Não é necessário instalar PHP ou Composer no host quando todo o fluxo é executado pelo container.

### Opção B — ambiente nativo

- PHP 8.3+
- Composer 2.x
- extensões PHP listadas acima
- Git
- Node.js 22+ recomendado para executar o mesmo `node --check` usado pelo CI

Não há `npm install`: o frontend atual usa JavaScript e CSS locais sem bundler.

## 3. Dependências PHP

Produção:

- `phpoffice/phpspreadsheet ^3.0`

Desenvolvimento:

- `phpunit/phpunit ^11.0`

As versões resolvidas ficam em `composer.lock`. Não remover o lockfile de uma aplicação instalada; ele garante builds reproduzíveis e evita resolver novas versões durante cada build Docker.

## 4. Requisitos de filesystem

A aplicação precisa escrever em:

```text
storage/
storage/imports/
storage/analysis/
```

Com SQLite também precisa escrever no diretório que contém:

```text
storage/app.sqlite
```

Em Linux/Apache, o usuário do processo web deve ter permissão de leitura/escrita nesses caminhos.

Arquivos PPV enviados nunca devem ficar dentro de `public/`.

## 5. Variáveis de ambiente

Arquivo de referência: `.env.example`.

### Aplicação

| Variável | Padrão | Finalidade |
|---|---|---|
| `APP_ENV` | `local` | Ambiente de execução |
| `APP_DEBUG` | `true` | Modo de debug |
| `APP_SESSION_NAME` | `indiyoin_session` | Nome do cookie de sessão |
| `APP_SECURE_COOKIES` | `false` | Exigir cookie HTTPS |

Em produção com HTTPS, usar `APP_DEBUG=false` e `APP_SECURE_COOKIES=true`.

### Banco

| Variável | Padrão | Finalidade |
|---|---|---|
| `DB_DRIVER` | `sqlite` | `sqlite` ou `mysql` |
| `DB_DATABASE` | `storage/app.sqlite` | Arquivo SQLite ou nome do banco MySQL |
| `DB_HOST` | `127.0.0.1` | Host MySQL |
| `DB_PORT` | `3306` | Porta MySQL |
| `DB_USERNAME` | `appfoundry` | Usuário MySQL |
| `DB_PASSWORD` | vazio | Senha MySQL |

### Indiyoin

| Variável | Padrão | Finalidade |
|---|---|---|
| `TECHNICAL_CATALOG_PATH` | `data/technical-parameters.csv` | Fonte CT/OEE atual |
| `PPV_SHEET_NAME` | `PPV` | Aba preferencial do workbook |
| `MAX_UPLOAD_MB` | `30` | Limite de upload do PPV |

## 6. Contrato mínimo do PPV

O arquivo deve ser `.xlsx` e possuir uma estrutura que o detector semântico consiga identificar.

O leitor precisa encontrar:

- uma aba de PPV, preferencialmente chamada `PPV`;
- `LINHA`;
- `MODELO` ou `MOD`;
- períodos mensais;
- `PROD.` como demanda produtiva;
- dias produtivos associados a cada período.

O sistema **não deve exigir letras de coluna específicas**.

Saída canônica:

```text
line      model      period      demand      productive_days
THB 5.0   K62H       ABR         20200       20
```

## 7. Catálogo técnico atual

Arquivo padrão:

```text
data/technical-parameters.csv
```

Campos conceituais:

```text
line
model
ct_seconds
oee_decimal
mapping_status
source_row
```

Estados esperados:

- `MATCHED`
- `AMBIGUOUS`
- `UNRESOLVED`

O sistema não deve inventar CT/OEE para eliminar pendências.

## 8. Fórmula de capacidade

```text
required_hours_month = demand * CT_seconds / (3600 * OEE)
required_hours_day   = required_hours_month / productive_days
```

CT é tratado como segundos/peça e OEE como decimal (`0.80`, `0.85`, etc.).

## 9. Requisitos de segurança

- PPVs enviados ficam fora do web root.
- `.env` não deve ser versionado.
- bancos locais não devem ser versionados.
- uploads e análises derivadas não devem ser versionados.
- assets web devem permanecer locais para respeitar a CSP atual.
- autenticação, sessão, CSRF, rate limit e auditoria são fornecidos pela camada AppFoundry.

## 10. Requisitos de qualidade antes de merge/commit final

Executar:

```bash
composer validate --strict
composer lint
composer test
node --check public/assets/dashboard.js
docker build -t indiyoin-local .
```

Mudanças de parsing do PPV devem adicionar teste específico. Mudanças de cálculo precisam de teste numérico. Mudanças no frontend não devem esconder pendências técnicas nem fazer uma leitura parcial parecer cobertura de 100%.

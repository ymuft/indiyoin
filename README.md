# Indiyoin

Protótipo independente para validar o fluxo **PPV → demanda produtiva → CT/OEE → necessidade de capacidade** antes de incorporar a abordagem ao Capacity.

## Principio

O PPV e tratado como **fonte de demanda**, nao como motor de capacidade. CT e OEE pertencem ao catalogo tecnico.

## Formula inicial

```text
horas_mes = demanda * CT / (3600 * OEE)
horas_dia = horas_mes / dias_produtivos
```

## Stack

- PHP 8.3+
- PhpSpreadsheet para leitura XLSX
- CSV versionado como catalogo tecnico inicial
- PHPUnit
- Nucleo sem dependencia de framework para facilitar migracao posterior para Laravel/Capacity

## Estrutura

```text
src/Domain/                   entidades e regras puras
src/Application/              casos de uso e calculo
src/Contracts/                portas para PPV e catalogo tecnico
src/Infrastructure/Csv/       catalogo CT/OEE atual
src/Infrastructure/Spreadsheet/ leitura/deteccao do PPV
data/                         catalogo parcial versionado
docs/                         arquitetura, contrato e roadmap
bin/                          ferramentas CLI
public/                       entrada web futura
```

## Catalogo tecnico inicial

`data/technical-parameters.csv` contem o mapeamento parcial extraido do PPV historico. O sistema preserva conflitos como `AMBIGUOUS`; nao escolhe CT/OEE arbitrariamente.

## Uso local

```bash
composer install
php bin/analyze.php /caminho/para/ppv.xlsx
```

A saida inicial mostra quantidade de pontos de demanda e quantos foram `MATCHED`, `AMBIGUOUS` ou `UNRESOLVED`.

Para servir a pagina inicial:

```bash
php -S 127.0.0.1:8080 -t public
```

## Estado atual

Esta e a **v0.1 estrutural**. O proximo objetivo e validar o detector semantico contra os dois PPVs reais antes de desenvolver o dashboard.

Veja `docs/ARCHITECTURE.md` e `docs/ROADMAP.md`.

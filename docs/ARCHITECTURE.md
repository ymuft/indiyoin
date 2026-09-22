# Arquitetura do Indiyoin

## Objetivo

Transformar um PPV de demanda produtiva em uma leitura de capacidade por modelo, linha e mês, sem usar o PPV como fonte de parâmetros técnicos.

## Regra central

**PPV informa demanda. Catálogo técnico informa como fabricar. Motor calcula capacidade. AppFoundry protege e entrega a aplicação web.**

```text
Browser
   |
   v
AppFoundry foundation
(auth / session / CSRF / audit / router / CSP)
   |
   v
PPV.xlsx
   |
   v
PpvStructureDetector
   |
   v
PpvDemandReader -------> Demanda canônica
                              |
                              v
CSV Technical Catalog -> Resolver Linha + Modelo
                              |
                              v
                        CapacityCalculator
                              |
                              v
                    AnalysisSummaryBuilder
                              |
                              v
                         Dashboard
```

## Fronteiras

### `src/AppFoundry/`

Infraestrutura web reaproveitada do projeto AppFoundry. Não deve conhecer conceitos como PPV, CT, OEE ou capacidade.

### `src/Domain` + `src/Application`

Regras do Indiyoin. Não devem depender de sessão, HTML, banco ou rota HTTP.

### `src/Infrastructure`

Adaptadores de entrada: planilha XLSX e catálogo CSV.

### `src/Web`

Controladores e view renderer que ligam a fundação AppFoundry ao núcleo do Indiyoin.

## Entradas do PPV

Dados extraídos semanticamente, nunca por coordenadas fixas:

- LINHA
- MODELO/MOD
- meses
- PROD. (demanda produtiva)
- dias produtivos de cada mês

Nos dois PPVs reais analisados, o padrão mensal é `MÊS | número de dias | DIAS` e o `PROD.` fica na primeira coluna do trio. O detector procura semanticamente essa relação em vez de fixar AM/AN/AO etc.

## Catálogo técnico CSV

- line
- model
- ct_seconds
- oee_decimal
- mapping_status
- source_row

O CSV é uma fonte inicial, não a arquitetura final do catálogo.

## Estados de resolução

- `MATCHED`: existe um único conjunto CT/OEE seguro para Linha + Modelo.
- `AMBIGUOUS`: existem variantes técnicas e o sistema não escolhe sozinho.
- `UNRESOLVED`: o catálogo ainda não conhece a combinação.

Nenhum valor técnico é inventado para permitir que o cálculo continue.

## Fórmula

```text
required_hours_month = demand * CT_seconds / (3600 * OEE)
required_hours_day   = required_hours_month / productive_days
```

## Segurança e arquivos

PPVs ficam em `storage/imports/`, fora do web root e fora do Git. O resumo derivado fica em `storage/analysis/`. A interface usa apenas assets locais para permanecer compatível com a CSP restritiva do AppFoundry.

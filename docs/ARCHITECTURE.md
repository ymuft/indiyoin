# Arquitetura do Indiyoin

## Objetivo

Transformar um PPV de demanda produtiva em uma leitura de capacidade por modelo, linha e mes, sem usar o PPV como fonte de parametros tecnicos.

## Regra central

**PPV informa demanda. Catalogo tecnico informa como fabricar. Motor calcula capacidade.**

```text
PPV.xlsx
   |
   v
PpvStructureDetector
   |
   v
PpvDemandReader -------> Demanda canonica
                              |
                              v
CSV Technical Catalog -> Resolver Linha + Modelo
                              |
                              v
                        CapacityCalculator
                              |
                              v
                      Capacity Analysis
```

## Entradas

### PPV

Dados que devem ser extraidos semanticamente, nunca por coordenadas fixas:

- LINHA
- MODELO/MOD
- meses
- PROD. (demanda produtiva)
- dias produtivos de cada mes

### Catalogo tecnico CSV

Primeiro catalogo veio do mapeamento parcial do PPV historico:

- line
- model
- ct_seconds
- oee_decimal
- mapping_status
- source_row

O CSV atual e uma fonte inicial, nao a arquitetura final do catalogo.

## Estados de resolucao

- `MATCHED`: existe um unico conjunto CT/OEE seguro para Linha + Modelo.
- `AMBIGUOUS`: existem variantes tecnicas e o sistema nao escolhe sozinho.
- `UNRESOLVED`: o catalogo ainda nao conhece a combinacao.

Nenhum valor tecnico deve ser inventado para permitir que o calculo continue.

## Formula

```text
required_hours_month = demand * CT_seconds / (3600 * OEE)
required_hours_day   = required_hours_month / productive_days
```

## Limites intencionais da v0.1

- Sem alias automatico de modelos.
- Sem escolha automatica em conflitos como K31A/K2KF.
- Sem banco de dados.
- Sem dashboard final.
- Detector de estrutura do PPV e heuristico e deve ganhar testes com PPVs reais antes de ser considerado estavel.

## Evolucao planejada

1. Validar detector contra PPVs de layouts diferentes.
2. Criar camada de identidade/aliases.
3. Persistir catalogo tecnico versionado.
4. Adicionar relatorio de importacao (matched/ambiguous/unresolved).
5. Agregar capacidade por linha e mes.
6. Dashboard ECharts com composicao por modelo e referencias de turnos.
7. Exportar o nucleo para o Capacity quando o fluxo estiver validado.

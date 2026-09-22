# Roadmap

## Fase 1 — Base tecnica

- [x] Dominio de demanda, parametro tecnico e capacidade.
- [x] Catalogo CSV inicial CT/OEE.
- [x] Estados MATCHED / AMBIGUOUS / UNRESOLVED.
- [x] Formula de horas/mes e horas/dia.
- [x] Contrato para leitura de PPV.
- [x] Detector semantico inicial.

## Fase 2 — Validacao com PPVs reais

- [ ] Testar `2029- youin - Copia.xlsx`.
- [ ] Testar `PCP_PPV_2026_.13 260924(1).xlsx`.
- [ ] Registrar layouts descobertos e falhas de deteccao.
- [ ] Fechar regra de mes + dias produtivos.
- [ ] Gerar relatorio canonico da demanda.

## Fase 3 — Resolucao tecnica

- [ ] Criar aliases validados.
- [ ] Resolver diferencas de nomenclatura sem adivinhar equivalencia.
- [ ] Modelar routing quando Linha + Modelo nao for suficiente.
- [ ] Resolver conflitos K31A e K2KF com evidencia tecnica.

## Fase 4 — Visualizacao

- [ ] Upload web de PPV.
- [ ] Resumo de importacao.
- [ ] Grafico horas/dia por linha e mes.
- [ ] Referencias 1S / 2S / 3S.
- [ ] Drill-down por modelo.
- [ ] Matriz Modelo x Mes.

## Fase 5 — Integracao

- [ ] Congelar contratos do nucleo.
- [ ] Comparar resultados contra YOUIN/Capacity.
- [ ] Levar o nucleo validado para o Capacity.

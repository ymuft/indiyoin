# Roadmap

## Fase 1 — Base técnica

- [x] Domínio de demanda, parâmetro técnico e capacidade.
- [x] Catálogo CSV inicial CT/OEE.
- [x] Estados MATCHED / AMBIGUOUS / UNRESOLVED.
- [x] Fórmula de horas/mês e horas/dia.
- [x] Contrato para leitura de PPV.
- [x] Detector semântico inicial.

## Fase 2 — Validação com PPVs reais

- [x] Inspecionar `2029- youin - Copia.xlsx`.
- [x] Inspecionar `PCP_PPV_2026_.13 260924(1).xlsx`.
- [x] Corrigir leitura de dias produtivos adjacentes à coluna PROD.
- [x] Criar teste sintético para o padrão `MES | DIAS | texto`.
- [ ] Rodar a suíte PHP completa contra os dois arquivos reais em ambiente com PhpSpreadsheet.
- [ ] Gerar relatório canônico da demanda para conferência humana.

## Fase 3 — Resolução técnica

- [ ] Criar aliases validados.
- [ ] Resolver diferenças de nomenclatura sem adivinhar equivalência.
- [ ] Modelar routing quando Linha + Modelo não for suficiente.
- [ ] Resolver conflitos K31A e K2KF com evidência técnica.

## Fase 4 — Visualização

- [x] Upload web de PPV.
- [x] Resumo de importação.
- [x] Gráfico horas/dia por linha e mês.
- [x] Referências 1S / 2S / 3S.
- [x] Lista de pendências técnicas.
- [ ] Drill-down interativo por modelo.
- [ ] Matriz Modelo × Mês.
- [ ] Comparativo de cenários.

## Fase 5 — AppFoundry

- [x] Integrar sessão, CSRF, login e rate limit.
- [x] Integrar SQLite/MySQL e audit log.
- [x] Manter CSP sem CDN.
- [x] Adicionar smoke test de login no CI.
- [x] Adicionar Docker/Apache para execução local.
- [ ] Validar o workflow CI após o primeiro push da integração.

## Fase 6 — Integração futura com Capacity

- [ ] Congelar contratos do núcleo.
- [ ] Comparar resultados contra YOUIN/Capacity.
- [ ] Levar o núcleo validado para o Capacity.

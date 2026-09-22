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
- [ ] Rodar a suíte PHP completa contra os dois arquivos reais em ambiente controlado com PhpSpreadsheet.
- [ ] Gerar relatório canônico da demanda para conferência humana.

PPVs reais não devem ser versionados no repositório.

## Fase 3 — Resolução técnica

### Prioridade atual

- [ ] Criar tela de **Parâmetros Técnicos**.
- [ ] Tornar CT/OEE editáveis com auditoria.
- [ ] Criar fluxo explícito para resolver `UNRESOLVED`.
- [ ] Criar fluxo explícito para resolver `AMBIGUOUS`.
- [ ] Criar aliases validados.
- [ ] Resolver diferenças de nomenclatura sem adivinhar equivalência.
- [ ] Modelar routing/identidade técnica quando Linha + Modelo não for suficiente.
- [ ] Resolver conflitos K31A e K2KF com evidência técnica.
- [ ] Planejar migração do CSV para catálogo técnico persistente sem quebrar o adaptador atual.

## Fase 4 — Visualização

- [x] Upload web de PPV.
- [x] Resumo de importação.
- [x] Gráfico horas/dia por linha e mês.
- [x] Referências 1S / 2S / 3S.
- [x] Lista de pendências técnicas.
- [x] Drill-down interativo por período/modelo.
- [x] Composição da carga por modelo.
- [x] Cobertura de demanda calculada versus demanda total.
- [x] Ranking/pico por linha.
- [ ] Matriz Modelo × Mês.
- [ ] Comparativo de cenários.
- [ ] Movimentação de modelos pela interface.

## Fase 5 — AppFoundry e execução

- [x] Integrar sessão, CSRF, login e rate limit.
- [x] Integrar SQLite/MySQL e audit log.
- [x] Manter CSP sem CDN.
- [x] Adicionar smoke test de login no CI.
- [x] Adicionar smoke test HTTP de upload PPV → cálculo → dashboard.
- [x] Adicionar Docker/Apache para execução local.
- [x] Versionar `composer.lock` para builds reproduzíveis.
- [x] Integrar `Bootstrap`/`Paths` portáveis do AppFoundry.
- [ ] Manter CI verde após mudanças funcionais relevantes.

## Fase 6 — Integração futura com Capacity

- [ ] Congelar contratos do núcleo.
- [ ] Comparar resultados contra YOUIN/Capacity.
- [ ] Validar catálogo/routing em cenários reais.
- [ ] Levar o núcleo validado para o Capacity.

## Critério para avançar da validação para integração

Antes de incorporar o núcleo ao Capacity, o Indiyoin deve provar:

1. leitura robusta de PPVs com estruturas reais diferentes;
2. demanda canônica conferível;
3. resolução técnica sem parâmetros inventados;
4. cálculo reproduzível;
5. cobertura técnica explícita;
6. testes automatizados;
7. catálogo técnico administrável/auditável;
8. resultados comparáveis ao processo de referência.

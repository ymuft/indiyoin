# Roadmap

> O roadmap abaixo pertence exclusivamente ao **Indiyoin**. Nenhuma fase pressupõe encaminhar trabalho para outro repositório.

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

## Fase 6 — Consolidação do Indiyoin

- [ ] Tornar o catálogo técnico persistente e auditável.
- [ ] Manter histórico/versionamento de parâmetros técnicos.
- [ ] Criar aliases e identidade técnica administráveis pela interface.
- [ ] Criar routing versionado por produto/linha quando necessário.
- [ ] Implementar movimentação de modelos entre linhas como cenário, sem alterar a base técnica silenciosamente.
- [ ] Criar comparativo entre cenário base e cenários simulados.
- [ ] Criar rastreabilidade completa: demanda → identidade → routing → CT/OEE → cálculo.
- [ ] Criar exportação/relatório dos resultados e pendências.
- [ ] Ampliar cobertura de testes com diferentes formatos de PPV.

## Critério de maturidade do Indiyoin

O Indiyoin deve ser considerado maduro quando provar:

1. leitura robusta de PPVs com estruturas reais diferentes;
2. demanda canônica conferível;
3. resolução técnica sem parâmetros inventados;
4. cálculo reproduzível;
5. cobertura técnica explícita;
6. testes automatizados;
7. catálogo técnico administrável e auditável;
8. movimentação/cenários com provenance claro;
9. rastreabilidade suficiente para explicar cada número do dashboard.

Comparações com ferramentas ou projetos externos podem ser usadas como validação quando solicitadas, mas **não fazem parte do fluxo normal de desenvolvimento e não mudam o repositório alvo: Indiyoin**.

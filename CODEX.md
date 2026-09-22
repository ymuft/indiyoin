# CODEX.md — Indiyoin

Este é o ponto de entrada rápido para o Codex neste repositório.

## Escopo obrigatório

Você está trabalhando no **Indiyoin**.

- Repositório alvo: `ymuft/indiyoin`.
- Faça as alterações solicitadas neste repositório.
- Não encaminhe tarefas para Capacity Planning ou qualquer outro projeto.
- Não troque o diretório de trabalho para outro repositório.
- Não porte ou publique alterações em outro repositório sem solicitação explícita do usuário na tarefa atual.
- Neste projeto, `capacity`, `CapacityCalculator`, `capacity engine` e "capacidade" referem-se ao domínio de cálculo do **próprio Indiyoin**, não a outro sistema.

Se outro projeto for citado em documentação histórica, trate-o apenas como contexto/referência até que o usuário peça explicitamente uma integração.

## Antes de qualquer alteração

Leia integralmente, nesta ordem:

1. `AGENTS.md` — contrato operacional e regras obrigatórias do projeto.
2. `README.md` — visão geral e quick start.
3. `docs/ARCHITECTURE.md` — fronteiras e fluxo técnico.
4. `docs/PPV-MAPPING.md` — contrato de leitura do PPV.
5. `docs/REQUIREMENTS.md` — requisitos do ambiente.
6. `docs/INSTALL.md` — instalação e execução.
7. `docs/ROADMAP.md` — estado atual e prioridades do Indiyoin.

Se a tarefa tocar autenticação, bootstrap, sessão, banco, CSRF, CSP ou infraestrutura web, leia também `docs/APPFOUNDRY.md`.

## Regra central

> PPV informa demanda. Catálogo técnico informa como fabricar. O motor calcula capacidade.

Não quebre essa separação.

## Nunca fazer

- Não fixar coordenadas de Excel (`AM`, `LT`, `LY`, `LZ`, linhas específicas etc.) como regra definitiva.
- Não relacionar blocos independentes pela posição física da linha da planilha.
- Não inventar CT/OEE para resolver `AMBIGUOUS` ou `UNRESOLVED`.
- Não esconder demanda sem cobertura técnica.
- Não commitir PPVs reais, `.env`, credenciais, SQLite local ou conteúdo de `storage/`.
- Não usar `git push --force` nem reescrever histórico sem solicitação explícita.
- Não sair do Indiyoin para implementar a tarefa em outro repositório.

## Fluxo funcional esperado

```text
PPV.xlsx
  -> PpvStructureDetector
  -> demanda canônica
  -> resolução Linha + Modelo / identidade técnica
  -> catálogo CT/OEE
  -> CapacityCalculator do Indiyoin
  -> AnalysisSummaryBuilder
  -> dashboard
```

Fórmula atual:

```text
required_hours_month = demand * CT_seconds / (3600 * OEE)
required_hours_day   = required_hours_month / productive_days
```

## Validação obrigatória

Antes de encerrar uma alteração, execute o máximo aplicável de:

```bash
composer validate --strict
composer lint
composer test
node --check public/assets/dashboard.js
docker build -t indiyoin-local .
```

Mudanças no parser do PPV devem trazer teste da nova estrutura. Use planilhas sintéticas/fixtures; não versione PPVs corporativos reais.

## Estado atual

Já existem:

- AppFoundry integrado;
- login/sessão/CSRF/auditoria;
- upload `.xlsx`;
- detector semântico de PPV;
- catálogo técnico CSV;
- estados `MATCHED`, `AMBIGUOUS`, `UNRESOLVED`;
- cálculo de horas/mês e horas/dia;
- dashboard de capacidade;
- seleção de linha e período;
- composição por modelo;
- cobertura técnica da demanda;
- pendências técnicas;
- CI e Docker.

## Prioridade atual

A próxima frente é **Parâmetros Técnicos** dentro do Indiyoin:

1. visualizar catálogo técnico;
2. editar CT/OEE de forma auditável;
3. resolver `UNRESOLVED`;
4. resolver `AMBIGUOUS` com escolha explícita/evidência;
5. criar aliases validados;
6. evoluir para routing/identidade técnica quando `linha + modelo` não for suficiente.

Depois vêm movimentação de modelos e cenários, também no Indiyoin.

## Forma de trabalhar

Antes de implementar:

1. confirme que o diretório/repositório atual é o Indiyoin;
2. inspecione o código existente;
3. identifique os componentes afetados;
4. preserve as fronteiras arquiteturais;
5. faça a menor mudança coerente que resolva o problema;
6. adicione/ajuste testes;
7. execute a validação;
8. ao finalizar, reporte arquivos alterados, comportamento, testes e pendências.

Se houver conflito entre este arquivo e `AGENTS.md`, **`AGENTS.md` prevalece**.

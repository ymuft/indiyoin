# Contrato de leitura do PPV

O Indiyoin nao deve depender de letras de coluna como `AM`, `LT`, `LY` ou `LZ`.

## Estrutura procurada

1. Aba preferencial `PPV`.
2. Uma linha de cabecalho contendo simultaneamente `LINHA` e `MODELO` ou `MOD`.
3. Subcabecalhos de demanda `PROD.`.
4. Para cada `PROD.`, um periodo mensal e um numero de dias produtivos proximos no bloco de cabecalho.

## Saida canonica esperada

```text
line      model      period    demand     productive_days
THB 5.0   K62H       ABR       20200      20
THB 5.0   K62H       MAI       18200      20
...
```

Depois dessa transformacao, nenhuma regra de calculo deve depender de Excel.

## Regra de seguranca

Se o leitor nao conseguir identificar inequivocamente os campos obrigatorios, a importacao deve falhar com diagnostico. Nao usar coordenadas de fallback silenciosas.

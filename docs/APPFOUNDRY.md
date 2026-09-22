# AppFoundry no Indiyoin

O Indiyoin incorpora uma camada enxuta da base `ymuft/appfoundry` para validar o AppFoundry em uma aplicação de negócio real.

## O que foi reaproveitado

- carregamento de ambiente;
- validação de configuração segura;
- PDO SQLite/MySQL;
- roteamento HTTP;
- respostas HTTP;
- sessão autenticada;
- CSRF;
- rate limit de login;
- política de senha;
- audit log;
- security headers/CSP;
- migrações base de usuários, rate limit e auditoria.

O código reaproveitado fica isolado em `src/AppFoundry/` com o namespace original `App\\`. O domínio do Indiyoin continua em `Indiyoin\\`.

Essa separação é intencional: o AppFoundry fornece infraestrutura web; o Indiyoin fornece leitura de PPV, resolução técnica e cálculo de capacidade.

## Teste de integração

O workflow `.github/workflows/ci.yml` executa um smoke test real:

1. cria banco SQLite;
2. aplica a migration;
3. cria um administrador;
4. sobe o servidor PHP;
5. valida `/health`;
6. abre `/login` e coleta o token CSRF;
7. autentica com cookie de sessão;
8. confirma acesso à tela principal.

## Licença da base incorporada

As partes derivadas de AppFoundry permanecem cobertas pela licença MIT do projeto original:

MIT License

Copyright (c) 2026 Gabriel Poças

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

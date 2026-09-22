# Instalação e uso do Indiyoin

Este é o guia operacional para instalar o projeto do zero, subir a aplicação, criar o primeiro usuário e validar que o ambiente está saudável.

## 1. Clonar o repositório

```bash
git clone https://github.com/ymuft/indiyoin.git
cd indiyoin
```

Como o repositório é privado, o Git precisa estar autenticado com uma conta/token/SSH que tenha acesso.

## 2. Caminho recomendado: Docker

O Docker é o caminho recomendado para desenvolvimento e para o Codex porque reduz diferenças entre máquinas.

### 2.1 Criar o `.env`

```bash
cp .env.example .env
```

Para desenvolvimento local, os valores padrão já usam SQLite.

### 2.2 Build da imagem

```bash
docker compose build
```

### 2.3 Criar o banco

```bash
docker compose run --rm app php scripts/migrate.php
```

Resultado esperado:

```text
Migration complete.
```

### 2.4 Criar o primeiro administrador

```bash
docker compose run --rm app php scripts/create-admin.php \
  --name="Admin" \
  --email="admin@example.com"
```

A senha será solicitada no terminal e não precisa aparecer no histórico do shell.

Para ambiente efêmero de teste, também existe:

```bash
docker compose run --rm app php scripts/create-admin.php \
  --name="Admin" \
  --email="admin@example.com" \
  --password="uma-senha-de-teste-segura"
```

Não use senha real em comando versionado, script compartilhado ou documentação interna com histórico persistente.

### 2.5 Subir a aplicação

```bash
docker compose up -d
```

Abra:

```text
http://localhost:8080
```

### 2.6 Verificar saúde

```bash
curl -fsS http://localhost:8080/health
```

O endpoint deve retornar status `ok`.

### 2.7 Logs

```bash
docker compose logs -f app
```

### 2.8 Parar

```bash
docker compose down
```

O diretório `storage/` é montado como volume bind, então banco SQLite, imports e análises persistem no diretório local do projeto.

---

## 3. Instalação nativa no Ubuntu/Linux

Use este caminho quando não quiser Docker.

### 3.1 Pré-requisitos

Você precisa de:

- PHP 8.3+
- Composer 2.x
- Git
- extensões PHP descritas em `docs/REQUIREMENTS.md`

Verifique:

```bash
php -v
composer --version
php -m
```

Extensões relevantes:

```text
pdo
pdo_sqlite
mbstring
zip
gd
dom
simplexml
xml
xmlreader
xmlwriter
```

Para MySQL, também:

```text
pdo_mysql
```

A forma exata de instalar pacotes PHP varia conforme a versão/distribuição do Ubuntu. Se PHP 8.3 estiver disponível no repositório configurado, os pacotes normalmente correspondem às extensões acima.

### 3.2 Instalar dependências PHP

```bash
composer install
```

O projeto usa `composer.lock`; não execute `composer update` apenas para instalar o projeto.

### 3.3 Configurar ambiente

```bash
cp .env.example .env
```

### 3.4 Criar banco

```bash
php scripts/migrate.php
```

### 3.5 Criar administrador

```bash
php scripts/create-admin.php \
  --name="Admin" \
  --email="admin@example.com"
```

### 3.6 Subir servidor de desenvolvimento

```bash
php -S 127.0.0.1:8080 -t public public/index.php
```

Abra:

```text
http://127.0.0.1:8080
```

---

## 4. Primeira utilização

Depois de entrar no sistema:

1. abra a tela principal;
2. clique em **Importar PPV**;
3. selecione o arquivo `.xlsx`;
4. aguarde o processamento;
5. escolha uma linha produtiva;
6. navegue pelos períodos;
7. confira a composição por modelo;
8. confira a cobertura da demanda;
9. revise `AMBIGUOUS` e `UNRESOLVED` antes de tratar o resultado como completo.

A aplicação diferencia:

```text
Demanda total
Demanda calculada
Cobertura técnica
Horas/mês
Horas/dia
```

Se parte da demanda não tiver CT/OEE seguro, o valor de horas será parcial e a interface deve deixar isso visível.

---

## 5. Formato esperado do PPV

O sistema não depende de letras fixas de coluna.

O detector procura semanticamente:

- aba `PPV` (preferencial, configurável);
- `LINHA`;
- `MODELO` ou `MOD`;
- períodos mensais;
- `PROD.`;
- dias produtivos.

Exemplo da saída canônica interna:

```text
line      model      period      demand      productive_days
THB 5.0   K62H       ABR         20200       20
THB 5.0   K62H       MAI         18200       20
```

Depois dessa etapa, o cálculo não deve depender da estrutura do Excel.

---

## 6. Catálogo técnico

O catálogo atual fica em:

```text
data/technical-parameters.csv
```

Configuração:

```env
TECHNICAL_CATALOG_PATH=data/technical-parameters.csv
```

O cálculo cruza a demanda canônica com CT/OEE do catálogo.

Não altere a lógica para usar CT/OEE diretamente do PPV apenas para resolver um `UNRESOLVED`.

---

## 7. Uso pela CLI

É possível analisar um PPV sem abrir a interface web:

```bash
php bin/analyze.php /caminho/para/arquivo.xlsx
```

Em Docker:

```bash
docker compose run --rm app php bin/analyze.php /caminho/no/container/arquivo.xlsx
```

Para analisar um arquivo externo via Docker, monte ou copie o arquivo para um caminho acessível ao container; não coloque PPVs reais no Git.

---

## 8. Rodar testes

### Suite PHP

```bash
composer test
```

### Lint PHP

```bash
composer lint
```

### Validar Composer

```bash
composer validate --strict
```

### Sintaxe do frontend

```bash
node --check public/assets/dashboard.js
```

### Build Docker

```bash
docker build -t indiyoin-local .
```

Sequência recomendada antes de entregar uma alteração:

```bash
composer validate --strict \
  && composer lint \
  && composer test \
  && node --check public/assets/dashboard.js \
  && docker build -t indiyoin-local .
```

---

## 9. Bootstrap rápido para Codex

Ao abrir o repositório em uma máquina de desenvolvimento, o Codex deve começar lendo:

```text
AGENTS.md
README.md
docs/ARCHITECTURE.md
docs/PPV-MAPPING.md
docs/REQUIREMENTS.md
docs/ROADMAP.md
```

Depois, para preparar o ambiente nativo:

```bash
composer install
[ -f .env ] || cp .env.example .env
php scripts/migrate.php
composer validate --strict
composer lint
composer test
node --check public/assets/dashboard.js
```

Para tarefas que precisam da interface autenticada, crie um usuário local de desenvolvimento com `scripts/create-admin.php`.

O Codex nunca deve:

- commitir `.env`;
- commitir PPV real;
- commitir `storage/app.sqlite`;
- commitir uploads/análises de `storage/`;
- inventar CT/OEE para fazer testes passarem;
- criar parsing dependente de coordenadas fixas de planilha.

---

## 10. MySQL opcional

Configure no `.env`:

```env
DB_DRIVER=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=indiyoin
DB_USERNAME=indiyoin
DB_PASSWORD=sua-senha
```

Depois execute:

```bash
php scripts/migrate.php
```

O script seleciona automaticamente `migrations/001_init.mysql.sql` quando `DB_DRIVER=mysql`.

---

## 11. Reset de ambiente local

Somente para desenvolvimento, quando quiser recriar o SQLite do zero:

```bash
docker compose down
rm -f storage/app.sqlite
php scripts/migrate.php
```

Se estiver usando Docker para executar PHP:

```bash
docker compose run --rm app php scripts/migrate.php
```

Depois recrie o administrador.

Não execute esse procedimento em ambiente com dados que precisem ser preservados.

---

## 12. Problemas comuns

### `vendor/autoload.php` não encontrado

Execute:

```bash
composer install
```

### Erro ao abrir XLSX

Confira extensões PHP como `zip`, `xmlreader`, `xmlwriter`, `dom` e `simplexml`.

### SQLite `unable to open database file`

Garanta que o processo PHP/Apache possa escrever em `storage/`.

### Upload rejeitado

Confira:

```env
MAX_UPLOAD_MB=30
```

E os limites PHP/Apache. A imagem Docker do projeto configura `upload_max_filesize=32M` e `post_max_size=34M`.

### PPV importado, mas cobertura abaixo de 100%

Isso normalmente significa que existem modelos `AMBIGUOUS` ou `UNRESOLVED`. Não trate isso automaticamente como erro de parsing: primeiro veja as pendências técnicas.

### Estrutura do PPV não reconhecida

O parser deve emitir diagnóstico. Corrija o detector semanticamente e adicione teste para o novo padrão; não faça fallback por letras fixas de coluna.

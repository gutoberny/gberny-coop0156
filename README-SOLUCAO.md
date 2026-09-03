# Solução — Desafio Coop0156

As 4 etapas obrigatórias e os 2 diferenciais opcionais. **73 testes passando** (235 asserções).

| Etapa | |
|---|---|
| 1. CRUD de clientes | Completa |
| 2. Bureau + regras de negócio | Completa |
| 3. Simulação e contratação | Completa |
| 4. Testes automatizados | Completa |
| ⭐ Filas | Implementado |
| ⭐ Vá além | Painel com menu lateral, identidade Sicredi, listagens paginadas |

---

## Como executar

```bash
# 1. Dependências (use php84: o Laravel 13 exige property hooks do PHP 8.4)
docker run --rm -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs

# 2. Ambiente — a chave é gerada ANTES de subir o Sail, que roda sem auto-reload
cp .env.example .env
docker run --rm -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" -w /var/www/html \
    laravelsail/php84-composer:latest \
    php artisan key:generate

# 3. Subir
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate

# Acesse http://localhost
./vendor/bin/sail artisan queue:work   # necessário para concluir contratações
./vendor/bin/sail artisan test
```

> **Porta 80 ocupada?** Defina `APP_PORT=8000` e `APP_URL=http://localhost:8000` no `.env` antes do `sail up`. Não altere `SCORE_BUREAU_API_URL` — é o endereço interno do container.
>
> **Mudou o `.env` com o Sail rodando?** `./vendor/bin/sail restart`.

---

## Telas

O enunciado descrevia uma tela única em `resources/views/analise.blade.php`. A interface foi reorganizada em um painel com menu lateral, então **esse arquivo não existe mais** — o formulário de análise virou `solicitacoes/create.blade.php`, sob o layout compartilhado `layouts/app.blade.php`.

| Rota | |
|---|---|
| `/` | Redireciona para `/clientes` |
| `/clientes` | Listagem paginada + acesso ao cadastro |
| `/clientes/novo` | Formulário de cliente |
| `/solicitacoes` | Análises com status, score, taxa e parcela |
| `/solicitacoes/nova` | Formulário de análise, com o resultado ao lado |
| `/simulacao/{id}` | Condições aprovadas + contratação |

Cores e tipografia seguem o [manual da marca](https://marca.sicredi.com.br/cores/): verde `#3FA110` e `#146E37` sobre fundo branco (o manual define o branco como cor primária junto do verde), Exo 2.0 nos títulos e Nunito na interface. Listagens em tabela, com algarismos tabulares nos valores.

---

## Arquitetura

Lógica de negócio fora dos controllers, em três peças isoladas:

| Peça | Responsabilidade |
|---|---|
| [`PoliticaCredito`](app/Support/PoliticaCredito.php) | Regras **puras**: renda, score e valor entram; a decisão sai. Sem banco nem HTTP |
| [`BureauCreditoClient`](app/Services/BureauCreditoClient.php) | Único ponto que fala HTTP com o Bureau |
| [`AnaliseCreditoService`](app/Services/AnaliseCreditoService.php) | Orquestra o caso de uso e persiste |

Os controllers só traduzem HTTP: Form Request na entrada, API Resource na saída. As exceções de domínio implementam `render()`, então o Laravel as converte sozinho — [`BureauIndisponivelException`](app/Exceptions/BureauIndisponivelException.php) em **503** e [`AnaliseNaoContratavelException`](app/Exceptions/AnaliseNaoContratavelException.php) em **422** — sem `try/catch` nos controllers e sem 500 inesperado.

**Resiliência do Bureau** — as três falhas do mock viram 503, com a análise permanecendo em `pendente` para nova tentativa:

| Cenário | CPF termina em | Tratamento |
|---|---|---|
| Timeout | `5` (delay de 5s) | `ConnectionException`, timeout de 3s |
| Erro HTTP | `4` (HTTP 500) | `$resposta->failed()` |
| Payload sem `score` | `6` | `is_numeric($score)` |

---

## Decisões

**E-mail na criação automática do cliente.** O enunciado exige `email` obrigatório e único no CRUD, mas o payload do `solicitar` não o inclui — e a coluna é `NOT NULL UNIQUE`. Optei por não afrouxar o schema: tornar a coluna nullable contradiria o `required` do CRUD e deixaria vários `NULL` furarem o `unique`. Em vez disso, `email` é opcional no `solicitar` e, quando ausente, derivado do CPF. A interface mostra "E-mail não informado" nesses casos, em vez do endereço sintético. Nenhuma migration foi alterada.

**Ordem das regras.** O Bureau é consultado sempre, na ordem numerada do enunciado, então o `score` fica registrado mesmo quando a reprovação vem da renda.

**Contratação assíncrona e idempotente.** `contratar` move para `processando_contratacao` e despacha o job, que finaliza em `contratado`. O job ignora análises já contratadas ou inexistentes, com 3 tentativas e espera progressiva.

**`valor_total`** deriva do valor solicitado e da taxa, não da parcela arredondada — bate com o exemplo do enunciado (R$ 13.480,00, não R$ 13.479,96).

**Endpoint adicionado.** Não havia `GET /api/analise-credito`; criei o index paginado com filtro `?cliente_id=`, que a tela de solicitações consome.

---

## Correções no scaffold

1. **URL do Bureau apontava para rota inexistente** (`/api/mock/score` em vez de `/api/mock/bureau/{cpf}`) — nenhuma consulta funcionaria.
2. **Essa URL é interna, não pública.** No Sail a aplicação escuta na porta 80 do container mesmo publicada em outra porta no host, então derivá-la do `APP_URL` quebra a consulta.
3. **O servidor travava na autochamada.** O mock é servido pela própria aplicação, que precisa atender uma requisição aninhada — com um worker só, todo cenário de sucesso retornava 503. Como `artisan serve` só respeita `PHP_CLI_SERVER_WORKERS` junto de `--no-reload`, o `compose.yaml` redefine `SUPERVISOR_PHP_COMMAND`. O custo é perder o auto-reload do `.env`.
4. **Imagem Docker do enunciado.** `laravelsail/php83-composer` não executa o Laravel 13 (`syntax error, unexpected token "{"` em `Request.php`). Use `php84`.

`MockBureauController` e as migrations entregues estão byte a byte idênticos ao scaffold.

---

## Testes

```bash
./vendor/bin/sail artisan test
```

Nenhuma chamada real de rede: os cenários do Bureau usam `Http::fake()`, e `Http::preventStrayRequests()` falha o teste se alguma requisição escapar.

| Arquivo | Testes | Cobertura |
|---|---|---|
| [`AnaliseCreditoTest`](tests/Feature/AnaliseCreditoTest.php) | 30 | Faixas de score, as três reprovações, os três modos de falha do Bureau, validação, criação e reaproveitamento do cliente, listagem, ciclo de contratação |
| [`ClienteTest`](tests/Feature/ClienteTest.php) | 18 | Os 10 casos do enunciado + formato de CPF, renda negativa, `per_page`, unicidade na atualização, mensagens em português |
| [`PoliticaCreditoTest`](tests/Unit/PoliticaCreditoTest.php) | 16 | Fronteiras exatas: score 399/400/699/700, renda 1499,99/1500, parcela em exatamente 30% |
| [`NavegacaoTest`](tests/Feature/NavegacaoTest.php) | 9 | Telas do painel e proteção da simulação por status |

A contratação é coberta pelos dois lados de propósito: com `Queue::fake()` (verifica o despacho e o estado `processando_contratacao`) e sem (com `QUEUE_CONNECTION=sync` o job roda inline e chega a `contratado`).

# Solução — Desafio Coop0156

Implementação das 4 etapas obrigatórias e dos 2 diferenciais opcionais.

**Status:** 63 testes automatizados passando (181 asserções), fluxo validado end-to-end no navegador e via API, incluindo o worker de fila real.

---

## O que foi implementado

| Etapa | Situação |
|---|---|
| 1. CRUD de Clientes | Completa |
| 2. Integração com o Bureau e regras de negócio | Completa |
| 3. Tela de simulação e contratação | Completa |
| 4. Testes automatizados | Completa (63 testes) |
| ⭐ Diferencial — Filas | Implementado |
| ⭐ Diferencial — Vá além | Tela de clientes, prévia da parcela, máscara de CPF, feedback de erro por campo |

Nada do escopo obrigatório ficou de fora.

---

## Como executar

### Sail (recomendado)

```bash
# 1. Dependências. Atenção: a imagem php83 do README original NÃO funciona —
#    o Laravel 13 usa property hooks do PHP 8.4 (veja "Ajustes no scaffold").
docker run --rm -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs

# 2. Ambiente. A chave é gerada ANTES de subir o Sail: o servidor roda com
#    --no-reload (necessário para os workers), então não recarrega o .env.
cp .env.example .env
docker run --rm -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" -w /var/www/html \
    laravelsail/php84-composer:latest \
    php artisan key:generate

# 3. Subir e migrar
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate

# Acesse http://localhost
./vendor/bin/sail artisan queue:work   # necessário para concluir contratações
./vendor/bin/sail artisan test
```

> **Porta 80 ocupada?** Defina `APP_PORT=8000` e `APP_URL=http://localhost:8000` no `.env`
> **antes** do `sail up -d`. Deixe `SCORE_BUREAU_API_URL` como está — ela é o endereço
> *interno* do container, independente da porta publicada no host (veja abaixo).
>
> **Alterou o `.env` com o Sail já rodando?** Rode `./vendor/bin/sail restart`. O
> `--no-reload` troca o recarregamento automático pela concorrência que o mock exige.

### PHP local

```bash
composer install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite && php artisan migrate

# A URL do Bureau precisa apontar para a porta do artisan serve:
# SCORE_BUREAU_API_URL=http://localhost:8000/api/mock/bureau
php artisan serve
php artisan queue:work
php artisan test
```

---

## Arquitetura

O critério de avaliação pede lógica de negócio fora do controller. A separação ficou em três peças com responsabilidade única:

| Peça | Responsabilidade | Depende de |
|---|---|---|
| [`PoliticaCredito`](app/Support/PoliticaCredito.php) | Motor de regras **puro**: renda, score e valor entram; a decisão sai. | nada |
| [`BureauCreditoClient`](app/Services/BureauCreditoClient.php) | Único ponto que fala HTTP com o Bureau. | `Http`, config |
| [`AnaliseCreditoService`](app/Services/AnaliseCreditoService.php) | Orquestra o caso de uso e persiste o resultado. | as duas acima |

Os controllers ([`ClienteController`](app/Http/Controllers/ClienteController.php), [`AnaliseCreditoController`](app/Http/Controllers/AnaliseCreditoController.php)) apenas traduzem HTTP: validam via Form Request, delegam e serializam via API Resource.

A `PoliticaCredito` não toca banco nem rede, então é testada como unidade pura em [`PoliticaCreditoTest`](tests/Unit/PoliticaCreditoTest.php) — sem bootstrap do framework, incluindo as fronteiras exatas das faixas.

### Exceções que se traduzem em status code

Em vez de `try/catch` espalhado nos controllers, as exceções de domínio implementam `render()`, que o Laravel chama automaticamente:

- [`BureauIndisponivelException`](app/Exceptions/BureauIndisponivelException.php) → **503** com mensagem limpa.
- [`AnaliseNaoContratavelException`](app/Exceptions/AnaliseNaoContratavelException.php) → **422** com o status atual da análise.

O controller fica sem ruído e nunca há um 500 inesperado.

### Resiliência da integração

Os três modos de falha do mock são tratados e convertidos em 503, mantendo a análise em `pendente` para permitir nova tentativa:

| Cenário | CPF terminado em | Tratamento |
|---|---|---|
| Timeout / indisponibilidade | `5` (delay de 5s) | `ConnectionException` capturada; timeout de 3s |
| Erro HTTP | `4` (HTTP 500) | `$resposta->failed()` |
| Payload malformado | `6` (sem `score`) | validação de `is_numeric($score)` |

Toda falha gera `Log::warning` com CPF, URL chamada e contexto.

---

## Decisões técnicas

**E-mail na criação automática do cliente.** O enunciado exige `email` obrigatório e único no CRUD, mas o payload do `solicitar` não o inclui — e a coluna é `NOT NULL UNIQUE`. Optei por **não afrouxar o schema**: tornar a coluna nullable contradiria o `required` do CRUD e permitiria múltiplos `NULL` furando o `unique`. Em vez disso, `email` é **opcional** no `solicitar` (campo adicionado ao formulário) e, quando ausente, derivado deterministicamente do CPF (`{cpf}@nao-informado.coop0156.local`), garantindo que o mesmo CPF nunca gere cliente duplicado. Nenhuma migration foi alterada.

**Ordem das regras.** O Bureau é consultado sempre, seguindo o fluxo numerado do enunciado (persistir `pendente` → consultar Bureau → aplicar regras). O `score` fica registrado mesmo quando a reprovação vem da renda, o que é mais útil para auditoria e para a tela. Um curto-circuito na renda economizaria a chamada externa, mas deixaria o score nulo e desviaria da ordem descrita.

**Botão da tela inicial.** O scaffold já trazia um botão de contratação direta na home, mas o enunciado pede que a aprovação leve o usuário a `/simulacao/{id}`. Converti o botão em CTA de redirecionamento, concentrando a contratação numa única tela. O card de sucesso que sobrou sem uso na home foi removido.

**Cliente recorrente.** Se um CPF já cadastrado solicita nova análise com renda diferente, o cadastro é atualizado — a renda informada agora é o dado mais recente.

**Job idempotente.** O `ProcessarContratacaoJob` ignora análises já contratadas e análises inexistentes, então um reprocessamento do worker não corrompe o estado. Tem 3 tentativas com espera progressiva e log de falha definitiva.

**`valor_total`.** Derivado do valor solicitado e da taxa, não da parcela arredondada, para bater com o exemplo do enunciado (R$ 13.480,00 em vez de 12 × R$ 1.123,33 = R$ 13.479,96).

---

## Ajustes no scaffold

Três problemas encontrados no material entregue, todos corrigidos:

**1. URL do Bureau apontava para uma rota inexistente.** `config/services.php` trazia `/api/mock/score`, mas a rota real é `/api/mock/bureau/{cpf}` — nenhuma consulta funcionaria.

**2. A URL do Bureau é interna, não pública.** Como a aplicação chama a si mesma, `SCORE_BUREAU_API_URL` precisa ser o endereço de dentro do container. No Sail a aplicação escuta na porta 80 do container ainda que publicada em outra porta no host, então derivar essa URL do `APP_URL` quebra a consulta. O valor é explícito e documentado no `.env.example`.

**3. O servidor de desenvolvimento travava na autochamada.** Como o mock é servido pela própria aplicação, ela precisa atender uma requisição aninhada — com um único worker o servidor bloqueia e a consulta estoura o timeout, fazendo todo cenário de sucesso retornar 503. O `artisan serve` só respeita `PHP_CLI_SERVER_WORKERS` junto de `--no-reload`, então o `compose.yaml` redefine `SUPERVISOR_PHP_COMMAND` com essa flag. O custo é perder o recarregamento automático em mudanças no `.env` — por isso a chave da aplicação é gerada antes de subir o Sail, e alterações posteriores no `.env` pedem `sail restart`.

**Bônus — imagem Docker do README.** O comando de instalação do README original usa `laravelsail/php83-composer`, que não consegue executar o Laravel 13 (o framework usa *property hooks* do PHP 8.4): `syntax error, unexpected token "{"` em `Request.php`. Use `laravelsail/php84-composer`.

Além disso, `phpunit.xml` passou a usar SQLite **em memória** — antes a suíte criava um arquivo `testing` residual na raiz do projeto.

Os arquivos que o enunciado pede para não alterar (`MockBureauController`) e as migrations entregues estão **byte a byte idênticos** ao scaffold.

---

## Testes

```bash
./vendor/bin/sail artisan test
```

**63 testes, 181 asserções.** Nenhuma chamada real de rede: todos os cenários do Bureau usam `Http::fake()`, e `Http::preventStrayRequests()` garante que uma requisição não simulada falhe o teste em vez de sair para a internet.

| Arquivo | Testes | Cobertura |
|---|---|---|
| [`AnaliseCreditoTest`](tests/Feature/AnaliseCreditoTest.php) | 28 | Faixas de score, as três reprovações, os três modos de falha do Bureau, validação, criação/reaproveitamento do cliente, ciclo completo de contratação, tela de simulação |
| [`ClienteTest`](tests/Feature/ClienteTest.php) | 17 | Os 10 casos do enunciado + formato de CPF, renda negativa, `per_page`, unicidade de e-mail na atualização |
| [`PoliticaCreditoTest`](tests/Unit/PoliticaCreditoTest.php) | 16 | Fronteiras exatas: score 399/400/699/700, renda 1499,99/1500, parcela em exatamente 30% |

Dois testes cobrem a contratação de propósito pelos dois lados: um com `Queue::fake()` (verifica o despacho do Job e o estado `processando_contratacao`) e um sem (com `QUEUE_CONNECTION=sync` o Job roda inline e a análise chega a `contratado`).

---

## Verificação manual

Validado no navegador em `http://localhost` com `queue:work` ativo:

| CPF | Resultado observado |
|---|---|
| `90000000003` (score 850), renda 10.000, valor 10.000 | Aprovado, 2,9%, parcela R$ 1.123,33, total R$ 13.480,00 → CTA para a simulação → contratação → `contratado` no log do Job |
| `90000000002` (score 550) | Aprovado, 4,5%, parcela R$ 1.283,33 |
| `90000000001` (score 150) | Reprovado — score muito baixo |
| renda 1.000 | Reprovado — renda insuficiente (score 850 registrado) |
| renda 3.000 / valor 100.000 | Reprovado — comprometimento acima de 30% |
| `93000000004` / `93000000005` / `93000000006` | 503 com mensagem limpa (erro HTTP, timeout, payload sem score) |

Contratação repetida retorna 422; análise inexistente, 404.

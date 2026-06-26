# BUG_TRACKER.md — Indique e Ganhe

**Data:** 26/06/2026  
**Fase:** 0 — Diagnóstico (bugs identificados, não corrigidos nesta fase)

Legenda de severidade: **P0** crítico · **P1** alto · **P2** médio · **P3** baixo

---

## P0 — Bloqueadores de produção

### BUG-001 — Router não suporta parâmetros dinâmicos `{id}`

**Severidade:** P0  
**Arquivo:** `core/Router.php`, `config/routes.php`  
**Descrição:** Rotas registradas com `{id}` (ex.: `/admin/campanhas/editar/{id}`) fazem match **literal**. Qualquer URL real como `/admin/campanhas/editar/3` retorna 404.  
**Impacto:** CRUD admin de campanhas, visualização de validações, cupons e AppsFlyer **inoperantes**.  
**Rotas afetadas:**
- `/admin/validacoes/{id}`
- `/admin/cupons/{id}`
- `/admin/appsflyer/{id}`
- `/admin/campanhas/editar/{id}` (+ POST)
- `/admin/campanhas/ativar/{id}` (+ desativar, duplicar, excluir)

---

### BUG-002 — Middleware ApiAuth não registrado no Router

**Severidade:** P0  
**Arquivo:** `core/Router.php` linhas 10–17, `config/routes.php` linha 61  
**Descrição:** `routes.php` passa 4º argumento `'ApiAuth'` para `$router->post(...)`, mas `Router::post()` aceita apenas 3 parâmetros. Em PHP 8+ isso gera `ArgumentCountError` ao carregar rotas **ou** silenciosamente ignora o middleware (dependendo de versão/config).  
**Impacto:** API KOBE `/api/indicacao/confirmar-cadastro` sem autenticação ou com fatal error no boot.

---

### BUG-003 — ApiAuth e dependências não carregados no bootstrap

**Severidade:** P0  
**Arquivo:** `config/bootstrap.php`, `middleware/ApiAuth.php`  
**Descrição:** `ApiAuth` não está em `require_once` do bootstrap. Mesmo com Router corrigido, `new ApiAuth()` falharia com class not found.  
**Impacto:** API KOBE não funcional.

---

### BUG-004 — ApiIndicacaoController incompatível com model Indicado

**Severidade:** P0  
**Arquivo:** `controllers/ApiIndicacaoController.php`, `models/Indicado.php`  
**Descrição:** Controller passa campos inexistentes no model:
- `usuario_id` → model espera `usuario_indicador_id`
- `motivo`, `customer_id_vtex`, `appsflyer_id`, `tipo_evento`, `plataforma` → colunas não existem na tabela/migration
- Usa `Indicado::STATUS_EM_ANALISE` → constante **não definida** no model  
**Impacto:** Endpoint KOBE falha em runtime (SQL error ou undefined constant).

---

## P1 — Funcionalidade quebrada ou risco alto

### BUG-005 — IndicadosController usa métodos inexistentes no Validator

**Severidade:** P1  
**Arquivo:** `controllers/IndicadosController.php` linhas 164–186  
**Descrição:** Chama `Validator::sanitizeEmail()`, `validateCpf()`, `validateTelefone()`, `validateEmail()` — métodos não existem. Validator expõe `email()`, `cpf()`, `telefone()`, `sanitizeString()`.  
**Impacto:** Fluxo `/cadastro-indicado` → fatal error no POST.

---

### BUG-006 — IndicadosController usa RateLimiter incorretamente

**Severidade:** P1  
**Arquivo:** `controllers/IndicadosController.php` linha 153–156  
**Descrição:** Instancia `new RateLimiter()` e chama `attempt()` — classe só tem métodos estáticos `tooManyAttempts()`, `hit()`, `clear()`.  
**Impacto:** Rate limit do cadastro de indicado quebrado.

---

### BUG-007 — IndicadosController::index cria registro sem campos obrigatórios

**Severidade:** P1  
**Arquivo:** `controllers/IndicadosController.php` linhas 54–59  
**Descrição:** `Indicado::create()` exige `nome`, `cpf`, `telefone`, `email` (NOT NULL no schema), mas create no index só passa codigo, usuario_indicador_id, status, origem.  
**Impacto:** SQL error se fluxo index fosse roteado (hoje sem rota, mas participar/salvar também afetados indiretamente).

---

### BUG-008 — VTEXCouponProvider::generateCode retorno inválido

**Severidade:** P1  
**Arquivo:** `services/VTEXCouponProvider.php`  
**Descrição:** Assinatura `generateCode(...): string` retorna `array`. Viola contrato `CouponProviderInterface`.  
**Impacto:** Type error se provider VTEX for selecionado.

---

### BUG-009 — CampanhasController::edit espera parâmetro `$id` nunca injetado

**Severidade:** P1  
**Arquivo:** `controllers/CampanhasController.php`, `core/Router.php`  
**Descrição:** Métodos `edit(int $id)`, `activate(int $id)` etc. requerem ID, mas Router chama `$instance->{$action}()` sem argumentos.  
**Impacto:** Relacionado a BUG-001; mesmo com rotas dinâmicas, invoke precisa passar parâmetros.

---

### BUG-010 — AdminController usa slug de campanha como código de indicador

**Severidade:** P1  
**Arquivo:** `controllers/AdminController.php` linha 39  
**Descrição:** `$indicadoModel->listByIndicador($campanhaAtiva['slug'])` — slug da campanha ≠ codigo_indicador do usuário.  
**Impacto:** Estatísticas de campanha no admin dashboard incorretas (sempre zero ou dados errados).

---

### BUG-011 — Duplicidade de fluxo de convite (ConviteController vs IndicadosController)

**Severidade:** P1  
**Arquivo:** múltiplos  
**Descrição:** Dois fluxos paralelos com sessões diferentes (`indicacao_ref` vs `referral_code`), views diferentes, lógicas distintas. Apenas ConviteController está roteado para landing principal.  
**Impacto:** Confusão de manutenção; comportamento inconsistente para indicados.

---

### BUG-012 — IndicadosController::salvar associa indicado ao userId errado

**Severidade:** P1  
**Arquivo:** `controllers/IndicadosController.php` linha 272–274  
**Descrição:** Após criar usuário indicado, faz `update(..., ['usuario_indicador_id' => $userId])` onde `$userId` é o **indicado**, não o indicador.  
**Impacto:** Relacionamento indicador↔indicado corrompido no banco.

---

## P2 — Médio

### BUG-013 — Migrations conflitantes para `campanhas` e `validacoes`

**Severidade:** P2  
**Arquivo:** `database/migration_etapa6.sql` vs `migration_etapa13.sql`; `migration_etapa7.sql` vs `migration_etapa12.sql`  
**Descrição:** Mesmas tabelas criadas com schemas diferentes ao longo das etapas. Banco real depende de ordem de execução manual.  
**Impacto:** Ambientes divergentes; models assumem schema da etapa mais recente.

---

### BUG-014 — Tabela `validacao_indicacoes` (etapa 10) vs `validacoes` (etapa 13)

**Severidade:** P2  
**Descrição:** Dois motores de validação coexistem. `ValidationService` usa `validacoes`; `ValidacaoIndicacao` model existe mas integração incompleta.  
**Impacto:** Dados fragmentados; confusão operacional.

---

### BUG-015 — Eventos duplicados: `eventos` vs `eventos_indicacao`

**Severidade:** P2  
**Descrição:** Dois sistemas de log paralelos. `EventLogger` usa `eventos`; `IndicadosController` também grava em `eventos_indicacao`.  
**Impacto:** Auditoria incompleta se consultar apenas uma tabela.

---

### BUG-016 — `admin/index.php` e `api/index.php` são stubs obsoletos

**Severidade:** P2  
**Descrição:** Retornam 503 / JSON "em construção" se acessados diretamente, embora rotas reais existam em `index.php`.  
**Impacto:** Confusão no deploy; possível configuração errada de vhost apontando para subpastas.

---

### BUG-017 — Recuperação de senha não envia e-mail

**Severidade:** P2  
**Arquivo:** `controllers/AuthController.php`  
**Descrição:** Token gerado e logado; link só aparece em flash se `APP_DEBUG=true`. Sem integração SMTP.  
**Impacto:** Usuários não recuperam senha em produção.

---

### BUG-018 — Links persistidos com domínio antigo após migração

**Severidade:** P2  
**Arquivo:** `models/LinkIndicacao.php`  
**Descrição:** Coluna `url` grava URL absoluta na criação. Dashboard pode exibir link antigo mesmo após trocar `APP_URL`.  
**Impacto:** Compartilhamento com domínio nexden até UPDATE no banco ou regenerar links.

---

### BUG-019 — Validacao::statusIcon encoding corrompido

**Severidade:** P3  
**Arquivo:** `models/Validacao.php` linha 178  
**Descrição:** Emoji `EM_ANALISE` exibe caractere corrompido ``.  
**Impacto:** UX admin degradada.

---

### BUG-020 — Campanha::activate usa `$this->db->exec()` sem prepared statement

**Severidade:** P3  
**Arquivo:** `models/Campanha.php` linha 160  
**Descrição:** String concatenada (mesmo sendo constante interna). Padrão inconsistente.  
**Impacto:** Baixo risco imediato; inconsistência de código.

---

## P3 — Baixo / código morto

### BUG-021 — IndicadosController::index sem rota

**Severidade:** P3  
**Descrição:** Método `index()` implementado mas não registrado em `routes.php`.  
**Impacto:** Código morto.

---

### BUG-022 — Views admin órfãs

**Severidade:** P3  
**Arquivos:** `views/admin/validacao.php` (vs `validacoes.php`)  
**Descrição:** Possível view substituída sem remoção.

---

### BUG-023 — AUDIT_ERRORS.md afirma "Middleware funcionando" incorretamente

**Severidade:** P3  
**Descrição:** Documento interno contradiz estado real do Router (BUG-002).  
**Impacto:** Falsa sensação de prontidão.

---

## Matriz de priorização para Fase 1

| Ordem | Bug | Esforço estimado |
|-------|-----|------------------|
| 1 | BUG-001 + BUG-009 | Médio — implementar route params no Router |
| 2 | BUG-002 + BUG-003 | Baixo — estender get/post + bootstrap |
| 3 | BUG-004 | Médio — alinhar API com schema Indicado |
| 4 | BUG-005 + BUG-006 | Baixo — corrigir chamadas Validator/RateLimiter |
| 5 | BUG-012 | Baixo — fix lógica usuario_indicador_id |
| 6 | BUG-010 | Baixo — fix query admin stats |
| 7 | BUG-018 | Baixo — migration SQL domínio |
| 8 | BUG-011 | Alto — unificar fluxo convite (decisão arquitetural) |

---

## Testes de validação pendentes (pós-correção)

- [ ] Todas rotas admin com ID retornam 200
- [ ] API KOBE rejeita sem Bearer token
- [ ] API KOBE cria indicado com payload válido
- [ ] Cadastro indicado web (`/salvar-indicado`) completa sem fatal error
- [ ] Links de convite usam domínio minasmais após migração
- [ ] Login/logout/CSRF/remember-me
- [ ] Soft delete impede login de conta excluída

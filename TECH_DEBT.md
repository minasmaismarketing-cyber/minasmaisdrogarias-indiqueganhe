# TECH_DEBT.md — Indique e Ganhe

**Data:** 26/06/2026  
**Fase:** 0 — Diagnóstico

---

## 1. Infraestrutura e arquitetura

### TD-001 — Ausência de autoloader (Composer / PSR-4)

**Prioridade:** Alta  
**Descrição:** Bootstrap com ~20 `require_once` manuais. Services, repositories, enums e middleware não são carregados centralmente.  
**Risco:** Fatal errors intermitentes; difícil escalar número de classes.  
**Recomendação:** Adicionar Composer com PSR-4 autoload para `App\` namespaces.

---

### TD-002 — Router primitivo sem parâmetros, grupos ou named routes

**Prioridade:** Crítica  
**Descrição:** Match exato de string; rotas `{id}` declaradas mas não implementadas.  
**Recomendação:** Implementar regex/pattern matching ou query string fallback temporário.

---

### TD-003 — Middleware parcialmente implementado

**Prioridade:** Alta  
**Descrição:** `addRoute` suporta middleware internamente, mas API pública `get()`/`post()` não expõe. ApiAuth isolado.  
**Recomendação:** Unificar: `$router->post($path, $controller, $action, $middleware)`.

---

### TD-004 — Entradas legadas admin/ e api/

**Prioridade:** Média  
**Descrição:** `admin/index.php` (503) e `api/index.php` (stub JSON) obsoletos.  
**Recomendação:** Remover ou redirecionar para front controller; documentar vhost único.

---

## 2. Banco de dados e models

### TD-005 — Migrations incrementais conflitantes

**Prioridade:** Alta  
**Descrição:** `campanhas`, `validacoes` e `indicados` redefinidos em múltiplas etapas sem migration de consolidação.  
**Recomendação:** Criar `schema_consolidated.sql` + script de diff para produção.

---

### TD-006 — Dualidade indicacoes vs indicados

**Prioridade:** Alta  
**Descrição:** Tabela `indicacoes` (legado etapas 3–4) coexiste com `indicados` (etapa 8+). Dashboard consulta ambas.  
**Recomendação:** Definir fonte única de verdade; migrar dados; deprecar tabela legada.

---

### TD-007 — Dualidade eventos vs eventos_indicacao

**Prioridade:** Média  
**Descrição:** Dois sistemas de auditoria paralelos.  
**Recomendação:** Consolidar em `eventos` com tipos padronizados.

---

### TD-008 — validacao_indicacoes órfã

**Prioridade:** Média  
**Descrição:** Tabela e model `ValidacaoIndicacao` criados na etapa 10, substituídos parcialmente por `validacoes` etapa 13.  
**Recomendação:** Remover ou integrar ao ValidationService.

---

### TD-009 — URLs absolutas persistidas em links_indicacao

**Prioridade:** Média  
**Descrição:** Domínio baked-in no INSERT.  
**Recomendação:** Persistir apenas slug/código; gerar URL em runtime via `url()`.

---

### TD-010 — Foreign keys ausentes em tabelas recentes

**Prioridade:** Média  
**Descrição:** `validacoes`, `cupons`, `appsflyer_events`, `historico_*` sem FKs declaradas nas migrations 13–16.  
**Recomendação:** Adicionar constraints para integridade referencial.

---

## 3. Código e padrões

### TD-011 — requireAdmin() duplicado em 5 controllers

**Prioridade:** Média  
**Controllers:** Admin, Campanhas, Validacao, Cupons, AppsFlyer  
**Recomendação:** Extrair `AdminMiddleware` ou trait/base `AdminController`.

---

### TD-012 — Autorização admin por email hardcoded

**Prioridade:** Alta (segurança)  
**Descrição:** `admin@minasmais.com.br` em constante PHP.  
**Recomendação:** Coluna `role` ou `is_admin` em `usuarios`; config via env.

---

### TD-013 — Fluxos de convite duplicados

**Prioridade:** Alta  
**Descrição:** ConviteController + IndicadosController com lógicas e sessões diferentes.  
**Recomendação:** Unificar em um fluxo; deprecar controller/views redundantes.

---

### TD-014 — Inconsistência Validator API

**Prioridade:** Alta  
**Descrição:** IndicadosController usa métodos inexistentes; regras de senha diferem (6 vs 8 chars).  
**Recomendação:** Padronizar Validator; aliases deprecated se necessário.

---

### TD-015 — RateLimiter dual (estático vs instância esperada)

**Prioridade:** Média  
**Recomendação:** API unificada com `attempt($key, $max, $window)`.

---

### TD-016 — VTEXCouponProvider quebra contrato de interface

**Prioridade:** Média  
**Recomendação:** Corrigir return type ou renomear método.

---

### TD-017 — Controllers avançados sem testes de integração

**Prioridade:** Média  
**Descrição:** Etapas 13–16 adicionadas rapidamente; AUDIT_ERRORS.md marca OK sem validar Router.  
**Recomendação:** Smoke test automatizado de rotas.

---

## 4. Operações e deploy

### TD-018 — README desatualizado (domínio nexden)

**Prioridade:** Baixa  
**Recomendação:** Atualizar na Fase 1 de migração.

---

### TD-019 — schema.hostinger.sql referencia banco antigo

**Prioridade:** Baixa  
**Descrição:** Comentário `u352670812_indice`.  
**Recomendação:** Atualizar para `u146248277_indicacao`.

---

### TD-020 — Sem log rotation

**Prioridade:** Baixa  
**Descrição:** `uploads/logs/app.log` cresce indefinidamente.  
**Recomendação:** Cron ou rotação por tamanho.

---

### TD-021 — Sem envio de e-mail (SMTP)

**Prioridade:** Alta (produto)  
**Descrição:** Recuperação de senha não funcional em produção.  
**Recomendação:** Integrar SMTP Hostinger ou serviço transacional.

---

### TD-022 — APP_DEBUG e credenciais em .env local

**Prioridade:** Alta (segurança operacional)  
**Descrição:** `.env` presente no workspace (gitignored). Garantir que nunca entre em repositório.  
**Recomendação:** Validar `.gitignore`; usar secrets no painel Hostinger.

---

## 5. Performance

### TD-023 — Bootstrap carrega models não usados em toda requisição

**Prioridade:** Baixa  
**Descrição:** 10+ models no bootstrap mesmo para `/login`.  
**Recomendação:** Lazy load pós-autoload.

---

### TD-024 — N+1 potencial em listagens admin

**Prioridade:** Baixa  
**Descrição:** `Validacao::findAll()` com JOINs OK; timeline de indicados faz loop com queries.  
**Recomendação:** Batch fetch timelines.

---

### TD-025 — CSS monolítico (~3700 linhas)

**Prioridade:** Baixa  
**Descrição:** Arquivo único sem minificação/build.  
**Recomendação:** Aceitável no curto prazo; considerar purge para produção.

---

## 6. UX / Frontend

### TD-026 — Branding footer "NEXDEN Digital"

**Prioridade:** Baixa (produto)  
**Descrição:** Footer em login, cadastro, home, convite ainda referencia desenvolvedor anterior.  
**Recomendação:** Substituir por Minas Mais se desejado pelo cliente.

---

### TD-027 — admin/index.php 503 vs admin funcional

**Prioridade:** Média  
**Descrição:** Pode confundir equipe de deploy.  
**Recomendação:** Remover stub.

---

## 7. Resumo quantitativo

| Categoria | Itens | Críticos/Altos |
|-----------|-------|-----------------|
| Infraestrutura | 4 | 3 |
| Banco/Models | 6 | 3 |
| Código | 7 | 4 |
| Operações | 5 | 2 |
| Performance | 3 | 0 |
| UX | 2 | 0 |
| **Total** | **27** | **12** |

---

## 8. Ordem sugerida de pagamento

1. Router + middleware (TD-002, TD-003) — desbloqueia admin e API
2. Consolidação schema DB (TD-005) — ambiente previsível
3. Autoload Composer (TD-001) — base para manutenção
4. Unificação fluxo convite (TD-013) — reduz bugs
5. Admin RBAC (TD-012) — segurança
6. SMTP (TD-021) — feature essencial produção
7. Demais itens médios/baixos

---

## TODO — Backlog funcional

- **Futuro:** implementar histórico de participação por hash do CPF para prevenção de fraude em campanhas.

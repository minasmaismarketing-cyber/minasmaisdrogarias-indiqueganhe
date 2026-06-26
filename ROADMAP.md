# ROADMAP.md — Indique e Ganhe

**Empresa:** Drogaria e Perfumaria Minas Mais  
**Domínio alvo:** https://indique.minasmaisdrogarias.com.br  
**Banco alvo:** u146248277_indicacao  
**Data:** 26/06/2026

---

## Visão do produto

Plataforma de indicação onde clientes Minas Mais compartilham códigos, indicados se cadastram, passam por validação (app/e-commerce) e recebem cupons/prêmios — com rastreamento via AppsFlyer e confirmação via app KOBE + VTEX.

---

## Estado atual (Fase 0 — concluída)

| Área | Status |
|------|--------|
| Auth (login/cadastro/sessão) | ✅ Funcional |
| Dashboard e compartilhamento | ✅ Funcional (domínio depende APP_URL) |
| Convite web (ConviteController) | ✅ Funcional |
| Perfil e soft delete | ✅ Implementado |
| Admin básico | ⚠️ Parcial — rotas com {id} quebradas |
| Validação admin | ⚠️ Código pronto, router bloqueia |
| Cupons | ⚠️ Código pronto, router bloqueia |
| API KOBE | ❌ Middleware/model quebrados |
| AppsFlyer | 🟡 Preparação (schema + admin UI) |
| VTEX | 🟡 Stubs apenas |
| Migração domínio | ❌ Pendente |
| Migração banco novo | ❌ Pendente |

---

## FASE 1 — Estabilização e migração (obrigatória)

**Objetivo:** Sistema operacional em produção no novo domínio e banco.

### 1.1 Migração de hospedagem

- [x] Configurar subdomínio `indique.minasmaisdrogarias.com.br` → `public_html` (ou pasta do projeto)
- [x] Atualizar `.env` produção (APP_URL, DB_*)
- [ ] Importar schema: executar migrations etapa 2→16 em ordem no banco novo
- [ ] Migrar dados do banco antigo (se houver produção ativa)
- [ ] SQL: revisar e executar `database/migration_domain_links.sql`
- [x] Validar `.htaccess` e SSL (HTTPS) — config local pronta; validar no deploy
- [x] Atualizar referências nexden no README

### 1.2 Correções bloqueadoras (ver BUG_TRACKER)

- [ ] Implementar parâmetros dinâmicos no Router
- [ ] Corrigir registro de middleware ApiAuth
- [ ] Carregar middleware/services no bootstrap ou autoload
- [ ] Corrigir ApiIndicacaoController ↔ Indicado model
- [ ] Corrigir IndicadosController (Validator, RateLimiter, usuario_indicador_id)
- [ ] Corrigir AdminController stats (slug vs codigo)

### 1.3 Smoke tests produção

- [ ] Login / logout / cadastro / convite / dashboard
- [ ] Admin: campanhas CRUD, validações, cupons
- [ ] API KOBE com token
- [ ] Logs em uploads/logs/

**Entregável:** Sistema web core em produção no domínio Minas Mais.

**Estimativa:** 1–2 sprints

---

## FASE 2 — Consolidação arquitetural

**Objetivo:** Reduzir dívida técnica antes das integrações.

- [ ] Composer + PSR-4 autoload
- [ ] `schema_consolidated.sql` único
- [ ] Unificar fluxo convite (deprecar IndicadosController redundante ou roteá-lo)
- [ ] Consolidar `indicacoes` → `indicados` (migração de dados)
- [ ] Consolidar `eventos_indicacao` → `eventos`
- [ ] AdminMiddleware + role em `usuarios`
- [ ] URLs de link geradas em runtime (não persistir domínio)
- [ ] SMTP para recuperação de senha
- [ ] Remover stubs `admin/index.php`, `api/index.php`

**Entregável:** Codebase maintainable; admin RBAC; e-mail funcional.

**Estimativa:** 2 sprints

---

## FASE 3 — Integração KOBE (API)

**Objetivo:** App mobile confirma cadastro de indicados.

- [ ] Corrigir e testar `POST /api/indicacao/confirmar-cadastro`
- [ ] Migration: colunas API em `indicados` (customer_id_vtex, appsflyer_id, tipo_evento, plataforma, motivo)
- [ ] Gerar `API_TOKEN` seguro em produção
- [ ] Documentar URL final: `https://indique.minasmaisdrogarias.com.br/api/indicacao/confirmar-cadastro`
- [ ] Homologação com equipe KOBE
- [ ] Monitoramento via `api_logs`

**Pré-requisitos:** Fase 1 completa.

**Estimativa:** 1 sprint (+ homologação externa)

---

## FASE 4 — Integração AppsFlyer

**Objetivo:** Rastrear installs e atribuição de campanhas.

- [ ] Preencher `config/appsflyer.php` (dev_key, app_ids, onelink)
- [ ] Endpoint webhook público para eventos AppsFlyer
- [ ] Validar assinatura (`AppsFlyerSignatureValidator`)
- [ ] Vincular eventos a `indicacao_id` / `usuario_id`
- [ ] Integrar com motor de validação (`AppsFlyerValidationProvider`)
- [ ] OneLink nos links de convite (`links_indicacao.appsflyer_enabled`)
- [ ] Testes com sandbox AppsFlyer

**Pré-requisitos:** Fase 1; app publicado nas stores.

**Estimativa:** 2 sprints

---

## FASE 5 — Integração VTEX

**Objetivo:** Validar compras e emitir cupons no e-commerce.

- [ ] Credenciais VTEX API (account, app key/token)
- [ ] Implementar `VTEXValidationProvider::validate()` — verificar pedido mínimo
- [ ] Implementar `VTEXCouponProvider` — criar cupom na VTEX
- [ ] Sincronizar status cupom (utilizado/expirado)
- [ ] Webhook ou polling de pedidos (definir arquitetura)
- [ ] Testes em ambiente VTEX QA

**Pré-requisitos:** Fase 3 ou 4 (identificação do cliente); contrato VTEX API.

**Estimativa:** 2–3 sprints

---

## FASE 6 — Notificações e polish

- [ ] WhatsApp (`WhatsAppNotificationProvider`) — cupom liberado, validação
- [ ] Notificações in-app (`notificacoes` table)
- [ ] Log rotation e monitoramento
- [ ] Performance: cache campanha ativa
- [ ] Rebranding footer Minas Mais
- [ ] Testes E2E fluxo completo campanha

**Estimativa:** 1–2 sprints

---

## Cronograma sugerido (alto nível)

```
Fase 0  ████ Diagnóstico                    [CONCLUÍDA]
Fase 1  ████████ Migração + fixes           [Semanas 1-3]
Fase 2  ██████ Consolidação                 [Semanas 4-6]
Fase 3  ████ KOBE API                       [Semanas 7-8]
Fase 4  ██████ AppsFlyer                    [Semanas 9-11]
Fase 5  ████████ VTEX                       [Semanas 12-15]
Fase 6  ████ Polish                         [Semanas 16-17]
```

*Paralelização possível: Fase 4 e 5 parcialmente após Fase 3.*

---

## Critérios de "pronto para produção"

| Critério | Fase mínima |
|----------|-------------|
| Domínio minasmais ativo | Fase 1 |
| Banco novo configurado | Fase 1 |
| Admin campanhas/validação operacional | Fase 1 |
| API KOBE autenticada | Fase 3 |
| AppsFlyer recebendo eventos | Fase 4 |
| Cupons VTEX reais | Fase 5 |
| Recuperação de senha | Fase 2 |

**Conclusão Fase 0:** Sistema **não** está pronto para produção plena; **parcialmente** pronto para MVP web após Fase 1.

---

## Dependências externas

| Integração | Responsável | Bloqueio |
|------------|-------------|----------|
| DNS/SSL Hostinger | Infra Minas Mais | Subdomínio |
| Credenciais DB Hostinger | Infra | Senha nova |
| API_TOKEN KOBE | Dev KOBE + Backend | Acordo de payload |
| AppsFlyer dashboard | Marketing + Dev | App IDs |
| VTEX API | E-commerce Minas Mais | Credenciais + ambiente QA |

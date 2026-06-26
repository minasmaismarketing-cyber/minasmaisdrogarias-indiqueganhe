# ROTAS_TESTADAS.md — Fase 1.2

**Data:** 26/06/2026  
**Escopo:** Resolução de rotas via `Router::resolve()` (sem executar controllers)  
**Script:** `php database/route_test_fase_1_2.php`

**Resumo:** 58 rotas registradas — **58 PASS**, 0 FAIL (validação estática + script de teste)

---

## Rotas estáticas (core — compatibilidade)

| Método | Rota | Status | Controller | Action | Middleware | Params | Resultado |
|--------|------|--------|------------|--------|------------|--------|-----------|
| GET | `/` | PASS | HomeController | index | — | — | OK |
| GET | `/404` | PASS | ErrorController | notFound | — | — | OK |
| GET | `/login` | PASS | AuthController | loginForm | — | — | OK |
| POST | `/login` | PASS | AuthController | login | — | — | OK |
| POST | `/logout` | PASS | AuthController | logout | — | — | OK |
| GET | `/cadastro` | PASS | AuthController | registerForm | — | — | OK |
| POST | `/cadastro` | PASS | AuthController | register | — | — | OK |
| GET | `/esqueci-senha` | PASS | AuthController | forgotForm | — | — | OK |
| POST | `/esqueci-senha` | PASS | AuthController | forgot | — | — | OK |
| GET | `/redefinir-senha` | PASS | AuthController | resetForm | — | — | OK |
| POST | `/redefinir-senha` | PASS | AuthController | reset | — | — | OK |
| GET | `/dashboard` | PASS | DashboardController | index | — | — | OK |
| POST | `/dashboard/compartilhar` | PASS | DashboardController | share | — | — | OK |
| GET | `/perfil` | PASS | ProfileController | index | — | — | OK |
| POST | `/perfil` | PASS | ProfileController | update | — | — | OK |
| POST | `/perfil/senha` | PASS | ProfileController | changePassword | — | — | OK |
| POST | `/perfil/excluir` | PASS | ProfileController | delete | — | — | OK |
| GET | `/indicacoes` | PASS | IndicacoesController | index | — | — | OK |
| GET | `/convite` | PASS | ConviteController | index | — | — | OK |
| POST | `/convite/participar` | PASS | ConviteController | participar | — | — | OK |
| GET | `/meus-premios` | PASS | PremiosController | index | — | — | OK |
| GET | `/configuracoes` | PASS | ConfiguracoesController | index | — | — | OK |
| POST | `/configuracoes` | PASS | ConfiguracoesController | update | — | — | OK |
| GET | `/meus-cupons` | PASS | CuponsController | userIndex | — | — | OK |
| GET | `/notificacoes` | PASS | NotificacoesController | index | — | — | OK |
| GET | `/cadastro-indicado` | PASS | IndicadosController | cadastro | — | — | OK |
| POST | `/salvar-indicado` | PASS | IndicadosController | salvar | — | — | OK |
| POST | `/participar` | PASS | IndicadosController | participar | — | — | OK |
| GET | `/finalizado` | PASS | IndicadosController | finalizado | — | — | OK |

---

## Rotas admin estáticas

| Método | Rota | Status | Controller | Action | Middleware | Params | Resultado |
|--------|------|--------|------------|--------|------------|--------|-----------|
| GET | `/admin` | PASS | AdminController | index | — | — | OK |
| GET | `/admin/usuarios` | PASS | AdminController | usuarios | — | — | OK |
| GET | `/admin/campanhas` | PASS | AdminController | campanhas | — | — | OK |
| GET | `/admin/indicacoes` | PASS | AdminController | indicacoes | — | — | OK |
| GET | `/admin/configuracoes` | PASS | AdminController | configuracoes | — | — | OK |
| GET | `/admin/validacoes` | PASS | ValidacaoController | adminIndex | — | — | OK |
| POST | `/admin/validacoes/iniciar` | PASS | ValidacaoController | start | — | — | OK |
| POST | `/admin/validacoes/aprovar` | PASS | ValidacaoController | approve | — | — | OK |
| POST | `/admin/validacoes/rejeitar` | PASS | ValidacaoController | reject | — | — | OK |
| POST | `/admin/validacoes/cancelar` | PASS | ValidacaoController | cancel | — | — | OK |
| GET | `/admin/cupons` | PASS | CuponsController | adminIndex | — | — | OK |
| POST | `/admin/cupons/cancelar` | PASS | CuponsController | cancel | — | — | OK |
| POST | `/admin/cupons/expirar` | PASS | CuponsController | expire | — | — | OK |
| POST | `/admin/cupons/reativar` | PASS | CuponsController | reactivate | — | — | OK |
| GET | `/admin/appsflyer` | PASS | AppsFlyerController | adminIndex | — | — | OK |
| POST | `/admin/appsflyer/validar` | PASS | AppsFlyerController | validate | — | — | OK |
| POST | `/admin/appsflyer/rejeitar` | PASS | AppsFlyerController | reject | — | — | OK |
| GET | `/admin/campanhas/criar` | PASS | CampanhasController | create | — | — | OK |
| POST | `/admin/campanhas/criar` | PASS | CampanhasController | create | — | — | OK |

---

## Rotas dinâmicas `{id}` (BUG-001 corrigido)

| Método | Rota (exemplo) | Status | Controller | Action | Middleware | Params | Resultado |
|--------|----------------|--------|------------|--------|------------|--------|-----------|
| GET | `/admin/validacoes/12` | PASS | ValidacaoController | view | — | `id=12` | OK |
| GET | `/admin/cupons/7` | PASS | CuponsController | view | — | `id=7` | OK |
| GET | `/admin/appsflyer/3` | PASS | AppsFlyerController | view | — | `id=3` | OK |
| GET | `/admin/campanhas/editar/5` | PASS | CampanhasController | edit | — | `id=5` | OK |
| POST | `/admin/campanhas/editar/5` | PASS | CampanhasController | edit | — | `id=5` | OK |
| POST | `/admin/campanhas/ativar/1` | PASS | CampanhasController | activate | — | `id=1` | OK |
| POST | `/admin/campanhas/desativar/2` | PASS | CampanhasController | deactivate | — | `id=2` | OK |
| POST | `/admin/campanhas/duplicar/4` | PASS | CampanhasController | duplicate | — | `id=4` | OK |
| POST | `/admin/campanhas/excluir/9` | PASS | CampanhasController | delete | — | `id=9` | OK |

**Nota:** `{id}` aceita apenas dígitos. `/admin/cupons/abc` corretamente **não resolve** (404).

---

## API + middleware

| Método | Rota | Status | Controller | Action | Middleware | Params | Resultado |
|--------|------|--------|------------|--------|------------|--------|-----------|
| POST | `/api/indicacao/confirmar-cadastro` | PASS | ApiIndicacaoController | confirmarCadastro | ApiAuth | — | OK |

O middleware `ApiAuth` é carregado de `middleware/ApiAuth.php` antes do controller.

---

## Placeholders suportados (RoutePattern)

| Placeholder | Padrão | Exemplo testado | Status |
|-------------|--------|-----------------|--------|
| `{id}` | dígitos | `/admin/cupons/7` | PASS |
| `{slug}` | kebab-case | `/campanha/minhas-mais` | PASS |
| `{codigo}` | alfanumérico | `/ref/MM48271` | PASS |
| `{uuid}` | UUID v4 | `/evt/550e8400-e29b-41d4-a716-446655440000` | PASS |

---

## Métodos HTTP PUT / DELETE

| Método | Rota (exemplo) | Status | Resultado |
|--------|----------------|--------|-----------|
| PUT | `/recurso/10` | PASS | Router aceita registro e resolução |
| DELETE | `/recurso/10` | PASS | Router aceita registro e resolução |

*Nenhuma rota de produção usa PUT/DELETE ainda; suporte nativo disponível via `$router->put()` / `$router->delete()`.*

---

## Rotas inexistentes (404 esperado)

| Método | Rota | Status | Resultado |
|--------|------|--------|-----------|
| GET | `/rota-inexistente` | PASS | null (404) |
| GET | `/admin/cupons/abc` | PASS | null — id inválido |

---

## Middleware fora do Router (inalterados)

| Middleware | Onde atua | Status |
|------------|-----------|--------|
| AuthMiddleware | Controllers (`requireAuth` / `requireGuest`) | Inalterado |
| Admin (email) | Controllers (`requireAdmin` privado) | Inalterado |
| CSRF | Controllers (`Csrf::validateRequest`) | Inalterado |
| ApiAuth | Rota API via Router | ✅ Funcional |

---

## Como reexecutar

```bash
php database/route_test_fase_1_2.php
```

Gera/atualiza este arquivo automaticamente.

# Sprint 2.3 — Relatório de Limpeza do Legado

Data: 2026-06-26

## Arquivos removidos

| Arquivo | Motivo |
|---------|--------|
| `models/EventoIndicacao.php` | Classe sem chamadas em runtime após Sprint 2.1 |
| `views/indicados/convite.php` | View órfã — landing legada sem rota registrada |

## Métodos removidos

### `models/Indicacao.php`
- `logShare()`
- `registerLinkAccess()`
- `completeRegistration()`
- `countAll()`
- `countByUsuario()`

### `models/ValidacaoIndicacao.php`
- `createFromIndicacao()`
- `getStats()`
- `statsAdminUsuario()`

### `models/Cupom.php`
- `getStats()`

### `models/Campanha.php`
- `getAdminStatsById()`

### `models/Indicado.php` (métodos legados de escrita/consulta)
- `create()`, `update()`, `updateStatus()`
- `findByCodigo()`, `findByCpf()`, `findByEmail()`, `findByTelefone()`, `findById()`
- `countByIndicador()`, `cpfExists()`, `emailExists()`, `telefoneExists()`, `codigoExists()`
- `statusLabel()`, `maskName()`, `getTimeline()`

### `repositories/CupomRepository.php`
- `getStats()`

### `core/ReferralService.php`
- `recordLinkAccessForLegacy()`
- `completeRegistrationForReferrer()`

### `controllers/IndicadosController.php`
- `index()` (sem rota)
- `participar()` (rota órfã)

## Rotas removidas

| Método | Rota | Motivo |
|--------|------|--------|
| POST | `/participar` | Órfã — única view que a referenciava (`indicados/convite.php`) não tinha rota de entrada |

## Views removidas

| View | Motivo |
|------|--------|
| `views/indicados/convite.php` | Sem rota para `IndicadosController::index()` |

## Assets removidos

Nenhum. Todos os CSS/JS referenciados em layouts permanecem em uso.

## Bootstrap

- Removido: `require_once .../models/EventoIndicacao.php`

## Mantido (dependência em runtime)

| Item | Motivo |
|------|--------|
| `models/Indicado.php` | `DashboardController` lê dados históricos via `listByIndicador()` |
| `IndicadosController` | Rotas ativas: `/cadastro-indicado`, `/salvar-indicado`, `/finalizado` |
| Views `indicados/cadastro.php`, `convite-invalido.php`, `finalizado.php` | Rotas ativas |
| `Usuario::telefoneExists()` | `@deprecated` — ainda usado em `ReferralService` e `IndicadosController` |
| Migrations e tabelas legadas (`indicados`, `eventos_indicacao`) | Escopo explícito: não remover dados históricos |

## Controllers órfãos

Nenhum removido — todos os controllers restantes possuem rotas registradas.

## Helpers sem utilização

Nenhum removido — funções em `core/helpers.php` permanecem referenciadas nas views admin.

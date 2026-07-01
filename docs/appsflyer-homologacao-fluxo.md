# AppsFlyer — Fluxo de Homologação Ponta a Ponta (Sprint 3.2)

Documentação do primeiro teste integrado: Dashboard → OneLink → AppsFlyer → App → API KOBE → Indicação → Validação → Cupom.

## Pré-requisitos

1. `config/appsflyer.php`:
   - `enabled` => `true`
   - `homologation` => `true`
   - `onelink_template` preenchido (URL OneLink AppsFlyer)
   - `default_media_source` e `default_campaign` configurados
2. Campanha ativa no admin
3. `API_TOKEN` configurado no `.env` para API KOBE
4. App configurado para enviar metadados AppsFlyer no cadastro

## Fluxo completo

```
Dashboard (Compartilhar)
    ↓
InviteLinkService
    ↓
OneLinkBuilder → URL OneLink com deep_link_sub1 = codigo indicador
    ↓  [LOG: uploads/logs/appsflyer/onelink.log]
AppsFlyer (redirect / atribuição)
    ↓
App mobile (install / open)
    ↓
POST /api/indicacao/confirmar-cadastro  (API KOBE)
    ↓  [LOG: uploads/logs/appsflyer/api_kobe.log]
ReferralService::registerApiIndication()
    ↓
AppsFlyerService::processApiEvent() (metadados opcionais)
    ↓
Admin → Validação manual
    ↓
CupomService (após aprovação)
```

Paralelamente, AppsFlyer pode enviar postbacks:

```
POST /api/appsflyer/webhook
    ↓  [LOG: uploads/logs/appsflyer/webhook.log]
AppsFlyerService::processWebhookEvent()
```

## Onde cada etapa grava dados

| Etapa | Componente | Tabela / Arquivo |
|-------|------------|------------------|
| Compartilhar link | `ReferralService::logShare()` | `indicacoes` (origem WEB, registro de share) |
| Link gerado | `InviteLinkService` | Log homologação: `uploads/logs/appsflyer/onelink.log` |
| Webhook AppsFlyer | `AppsFlyerWebhookController` | `appsflyer_events`, `api_logs`, log webhook |
| Teste manual | `AppsFlyerTestController` | `appsflyer_events`, `api_logs`, log webhook |
| Cadastro App | `ApiIndicacaoController` | `api_logs`, log api_kobe |
| Indicação criada | `ReferralService::registerApiIndication()` | `indicacoes`, `validacao_indicacoes` |
| Metadados AppsFlyer | `AppsFlyerService::processApiEvent()` | `appsflyer_events` |
| Validação admin | `ValidacaoController` | `validacao_indicacoes`, `historico_validacao` |
| Cupom | `CupomService` | `cupons`, `historico_cupom` |

## Passo a passo do teste

### 1. Dashboard — Compartilhar

1. Login como indicador
2. Acesse `/dashboard`
3. Clique em **Compartilhar**
4. Verifique que o link exibido é OneLink quando template configurado
5. Confira log em `uploads/logs/appsflyer/onelink.log`

**Grava:** `indicacoes` (share), log OneLink

### 2. OneLink → AppsFlyer → App

1. Abra o link OneLink em dispositivo de teste
2. Instale/abra o app via fluxo AppsFlyer
3. Confirme que `deep_link_sub1` contém o código indicador

**Grava:** atribuição no painel AppsFlyer (externo)

### 3. API KOBE — Cadastro do indicado

```bash
curl -X POST https://SEU_DOMINIO/api/indicacao/confirmar-cadastro \
  -H "Authorization: Bearer SEU_API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "codigoIndicador": "MM123456",
    "cpfIndicado": "12345678901",
    "emailIndicado": "indicado@teste.com",
    "telefoneIndicado": "31999999999",
    "tipoEvento": "INSTALL",
    "plataforma": "ANDROID",
    "appsflyerId": "af-test-device-id",
    "campaignId": "camp_001",
    "deepLinkSub1": "MM123456",
    "deepLinkSub2": "42",
    "deepLinkValue": "MM123456"
  }'
```

**Grava:** `indicacoes`, `validacao_indicacoes`, `appsflyer_events`, `api_logs`, log api_kobe

### 4. Webhook AppsFlyer (opcional)

```bash
curl -X POST https://SEU_DOMINIO/api/appsflyer/webhook \
  -H "Content-Type: application/json" \
  -d '{
    "appsflyer_id": "af-test-device-id",
    "event_name": "install",
    "deep_link_sub1": "MM123456",
    "platform": "android"
  }'
```

### 5. Teste sem App (homologação)

```bash
curl -X POST https://SEU_DOMINIO/api/appsflyer/test \
  -H "Content-Type: application/json" \
  -d '{
    "appsflyer_id": "homolog-test-001",
    "event_name": "install",
    "deep_link_sub1": "MM123456",
    "platform": "android"
  }'
```

### 6. Diagnóstico Admin

Acesse `/admin/appsflyer/diagnostico`.

### 7. Validação → Cupom

Admin → `/admin/validacoes` → aprovar → verificar `/admin/cupons`.

## Fallback WEB

Se `onelink_template` estiver vazio, `InviteLinkService` retorna `/convite?ref=CODIGO`.

## Desativar homologação

```php
'homologation' => false,
```

Desativa logs detalhados e endpoint `POST /api/appsflyer/test`.

## Logs por canal

| Arquivo | Conteúdo |
|---------|----------|
| `uploads/logs/appsflyer/onelink.log` | Usuário, código, URL, duração |
| `uploads/logs/appsflyer/webhook.log` | Payload, headers, IP, duração |
| `uploads/logs/appsflyer/api_kobe.log` | Payload recebido, resposta, duração |
| `uploads/logs/appsflyer/errors.log` | Erros de integração |

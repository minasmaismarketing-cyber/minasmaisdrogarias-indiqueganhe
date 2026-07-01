# API KOBE + AppsFlyer — Homologação

Documento técnico para integração do aplicativo KOBE com o backend **Indique e Ganhe** após leitura do Deep Link AppsFlyer.

---

## Endpoint

| Item | Valor |
|------|-------|
| **URL** | `POST /api/indicacao/confirmar-cadastro` |
| **Base** | `https://{seu-dominio}/api/indicacao/confirmar-cadastro` |

---

## Método HTTP

`POST`

---

## Headers obrigatórios

| Header | Valor |
|--------|-------|
| `Authorization` | `Bearer {API_TOKEN}` |
| `Content-Type` | `application/json` |

O `API_TOKEN` deve estar configurado no `.env` do servidor:

```env
API_TOKEN=seu-token-secreto
```

---

## Payload completo (exemplo)

```json
{
  "codigoIndicador": "MMN6GAXJ",
  "cpfIndicado": "12345678901",
  "emailIndicado": "indicado@example.com",
  "telefoneIndicado": "5511999999999",
  "tipoEvento": "INSTALL",
  "plataforma": "ANDROID",

  "appsflyerId": "1234567890-1234567",
  "deepLinkValue": "MMN6GAXJ",
  "afSub1": "MMN6GAXJ",
  "afSub2": "1",
  "afSub3": "Indique e Ganhe Minas Mais",
  "afSub4": "indique_ganhe",
  "afSub5": "homolog"
}
```

### Compatibilidade com payloads anteriores

Campos legados continuam aceitos (opcionais):

- `customerIdVtex`
- `campaignId`
- `deepLinkSub1` / `deep_link_sub1` (equivalente a `afSub1`)
- `deepLinkSub2` / `deep_link_sub2` (equivalente a `afSub2`)
- `mediaSource` / `pid`
- `campaign` / `c`

---

## Campos

### Obrigatórios

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `codigoIndicador` | string | Código do indicador (ex.: `MMN6GAXJ`) |
| `cpfIndicado` | string | CPF do indicado (11 dígitos, apenas números) |
| `emailIndicado` | string | E-mail do indicado |
| `telefoneIndicado` | string | Telefone com DDD (apenas números) |
| `tipoEvento` | string | `INSTALL`, `REENGAGEMENT` ou `UNKNOWN` |
| `plataforma` | string | `ANDROID`, `IOS` ou `WEB` |

### Opcionais (AppsFlyer / Deep Link)

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `appsflyerId` | string | ID do dispositivo AppsFlyer |
| `deepLinkValue` | string | Valor do deep link (código do indicador) |
| `afSub1` | string | Código do indicador (`ref`) |
| `afSub2` | string | Identificador auxiliar (ex.: `1`) |
| `afSub3` | string | Nome da campanha |
| `afSub4` | string | Origem do fluxo (ex.: `indique_ganhe`) |
| `afSub5` | string | Ambiente (ex.: `homolog`) |

Quando presentes, os metadados AppsFlyer são persistidos em `appsflyer_events` (campo `raw_payload` + campos normalizados). A indicação é criada independentemente desses campos.

---

## Valores aceitos

**tipoEvento**

| Valor | Comportamento |
|-------|---------------|
| `INSTALL` | Cadastro aceito → status `AGUARDANDO_VALIDACAO` |
| `REENGAGEMENT` | Rejeitado → `INVALIDADO` / motivo `APP_JA_EXISTENTE` |
| `UNKNOWN` | Aceito → status `EM_ANALISE` |

**plataforma**

- `ANDROID`
- `IOS`
- `WEB`

---

## Exemplo de resposta — sucesso (INSTALL)

**HTTP 200**

```json
{
  "success": true,
  "status": "AGUARDANDO_VALIDACAO",
  "message": "Cadastro recebido com sucesso."
}
```

## Exemplo de resposta — sucesso (UNKNOWN)

**HTTP 200**

```json
{
  "success": true,
  "status": "EM_ANALISE",
  "message": "Cadastro recebido e em análise."
}
```

## Exemplo de resposta — reengagement

**HTTP 400**

```json
{
  "success": false,
  "status": "INVALIDADO",
  "motivo": "APP_JA_EXISTENTE",
  "message": "Indicação inválida."
}
```

## Exemplo de resposta — erro de validação

**HTTP 400**

```json
{
  "success": false,
  "message": "Campo obrigatório: cpfIndicado"
}
```

## Exemplo de resposta — código inválido

**HTTP 404**

```json
{
  "success": false,
  "message": "Código do indicador não encontrado"
}
```

---

## Fluxo esperado após o cadastro

```
1. Usuário abre link de convite (/convite?ref=MMN6GAXJ ou OneLink AppsFlyer)
2. Smart Script gera OneLink com af_sub1 = ref
3. App é instalado/aberto via AppsFlyer
4. App lê parâmetros do Deep Link (deepLinkValue, afSub1–afSub5, appsflyerId)
5. Usuário conclui cadastro no app KOBE
6. App envia POST /api/indicacao/confirmar-cadastro
7. Backend:
   a. Valida payload e campanha ativa
   b. ReferralService::registerApiIndication() cria a indicação
   c. Metadados AppsFlyer (se enviados) → AppsFlyerService::processApiEvent()
   d. Registro em api_logs + uploads/logs/appsflyer/api_kobe.log (homologação)
8. Admin valida indicação manualmente
9. Após aprovação → cupom liberado ao indicado
```

---

## Exemplo cURL

```bash
curl -X POST "https://seu-dominio.com/api/indicacao/confirmar-cadastro" \
  -H "Authorization: Bearer SEU_API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "codigoIndicador": "MMN6GAXJ",
    "cpfIndicado": "12345678901",
    "emailIndicado": "indicado@teste.com",
    "telefoneIndicado": "5511999999999",
    "tipoEvento": "INSTALL",
    "plataforma": "ANDROID",
    "appsflyerId": "af-test-device-id",
    "deepLinkValue": "MMN6GAXJ",
    "afSub1": "MMN6GAXJ",
    "afSub2": "1",
    "afSub3": "Indique e Ganhe Minas Mais",
    "afSub4": "indique_ganhe",
    "afSub5": "homolog"
  }'
```

---

## Observações para homologação

1. Deve existir **campanha ativa** no admin antes de testar.
2. O `codigoIndicador` deve corresponder a um indicador cadastrado.
3. CPF, e-mail e telefone não podem estar duplicados no programa.
4. Metadados AppsFlyer são **opcionais** — o cadastro funciona sem eles.
5. Logs de homologação: `uploads/logs/appsflyer/api_kobe.log`.

---

## Referências internas

- Implementação: `controllers/ApiIndicacaoController.php`
- Normalização AppsFlyer: `services/AppsFlyerEventData.php`
- Persistência: `services/AppsFlyerService.php`
- Configuração: `config/appsflyer.php`

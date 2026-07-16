# API KOBE + AppsFlyer — Homologação

Documento técnico para integração do aplicativo KOBE com o backend **Indique e Ganhe** após leitura do Deep Link AppsFlyer (deferred deep linking).

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
| `Authorization` | `Bearer {KOBE_API_TOKEN}` |
| `Content-Type` | `application/json` |

Autenticação exclusiva desta integração: variável `KOBE_API_TOKEN` no `.env` do servidor.

```env
KOBE_API_TOKEN=
```

Não usar `API_TOKEN` genérico nesta rota. Se `KOBE_API_TOKEN` estiver ausente, a API responde erro interno seguro (HTTP 500), sem expor detalhes.

---

## OneLink (Landing / Smart Script)

A landing `/convite?ref={codigo}` gera o OneLink apenas via:

`window.AF_SMART_SCRIPT.generateOneLinkURL()` → `result.clickURL`

Parâmetros oficiais na URL gerada:

| Parâmetro | Valor |
|-----------|--------|
| `pid` | `User_invite` |
| `c` | `Indique e Ganhe Minas Mais` |
| `deep_link_value` | `indique` |
| `deep_link_sub1` | código do indicador (`?ref=`) |
| `deep_link_sub2` | ID interno do indicador (quando disponível) |
| `deep_link_sub3` | `Indique e Ganhe Minas Mais` |
| `deep_link_sub4` | `indique_ganhe` |
| `deep_link_sub5` | `homolog` |

Exemplo (homologação):

```
https://drogariasminasmais.onelink.me/zjoY/indiqueganhe?pid=User_invite&c=Indique%20e%20Ganhe%20Minas%20Mais&deep_link_value=indique&deep_link_sub1=MMN6GAXJ&deep_link_sub2={id}&deep_link_sub3=Indique%20e%20Ganhe%20Minas%20Mais&deep_link_sub4=indique_ganhe&deep_link_sub5=homolog
```

Fallback da landing: `APP_DOWNLOAD_URL`.

---

## Payload completo (exemplo oficial)

```json
{
  "codigoIndicador": "MMN6GAXJ",
  "cpfIndicado": "12345678901",
  "emailIndicado": "indicado@example.com",
  "telefoneIndicado": "5511999999999",
  "tipoEvento": "INSTALL",
  "plataforma": "ANDROID",

  "appsflyerId": "1234567890-1234567",
  "deepLinkValue": "indique",
  "deepLinkSub1": "MMN6GAXJ",
  "deepLinkSub2": "42",
  "deepLinkSub3": "Indique e Ganhe Minas Mais",
  "deepLinkSub4": "indique_ganhe",
  "deepLinkSub5": "homolog"
}
```

### Campos oficiais de deep link (AppsFlyer)

| Campo | Alias snake_case | Descrição |
|-------|------------------|-----------|
| `deepLinkSub1` | `deep_link_sub1` | Código do indicador |
| `deepLinkSub2` | `deep_link_sub2` | ID interno do indicador |
| `deepLinkSub3` | `deep_link_sub3` | Campanha |
| `deepLinkSub4` | `deep_link_sub4` | Discriminador (`indique_ganhe`) |
| `deepLinkSub5` | `deep_link_sub5` | Ambiente (`homolog` / `production`) |

### Compatibilidade com payloads legados

Ainda aceitos (prioridade menor):

- `afSub1`–`afSub5`
- `af_sub1`–`af_sub5`
- `customerIdVtex`
- `campaignId`
- `mediaSource` / `pid`
- `campaign` / `c`

**Prioridade de leitura dos subparâmetros:**

1. `deepLinkSub*`
2. `deep_link_sub*`
3. `afSub*`
4. `af_sub*`

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
| `deepLinkValue` | string | Valor do deep link (`indique`) |
| `deepLinkSub1` | string | Código do indicador (`ref`) |
| `deepLinkSub2` | string | ID interno do indicador |
| `deepLinkSub3` | string | Nome da campanha |
| `deepLinkSub4` | string | Discriminador (`indique_ganhe`) |
| `deepLinkSub5` | string | Ambiente (`homolog`) |

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

## Exemplo de resposta — token inválido

**HTTP 401**

```json
{
  "success": false,
  "message": "Invalid API token."
}
```

## Exemplo de resposta — token não configurado

**HTTP 500**

```json
{
  "success": false,
  "message": "Erro interno do servidor."
}
```

---

## Fluxo esperado após o cadastro

```
1. Usuário abre link de convite (/convite?ref=MMN6GAXJ ou OneLink AppsFlyer)
2. Smart Script gera OneLink com deep_link_value=indique e deep_link_sub1=ref
3. App é instalado/aberto via AppsFlyer (deferred deep linking)
4. App lê deepLinkValue + deepLinkSub1–5 (+ appsflyerId)
5. Usuário conclui cadastro no app KOBE
6. App envia POST /api/indicacao/confirmar-cadastro (Bearer KOBE_API_TOKEN)
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
  -H "Authorization: Bearer SEU_KOBE_API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "codigoIndicador": "MMN6GAXJ",
    "cpfIndicado": "12345678901",
    "emailIndicado": "indicado@teste.com",
    "telefoneIndicado": "5511999999999",
    "tipoEvento": "INSTALL",
    "plataforma": "ANDROID",
    "appsflyerId": "af-test-device-id",
    "deepLinkValue": "indique",
    "deepLinkSub1": "MMN6GAXJ",
    "deepLinkSub2": "42",
    "deepLinkSub3": "Indique e Ganhe Minas Mais",
    "deepLinkSub4": "indique_ganhe",
    "deepLinkSub5": "homolog"
  }'
```

Payload legado (ainda aceito):

```json
{
  "codigoIndicador": "MMN6GAXJ",
  "cpfIndicado": "12345678901",
  "emailIndicado": "indicado@teste.com",
  "telefoneIndicado": "5511999999999",
  "tipoEvento": "INSTALL",
  "plataforma": "ANDROID",
  "afSub1": "MMN6GAXJ",
  "afSub2": "42",
  "afSub3": "Indique e Ganhe Minas Mais",
  "afSub4": "indique_ganhe",
  "afSub5": "homolog"
}
```

---

## Observações para homologação

1. Deve existir **campanha ativa** no admin antes de testar.
2. O `codigoIndicador` deve corresponder a um indicador cadastrado.
3. CPF, e-mail e telefone não podem estar duplicados no programa.
4. Metadados AppsFlyer são **opcionais** — o cadastro funciona sem eles.
5. Configurar `KOBE_API_TOKEN` no `.env` do servidor (não versionar o valor real).
6. Logs de homologação: `uploads/logs/appsflyer/api_kobe.log`.

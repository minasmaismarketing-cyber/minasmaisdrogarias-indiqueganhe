# Contrato API KOBE — confirmar-cadastro

Referência canônica complementar a `docs/api-kobe-appsflyer-homologacao.md`.

## Endpoint (inalterado)

- **URL:** `POST /api/indicacao/confirmar-cadastro`
- **Auth:** `Authorization: Bearer {KOBE_API_TOKEN}`
- **Content-Type:** `application/json`

Campos obrigatórios, método POST, Bearer Token, deepLinkSub1–5, OneLink e AppsFlyer **permanecem inalterados**.

## Campo opcional novo

| Campo | Obrigatório | Descrição |
|-------|-------------|-----------|
| `nomeIndicado` | Não | Nome real do indicado. Se ausente, o backend tenta localizar em `usuarios` (CPF → e-mail → telefone) ou usa `Indicado via API`. |

## Header opcional recomendado

| Header | Descrição |
|--------|-----------|
| `Idempotency-Key` | Chave única por confirmação (até 64 chars). Reenvio com a mesma chave devolve o resultado original sem duplicar indicação/cupom. |

## Comportamento automático (Sprint 4.6)

| tipoEvento | Resultado |
|------------|-----------|
| `INSTALL` elegível | Aprovação automática + cupom 10% ao **indicador** → `BENEFICIO_LIBERADO` |
| `INSTALL` elegível sem estoque | Aprovado sem cupom → `BENEFICIO_PENDENTE` (motivo operacional `BENEFICIO_PENDENTE_SEM_ESTOQUE`) |
| `REENGAGEMENT` | Reprovado → HTTP 400 / `INVALIDADO` / `APP_JA_EXISTENTE` |
| `UNKNOWN` | `EM_ANALISE` / análise manual (sem cupom) |

## Respostas de sucesso

```json
{ "success": true, "status": "BENEFICIO_LIBERADO", "message": "Indicação aprovada e benefício liberado." }
```

```json
{ "success": true, "status": "BENEFICIO_PENDENTE", "message": "Indicação aprovada. O benefício será liberado em breve." }
```

```json
{ "success": true, "status": "EM_ANALISE", "message": "Cadastro recebido e em análise." }
```

Reenvio idempotente (HTTP 200 ou 400 conforme resultado original):

```json
{ "success": true, "status": "{STATUS_ATUAL}", "message": "Cadastro já processado anteriormente." }
```

## Evento “app aberto”

O endpoint atual é chamado **somente após o cadastro**. Não existe transição automática “app aberto” sem um evento adicional da KOBE. Para atualizar o Admin no momento da abertura do app, seria necessário um evento/endpoint adicional.

## Rate limit

Pendência: a infraestrutura atual (`login_tentativas`) não oferece rate limit seguro e isolado para esta rota sem risco de bloquear reenvios idempotentes legítimos. Documentado como pendência — não implementado de forma frágil nesta sprint.

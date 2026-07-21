# Contrato API KOBE — confirmar-cadastro

Referência canônica complementar a `docs/api-kobe-appsflyer-homologacao.md`.

## Endpoint (inalterado)

- **URL:** `POST /api/indicacao/confirmar-cadastro`
- **Auth:** `Authorization: Bearer {KOBE_API_TOKEN}`
- **Content-Type:** `application/json`

Campos obrigatórios, método POST, Bearer Token, deepLinkSub1–5, OneLink e AppsFlyer **permanecem inalterados**.

## Identificador principal de elegibilidade

1. **CPF** (principal)
2. E-mail
3. Telefone
4. `customerId` (quando enviado — persistido; não define elegibilidade sozinho)

**Dispositivo, IP e reinstalação do app não definem elegibilidade** no backend. IP permanece apenas em logs técnicos.

## Interpretação de tipoEvento

| Valor | Comportamento interno |
|-------|------------------------|
| `INSTALL` | Avalia elegibilidade (CPF e demais regras). Se elegível → aprova + cupom. |
| `REENGAGEMENT` | **Não** reprova automaticamente. Avalia pelo CPF como INSTALL. |
| `UNKNOWN` | `EM_ANALISE` apenas quando não há dados suficientes para decisão automática. |

`APP_JA_EXISTENTE` deixou de ser bloqueio automático por REENGAGEMENT.

## Uma indicação aberta por indicador

Cada indicador tem no máximo **uma** indicação aberta (`AGUARDANDO` / `LINK_ACESSADO` / `CADASTRO_PENDENTE`).

Novos cliques no link:

- continuam sendo contabilizados em `cliques`;
- **não** criam nova linha em `indicacoes`;
- atualizam `data_ultimo_clique` na indicação aberta.

## Associação da chamada à indicação

Ordem:

1. `Idempotency-Key`
2. código do indicador + CPF
3. código + telefone
4. código + e-mail
5. indicação aberta mais recente do indicador
6. criar nova somente se nenhuma associação segura for possível

Toda chamada recebida (aprovação ou reprovação de negócio) atualiza dados, status, motivo e histórico **antes** da resposta HTTP.

## Campo opcional

| Campo | Obrigatório | Descrição |
|-------|-------------|-----------|
| `nomeIndicado` | Não | Nome real. Nunca sobrescreve nome real salvo com “Indicado via API”. |
| `customerId` | Não | ID externo do cliente, quando disponível. |

## Header opcional recomendado

| Header | Descrição |
|--------|-----------|
| `Idempotency-Key` | Reenvio devolve o resultado original sem duplicar indicação/cupom. |

## Cupom

- Um único benefício 10% por indicador na campanha.
- Tentativas rejeitadas nunca geram cupom.
- Sem estoque → `BENEFICIO_PENDENTE` (não reprova).

## Limitações do contrato atual

O backend só atualiza o que a KOBE **envia** via este endpoint.

Cenários **sem** chamada à API (cadastro abandonado, falha local no app, cancelamento antes do POST) **não** podem ser atualizados automaticamente pelo backend.

Para rastreabilidade completa no futuro, a KOBE precisaria enviar eventos adicionais (ex.: app aberto, cadastro iniciado, cadastro cancelado, motivoFalha, statusCadastro).

# API KOBE - Documentação

## Endpoint: Confirmar Cadastro do Indicado

**URL:** `POST /api/indicacao/confirmar-cadastro`

**Descrição:** Endpoint para ser chamado pelo aplicativo KOBE quando o indicado concluir o cadastro no app.

### Autenticação

**Header obrigatório:**
```
Authorization: Bearer {API_TOKEN}
```

O `API_TOKEN` deve ser configurado no arquivo `.env`:
```
API_TOKEN=your-secret-api-token-here
```

### Content-Type

**Obrigatório:**
```
Content-Type: application/json
```

### Payload (JSON)

```json
{
  "codigoIndicador": "ABC123",
  "cpfIndicado": "12345678901",
  "emailIndicado": "indicado@example.com",
  "telefoneIndicado": "5511999999999",
  "customerIdVtex": "vtex_customer_id",
  "appsflyerId": "appsflyer_id",
  "tipoEvento": "INSTALL",
  "plataforma": "ANDROID"
}
```

### Campos

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| codigoIndicador | string | Sim | Código do indicador |
| cpfIndicado | string | Sim | CPF do indicado (11 dígitos) |
| emailIndicado | string | Sim | E-mail do indicado |
| telefoneIndicado | string | Sim | Telefone do indicado (com DDD) |
| customerIdVtex | string | Não | ID do cliente no VTEX |
| appsflyerId | string | Não | ID do AppsFlyer |
| tipoEvento | string | Sim | Tipo de evento (INSTALL, REENGAGEMENT, UNKNOWN) |
| plataforma | string | Sim | Plataforma (ANDROID, IOS, WEB) |

### Valores Aceitos

**tipoEvento:**
- `INSTALL` - Primeira instalação do app
- `REENGAGEMENT` - Usuário já tinha o app instalado
- `UNKNOWN` - Tipo de evento desconhecido

**plataforma:**
- `ANDROID` - Dispositivo Android
- `IOS` - Dispositivo iOS
- `WEB` - Acesso via navegador

### Regras de Validação

1. **Código do indicador existe** - O código deve estar cadastrado no sistema
2. **Campanha ativa** - Deve haver uma campanha ativa no sistema
3. **CPF indicado não participou antes** - O CPF não pode estar cadastrado como indicado anteriormente
4. **CPF indicado ≠ CPF indicador** - O CPF do indicado não pode ser igual ao CPF do indicador
5. **E-mail não duplicado** - O e-mail não pode estar cadastrado como indicado anteriormente
6. **Telefone não duplicado** - O telefone não pode estar cadastrado como indicado anteriormente
7. **tipoEvento válido** - Deve ser um dos valores aceitos
8. **plataforma válida** - Deve ser um dos valores aceitos

### Regras de Status

**Se tipoEvento = INSTALL:**
- Status: `AGUARDANDO_VALIDACAO`

**Se tipoEvento = REENGAGEMENT:**
- Status: `INVALIDADO`
- Motivo: `APP_JA_EXISTENTE`

**Se tipoEvento = UNKNOWN:**
- Status: `EM_ANALISE`

### Respostas

#### Sucesso (INSTALL)

**Status:** 200 OK

```json
{
  "success": true,
  "status": "AGUARDANDO_VALIDACAO",
  "message": "Cadastro recebido com sucesso."
}
```

#### Sucesso (UNKNOWN)

**Status:** 200 OK

```json
{
  "success": true,
  "status": "EM_ANALISE",
  "message": "Cadastro recebido e em análise."
}
```

#### Invalidado (REENGAGEMENT)

**Status:** 400 Bad Request

```json
{
  "success": false,
  "status": "INVALIDADO",
  "motivo": "APP_JA_EXISTENTE",
  "message": "Indicação inválida."
}
```

#### Erro de Validação

**Status:** 400 Bad Request

```json
{
  "success": false,
  "message": "Campo obrigatório: codigoIndicador"
}
```

#### Código não encontrado

**Status:** 404 Not Found

```json
{
  "success": false,
  "message": "Código do indicador não encontrado"
}
```

#### Sem campanha ativa

**Status:** 400 Bad Request

```json
{
  "success": false,
  "message": "Nenhuma campanha ativa encontrada"
}
```

#### CPF já participou

**Status:** 400 Bad Request

```json
{
  "success": false,
  "message": "CPF já participou do programa"
}
```

#### E-mail duplicado

**Status:** 400 Bad Request

```json
{
  "success": false,
  "message": "E-mail já cadastrado"
}
```

#### Telefone duplicado

**Status:** 400 Bad Request

```json
{
  "success": false,
  "message": "Telefone já cadastrado"
}
```

#### tipoEvento inválido

**Status:** 400 Bad Request

```json
{
  "success": false,
  "message": "tipoEvento inválido. Valores aceitos: INSTALL, REENGAGEMENT, UNKNOWN"
}
```

#### plataforma inválida

**Status:** 400 Bad Request

```json
{
  "success": false,
  "message": "plataforma inválida. Valores aceitos: ANDROID, IOS, WEB"
}
```

#### Erro de autenticação

**Status:** 401 Unauthorized

```json
{
  "success": false,
  "message": "Authorization header required."
}
```

**Ou:**

```json
{
  "success": false,
  "message": "Invalid authorization format. Use: Bearer {token}"
}
```

**Ou:**

```json
{
  "success": false,
  "message": "Invalid API token."
}
```

#### Método não permitido

**Status:** 405 Method Not Allowed

```json
{
  "success": false,
  "message": "Method not allowed. Use POST."
}
```

#### Content-Type inválido

**Status:** 400 Bad Request

```json
{
  "success": false,
  "message": "Content-Type must be application/json."
}
```

#### Erro interno

**Status:** 500 Internal Server Error

```json
{
  "success": false,
  "message": "Erro interno do servidor."
}
```

### Segurança

- **Apenas POST** - O endpoint aceita apenas requisições POST
- **Content-Type JSON** - O Content-Type deve ser application/json
- **Sanitização de dados** - Todos os dados são sanitizados antes do processamento
- **Sem exposição de erros técnicos** - Erros técnicos não são expostos na resposta
- **API Token** - Token de autenticação obrigatório via header Authorization
- **Log de requisições** - Todas as requisições são logadas na tabela `api_logs`

### Logs

Todas as requisições são registradas na tabela `api_logs` com as seguintes informações:

- `endpoint` - Endpoint chamado
- `payload` - Payload enviado (JSON)
- `response` - Resposta enviada (JSON)
- `ip` - IP do cliente
- `status_code` - Código HTTP da resposta
- `created_at` - Data/hora da requisição

### Exemplo de Requisição (cURL)

```bash
curl -X POST https://seu-dominio.com/api/indicacao/confirmar-cadastro \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer your-secret-api-token-here" \
  -d '{
    "codigoIndicador": "ABC123",
    "cpfIndicado": "12345678901",
    "emailIndicado": "indicado@example.com",
    "telefoneIndicado": "5511999999999",
    "customerIdVtex": "vtex_customer_id",
    "appsflyerId": "appsflyer_id",
    "tipoEvento": "INSTALL",
    "plataforma": "ANDROID"
  }'
```

### Exemplo de Requisição (JavaScript)

```javascript
fetch('https://seu-dominio.com/api/indicacao/confirmar-cadastro', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer your-secret-api-token-here'
  },
  body: JSON.stringify({
    codigoIndicador: 'ABC123',
    cpfIndicado: '12345678901',
    emailIndicado: 'indicado@example.com',
    telefoneIndicado: '5511999999999',
    customerIdVtex: 'vtex_customer_id',
    appsflyerId: 'appsflyer_id',
    tipoEvento: 'INSTALL',
    plataforma: 'ANDROID'
  })
})
.then(response => response.json())
.then(data => console.log(data))
.catch(error => console.error('Error:', error));
```

### Integrações Futuras

Este endpoint está preparado para integrações futuras com:

- **AppsFlyer** - Campo `appsflyerId` já incluído no payload
- **VTEX** - Campo `customerIdVtex` já incluído no payload

Atualmente, estes campos são apenas armazenados para uso futuro.

### Notas

- O endpoint não integra com AppsFlyer ou VTEX nesta etapa
- A validação é puramente interna
- Todos os dados são sanitizados para prevenir XSS
- O IP do cliente é registrado para fins de auditoria
- Logs de eventos são criados via EventLogger

# CHECKLIST - Etapa 15 — API de Confirmação do Indicado

### OBJETIVO
Criar a API que será chamada pelo aplicativo KOBE quando o indicado concluir o cadastro no app, preparando a arquitetura para integrações futuras (AppsFlyer, VTEX).

### MIGRAÇÃO

#### Tabela api_logs
- [x] Criar migration migration_etapa15.sql
- [x] Campos: id, endpoint, payload, response, ip, status_code, created_at
- [x] Índices: endpoint, created_at

### MODELOS

#### ApiLog Model
- [x] create() - Criar log de API
- [x] findByEndpoint() - Buscar logs por endpoint
- [x] findByIp() - Buscar logs por IP
- [x] listRecent() - Listar logs recentes
- [x] countByEndpoint() - Contar logs por endpoint
- [x] countByIp() - Contar logs por IP

### CONFIGURAÇÃO

#### .env.example
- [x] Adicionar API_TOKEN
- [x] Documentar uso do token

### MIDDLEWARE

#### ApiAuth
- [x] Validar método POST
- [x] Validar Content-Type JSON
- [x] Validar header Authorization
- [x] Validar formato Bearer token
- [x] Validar token com hash_equals
- [x] Responder com erro apropriado
- [x] Não expor erros técnicos

### CONTROLLER

#### ApiIndicacaoController
- [x] confirmarCadastro() - Endpoint principal
- [x] Validar campos obrigatórios
- [x] Sanitizar dados
- [x] Validar tipoEvento (INSTALL, REENGAGEMENT, UNKNOWN)
- [x] Validar plataforma (ANDROID, IOS, WEB)
- [x] Validar código do indicador
- [x] Validar campanha ativa
- [x] Validar CPF não participou
- [x] Validar CPF ≠ CPF indicador
- [x] Validar e-mail não duplicado
- [x] Validar telefone não duplicado
- [x] Determinar status baseado em tipoEvento
- [x] Criar registro de indicado
- [x] Logar evento via EventLogger
- [x] Logar chamada API via ApiLog
- [x] Retornar JSON apropriado
- [x] Tratar erros com try-catch

### ROTAS

#### API Routes
- [x] POST /api/indicacao/confirmar-cadastro - Confirmar cadastro
- [x] Adicionar middleware ApiAuth

### ROUTER

#### Router Middleware Support
- [x] Adicionar parâmetro middleware ao addRoute
- [x] Executar middleware no dispatch
- [x] Manter compatibilidade com rotas sem middleware

### REGRAS DE NEGÓCIO

#### Validações Implementadas
- [x] Código do indicador existe
- [x] Campanha ativa
- [x] CPF indicado não participou antes
- [x] CPF indicado ≠ CPF indicador
- [x] E-mail não duplicado
- [x] Telefone não duplicado
- [x] tipoEvento aceito: INSTALL, REENGAGEMENT, UNKNOWN
- [x] plataforma aceita: ANDROID, IOS, WEB

#### Regras de Status
- [x] INSTALL → AGUARDANDO_VALIDACAO
- [x] REENGAGEMENT → INVALIDADO (motivo: APP_JA_EXISTENTE)
- [x] UNKNOWN → EM_ANALISE

### SEGURANÇA

#### Medidas de Segurança
- [x] Apenas POST
- [x] Content-Type JSON obrigatório
- [x] Sanitização de dados
- [x] Sem exposição de erros técnicos
- [x] API Token via header Authorization
- [x] hash_equals para comparação de token
- [x] Log de IP do cliente
- [x] Log de todas as requisições

### RESPOSTAS

#### Respostas Implementadas
- [x] Sucesso (INSTALL) - 200 OK
- [x] Sucesso (UNKNOWN) - 200 OK
- [x] Invalidado (REENGAGEMENT) - 400 Bad Request
- [x] Campo obrigatório - 400 Bad Request
- [x] Código não encontrado - 404 Not Found
- [x] Sem campanha ativa - 400 Bad Request
- [x] CPF já participou - 400 Bad Request
- [x] E-mail duplicado - 400 Bad Request
- [x] Telefone duplicado - 400 Bad Request
- [x] tipoEvento inválido - 400 Bad Request
- [x] plataforma inválida - 400 Bad Request
- [x] Authorization header required - 401 Unauthorized
- [x] Invalid authorization format - 401 Unauthorized
- [x] Invalid API token - 401 Unauthorized
- [x] Method not allowed - 405 Method Not Allowed
- [x] Content-Type inválido - 400 Bad Request
- [x] Erro interno - 500 Internal Server Error

### EVENT LOGGING

#### Logs de API
- [x] Log de todas as chamadas API
- [x] Armazenar payload
- [x] Armazenar response
- [x] Armazenar IP
- [x] Armazenar status_code
- [x] Log de evento INDICADO_CADASTRADO

### DOCUMENTAÇÃO

#### API Documentation
- [x] docs/api-kobe.md criado
- [x] Endpoint documentado
- [x] Payload exemplo documentado
- [x] Campos documentados
- [x] Valores aceitos documentados
- [x] Regras de validação documentadas
- [x] Regras de status documentadas
- [x] Respostas documentadas
- [x] Erros documentados
- [x] Segurança documentada
- [x] Logs documentados
- [x] Exemplo cURL
- [x] Exemplo JavaScript
- [x] Integrações futuras documentadas

### ARQUIVOS ALTERADOS

#### Database
- `database/migration_etapa15.sql` - Migration da tabela api_logs

#### Models
- `models/ApiLog.php` - Model de logs de API

#### Config
- `.env.example` - Adicionado API_TOKEN

#### Middleware
- `middleware/ApiAuth.php` - Middleware de autenticação API

#### Controllers
- `controllers/ApiIndicacaoController.php` - Controller de API de indicação

#### Core
- `core/Router.php` - Adicionado suporte a middleware

#### Config
- `config/routes.php` - Adicionada rota API com middleware

#### Docs
- `docs/api-kobe.md` - Documentação completa da API

### VALIDAÇÕES

#### Testar Migration
- [ ] Executar migration_etapa15.sql no phpMyAdmin
- [ ] Verificar tabela api_logs criada
- [ ] Verificar índices criados

#### Testar Middleware
- [ ] Testar requisição sem Authorization header
- [ ] Testar requisição com Authorization inválido
- [ ] Testar requisição com token incorreto
- [ ] Testar requisição GET (deve falhar)
- [ ] Testar requisição sem Content-Type JSON

#### Testar Endpoint
- [ ] Testar payload válido (INSTALL)
- [ ] Testar payload válido (REENGAGEMENT)
- [ ] Testar payload válido (UNKNOWN)
- [ ] Testar campo obrigatório faltando
- [ ] Testar código indicador inexistente
- [ ] Testar sem campanha ativa
- [ ] Testar CPF duplicado
- [ ] Testar CPF igual ao indicador
- [ ] Testar e-mail duplicado
- [ ] Testar telefone duplicado
- [ ] Testar tipoEvento inválido
- [ ] Testar plataforma inválida

#### Testar Respostas
- [ ] Verificar status code 200 para sucesso
- [ ] Verificar status code 400 para validação
- [ ] Verificar status code 401 para autenticação
- [ ] Verificar status code 404 para não encontrado
- [ ] Verificar status code 405 para método não permitido
- [ ] Verificar status code 500 para erro interno

#### Testar Logs
- [ ] Verificar log de API criado
- [ ] Verificar payload armazenado
- [ ] Verificar response armazenado
- [ ] Verificar IP armazenado
- [ ] Verificar status_code armazenado
- [ ] Verificar evento INDICADO_CADASTRADO logado

#### Testar Integração
- [ ] Testar via cURL
- [ ] Testar via JavaScript fetch
- [ ] Verificar sanitização de dados
- [ ] Verificar não exposição de erros técnicos

### NÃO IMPLEMENTADO (FUTURO)
- [x] Integração AppsFlyer (arquitetura preparada)
- [x] Integração VTEX (arquitetura preparada)
- [x] WhatsApp
- [x] E-mail
- [x] Cashback
- [x] Push Notification
- [x] PIX

### MANUTIDO
- [x] Arquitetura MVC
- [x] Middleware Pattern
- [x] Mobile First
- [x] Layout Minas Mais
- [x] Paleta atual

### INSTRUÇÕES DE DEPLOY

1. Executar migration_etapa15.sql no phpMyAdmin
2. Configurar API_TOKEN no .env
3. Deploy para Hostinger
4. Testar endpoint via cURL
5. Testar autenticação
6. Testar validações
7. Verificar logs de API
8. Verificar eventos de indicado
9. Compartilhar documentação com equipe KOBE

### NOTAS

- API completa para integração com app KOBE
- Autenticação via Bearer token
- Middleware de segurança implementado
- Validações completas implementadas
- Logs de API para auditoria
- Documentação completa em docs/api-kobe.md
- Arquitetura preparada para AppsFlyer e VTEX
- Sanitização de dados para segurança
- Sem exposição de erros técnicos
- Router atualizado com suporte a middleware

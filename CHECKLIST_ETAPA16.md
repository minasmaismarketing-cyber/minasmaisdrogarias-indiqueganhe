# CHECKLIST - Etapa 16 — Preparação para Integração AppsFlyer

### OBJETIVO
Preparar toda a arquitetura do sistema para receber eventos da AppsFlyer, seguindo princípios SOLID e isolando toda comunicação AppsFlyer na camada de Services.

### MIGRAÇÃO

#### Tabela appsflyer_events
- [x] Criar migration migration_etapa16.sql
- [x] Campos: id, usuario_id, indicacao_id, appsflyer_id, event_name, event_value, install_type, media_source, campaign, campaign_id, af_status, platform, raw_payload, created_at
- [x] Índices: usuario_id, indicacao_id, appsflyer_id, af_status, created_at
- [x] ENUM install_type: FIRST_INSTALL, REINSTALL, REENGAGEMENT, UNKNOWN
- [x] ENUM af_status: PENDING, RECEIVED, VALIDATED, INVALID

### ENUMS

#### InstallType
- [x] Criar enum InstallType
- [x] Valores: FIRST_INSTALL, REINSTALL, REENGAGEMENT, UNKNOWN
- [x] Método label() - Label legível
- [x] Método icon() - Ícone (emoji)

#### AppsFlyerStatus
- [x] Criar enum AppsFlyerStatus
- [x] Valores: PENDING, RECEIVED, VALIDATED, INVALID
- [x] Método label() - Label legível
- [x] Método icon() - Ícone (emoji)
- [x] Método color() - Cor para badges

### MODELOS

#### AppsFlyerEvent Model
- [x] create() - Criar evento
- [x] findById() - Buscar por ID
- [x] findByUsuario() - Listar por usuário
- [x] findByIndicacao() - Listar por indicação
- [x] findByAppsflyerId() - Buscar por AppsFlyer ID
- [x] findAll() - Listar com filtros
- [x] updateStatus() - Atualizar status
- [x] getStats() - Estatísticas
- [x] countByStatus() - Contar por status
- [x] listRecent() - Listar recentes

### REPOSITORY

#### AppsFlyerRepository
- [x] findById() - Buscar por ID
- [x] findByUsuario() - Listar por usuário
- [x] findByIndicacao() - Listar por indicação
- [x] findByAppsflyerId() - Buscar por AppsFlyer ID
- [x] findAll() - Listar com filtros
- [x] create() - Criar evento
- [x] updateStatus() - Atualizar status
- [x] getStats() - Estatísticas
- [x] countByStatus() - Contar por status
- [x] listRecent() - Listar recentes
- [x] existsByAppsflyerId() - Verificar existência

### SERVICES

#### AppsFlyerService
- [x] processEvent() - Processar evento recebido
- [x] validateEvent() - Validar evento
- [x] rejectEvent() - Rejeitar evento
- [x] getEventByAppsflyerId() - Buscar por AppsFlyer ID
- [x] getEventsByUser() - Listar por usuário
- [x] getEventsByIndication() - Listar por indicação
- [x] getAllEvents() - Listar todos com filtros
- [x] getStats() - Estatísticas
- [x] isEnabled() - Verificar se integração está ativa
- [x] loadConfig() - Carregar configuração

#### AppsFlyerValidationService
- [x] validatePayload() - Validar estrutura do payload
- [x] validateInstallation() - Validar dados de instalação
- [x] validatePlatform() - Validar plataforma
- [x] validateCampaign() - Validar campanha
- [x] validateOrigin() - Validar origem (media source)
- [x] validateEventValue() - Validar valor do evento
- [x] isDuplicateEvent() - Verificar duplicidade
- [x] validateEventTiming() - Validar timing do evento
- [x] getValidationSummary() - Resumo de validação

#### AppsFlyerWebhookValidator (Disabled)
- [x] Criar validator
- [x] validate() - Validar webhook
- [x] isEnabled() - Verificar se está habilitado
- [x] Desabilitado por padrão

#### AppsFlyerSignatureValidator (Disabled)
- [x] Criar validator
- [x] validate() - Validar assinatura
- [x] isEnabled() - Verificar se está habilitado
- [x] Desabilitado por padrão

#### AppsFlyerPayloadValidator (Disabled)
- [x] Criar validator
- [x] validate() - Validar payload
- [x] isEnabled() - Verificar se está habilitado
- [x] Desabilitado por padrão

### CONFIGURAÇÃO

#### config/appsflyer.php
- [x] Criar arquivo de configuração
- [x] enabled - Habilitar/desabilitar integração
- [x] api_key - Chave da API
- [x] dev_key - Chave de desenvolvimento
- [x] app_id_android - ID do app Android
- [x] app_id_ios - ID do app iOS
- [x] onelink_template - Template OneLink
- [x] endpoint - Endpoint da API
- [x] webhook - Configuração de webhook
- [x] events - Configuração de eventos
- [x] validation - Regras de validação
- [x] retry - Configuração de retry
- [x] logging - Configuração de logging
- [x] Todos inicialmente vazios

### CONTROLLER

#### AppsFlyerController
- [x] adminIndex() - Listagem de eventos
- [x] view() - Detalhes do evento
- [x] validate() - Validar evento (POST)
- [x] reject() - Rejeitar evento (POST)
- [x] requireAdmin() - Middleware de admin
- [x] CSRF protection

### VIEWS

#### admin/appsflyer.php
- [x] Status da integração
- [x] Cards de estatísticas
- [x] Lista de eventos recentes
- [x] Filtros (status, plataforma, tipo de instalação, período)
- [x] Lista de todos os eventos
- [x] Ações (validar, rejeitar)
- [x] Badges de status com ícones

#### admin/appsflyer-view.php
- [x] Detalhes do evento
- [x] Status badge
- [x] Informações do evento
- [x] Payload original
- [x] Ações (validar, rejeitar)

### ROTAS

#### Admin Routes
- [x] GET /admin/appsflyer - Listagem
- [x] GET /admin/appsflyer/{id} - Detalhes
- [x] POST /admin/appsflyer/validar - Validar
- [x] POST /admin/appsflyer/rejeitar - Rejeitar

### EVENT LOGGING

#### Eventos de AppsFlyer
- [x] APPSFLYER_EVENTO_RECEBIDO - Evento recebido
- [x] APPSFLYER_EVENTO_PROCESSADO - Evento processado
- [x] APPSFLYER_EVENTO_VALIDADO - Evento validado
- [x] APPSFLYER_EVENTO_REJEITADO - Evento rejeitado

#### EventLogger Methods
- [x] logAppsflyerEventoRecebido()
- [x] logAppsflyerEventoProcessado()
- [x] logAppsflyerEventoValidado()
- [x] logAppsflyerEventoRejeitado()

### MODELS

#### Evento Model
- [x] Adicionar constantes de eventos AppsFlyer
- [x] Adicionar labels de eventos AppsFlyer
- [x] Adicionar ícones de eventos AppsFlyer

#### EventLogger
- [x] Adicionar métodos de log AppsFlyer

### SEGURANÇA

#### Validators (Disabled)
- [x] AppsFlyerWebhookValidator - Desabilitado
- [x] AppsFlyerSignatureValidator - Desabilitado
- [x] AppsFlyerPayloadValidator - Desabilitado
- [x] Todos preparados para integração futura

### ARQUITETURA

#### SOLID Principles
- [x] Single Responsibility - Cada classe com responsabilidade única
- [x] Open/Closed - Aberto para extensão, fechado para modificação
- [x] Liskov Substitution - Interfaces e implementações intercambiáveis
- [x] Interface Segregation - Interfaces focadas e específicas
- [x] Dependency Inversion - Módulos de alto nível não dependem de baixo nível

#### Isolation
- [x] Toda comunicação AppsFlyer isolada em Services
- [x] Controllers não conversam diretamente com AppsFlyer
- [x] Repository Pattern implementado
- [x] Service Layer implementada

### ARQUIVOS ALTERADOS

#### Database
- `database/migration_etapa16.sql` - Migration da tabela appsflyer_events

#### Enums
- `enums/InstallType.php` - Enum de tipo de instalação
- `enums/AppsFlyerStatus.php` - Enum de status AppsFlyer

#### Models
- `models/AppsFlyerEvent.php` - Model de eventos AppsFlyer
- `models/Evento.php` - Adicionados eventos AppsFlyer

#### Repositories
- `repositories/AppsFlyerRepository.php` - Repository de AppsFlyer

#### Services
- `services/AppsFlyerService.php` - Serviço de AppsFlyer
- `services/AppsFlyerValidationService.php` - Serviço de validação
- `services/AppsFlyerWebhookValidator.php` - Validator de webhook (disabled)
- `services/AppsFlyerSignatureValidator.php` - Validator de assinatura (disabled)
- `services/AppsFlyerPayloadValidator.php` - Validator de payload (disabled)

#### Config
- `config/appsflyer.php` - Configuração de AppsFlyer

#### Controllers
- `controllers/AppsFlyerController.php` - Controller de AppsFlyer

#### Views
- `views/admin/appsflyer.php` - Listagem de eventos
- `views/admin/appsflyer-view.php` - Detalhes de evento

#### Core
- `core/EventLogger.php` - Métodos de log AppsFlyer

#### Config
- `config/routes.php` - Rotas de AppsFlyer

#### Docs
- `docs/appsflyer-integration.md` - Documentação técnica

### VALIDAÇÕES

#### Testar Migration
- [ ] Executar migration_etapa16.sql no phpMyAdmin
- [ ] Verificar tabela appsflyer_events criada
- [ ] Verificar índices criados
- [ ] Verificar ENUMs criados

#### Testar Enums
- [ ] Testar InstallType::FIRST_INSTALL
- [ ] Testar InstallType::REINSTALL
- [ ] Testar InstallType::REENGAGEMENT
- [ ] Testar InstallType::UNKNOWN
- [ ] Testar AppsFlyerStatus::PENDING
- [ ] Testar AppsFlyerStatus::RECEIVED
- [ ] Testar AppsFlyerStatus::VALIDATED
- [ ] Testar AppsFlyerStatus::INVALID

#### Testar Model
- [ ] Testar create()
- [ ] Testar findById()
- [ ] Testar findByUsuario()
- [ ] Testar findByIndicacao()
- [ ] Testar findByAppsflyerId()
- [ ] Testar findAll() com filtros
- [ ] Testar updateStatus()
- [ ] Testar getStats()
- [ ] Testar countByStatus()
- [ ] Testar listRecent()

#### Testar Repository
- [ ] Testar existsByAppsflyerId()
- [ ] Testar todos os métodos do repository

#### Testar Service
- [ ] Testar processEvent()
- [ ] Testar validateEvent()
- [ ] Testar rejectEvent()
- [ ] Testar isEnabled()
- [ ] Testar getStats()

#### Testar ValidationService
- [ ] Testar validatePayload()
- [ ] Testar validateInstallation()
- [ ] Testar validatePlatform()
- [ ] Testar validateCampaign()
- [ ] Testar validateOrigin()
- [ ] Testar validateEventValue()
- [ ] Testar isDuplicateEvent()
- [ ] Testar validateEventTiming()
- [ ] Testar getValidationSummary()

#### Testar Validators (Disabled)
- [ ] Verificar AppsFlyerWebhookValidator desabilitado
- [ ] Verificar AppsFlyerSignatureValidator desabilitado
- [ ] Verificar AppsFlyerPayloadValidator desabilitado

#### Testar Controller
- [ ] Testar adminIndex()
- [ ] Testar view()
- [ ] Testar validate()
- [ ] Testar reject()
- [ ] Testar requireAdmin()

#### Testar Views
- [ ] Testar listagem de eventos
- [ ] Testar filtros
- [ ] Testar estatísticas
- [ ] Testar detalhes de evento
- [ ] Testar ações de validar/rejeitar

#### Testar Rotas
- [ ] Testar GET /admin/appsflyer
- [ ] Testar GET /admin/appsflyer/{id}
- [ ] Testar POST /admin/appsflyer/validar
- [ ] Testar POST /admin/appsflyer/rejeitar

#### Testar Event Logging
- [ ] Testar logAppsflyerEventoRecebido()
- [ ] Testar logAppsflyerEventoProcessado()
- [ ] Testar logAppsflyerEventoValidado()
- [ ] Testar logAppsflyerEventoRejeitado()
- [ ] Verificar eventos registrados

### NÃO IMPLEMENTADO (FUTURO)
- [x] Webhook endpoint (arquitetura preparada)
- [x] AppsFlyer SDK (arquitetura preparada)
- [x] Deep Link (arquitetura preparada)
- [x] Deferred Deep Link (arquitetura preparada)
- [x] Instalação (arquitetura preparada)
- [x] API calls (arquitetura preparada)
- [x] WhatsApp
- [x] E-mail
- [x] Cashback
- [x] Push Notification
- [x] PIX

### MANUTIDO
- [x] Arquitetura MVC
- [x] Repository Pattern
- [x] Service Layer
- [x] SOLID Principles
- [x] Isolation of external communication
- [x] Mobile First
- [x] Layout Minas Mais
- [x] Paleta atual

### INSTRUÇÕES DE DEPLOY

1. Executar migration_etapa16.sql no phpMyAdmin
2. Configurar config/appsflyer.php quando necessário
3. Deploy para Hostinger
4. Acessar /admin/appsflyer
5. Verificar status da integração
6. Testar filtros e ações
7. Verificar logs de eventos
8. Documentação disponível em docs/appsflyer-integration.md

### NOTAS

- Arquitetura completa para integração AppsFlyer
- Todos os serviços preparados seguindo SOLID
- Comunicação AppsFlyer isolada em Services
- Validators preparados mas desabilitados
- Configuração completa em config/appsflyer.php
- Painel admin com filtros e ações
- Event logging completo
- Documentação técnica detalhada
- Pronto para integração futura

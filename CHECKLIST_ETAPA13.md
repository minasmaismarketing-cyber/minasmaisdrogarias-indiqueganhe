# CHECKLIST - Etapa 13 — Motor de Validação das Indicações

### OBJETIVO
Criar toda a lógica interna responsável por validar automaticamente uma indicação, preparando a arquitetura para integrações futuras (AppsFlyer, VTEX, WhatsApp).

### MIGRAÇÃO

#### Tabela validacoes
- [x] Criar migration migration_etapa13.sql
- [x] Campos: id, indicacao_id, usuario_id, status, motivo, validado_em, created_at, updated_at
- [x] Status: PENDENTE, EM_ANALISE, VALIDADO, INVALIDADO, CANCELADO
- [x] Índices: indicacao_id, usuario_id, status, validado_em

#### Tabela historico_validacoes
- [x] Criar migration migration_etapa13.sql
- [x] Campos: id, validacao_id, status_anterior, status_novo, descricao, usuario_admin, created_at
- [x] Índices: validacao_id, created_at

### MODELOS

#### Validacao Model
- [x] create() - Criar validação
- [x] findById() - Buscar por ID
- [x] findByIndicacao() - Buscar por indicação
- [x] findByUsuario() - Listar por usuário
- [x] findAll() - Listar com filtros (status, cpf, nome, email, telefone, período)
- [x] updateStatus() - Atualizar status e motivo
- [x] getStats() - Estatísticas de validações
- [x] countByStatus() - Contar por status
- [x] statusLabel() - Label legível do status
- [x] statusIcon() - Ícone do status (emoji)

#### HistoricoValidacao Model
- [x] create() - Criar registro de histórico
- [x] findByValidacao() - Listar histórico de validação
- [x] findAll() - Listar com filtros

### SERVIÇOS

#### ValidationService
- [x] setValidationProvider() - Configurar provider externo
- [x] createValidation() - Criar validação para indicação
- [x] validateIndication() - Validar com regras:
  - [x] Indicação existente
  - [x] Usuário existente
  - [x] Indicador existente
  - [x] Indicador diferente do indicado
  - [x] CPF diferente
  - [x] Telefone diferente
  - [x] Email diferente
  - [x] Campanha ativa
  - [x] Indicação ainda não validada
  - [x] Provider externo (se disponível)
- [x] startValidation() - Iniciar análise
- [x] approveValidation() - Aprovar validação
- [x] rejectValidation() - Rejeitar validação com motivo
- [x] cancelValidation() - Cancelar validação
- [x] getHistory() - Obter histórico de alterações

### INTERFACES FUTURAS

#### ValidationProviderInterface
- [x] validate() - Método de validação
- [x] isAvailable() - Verificar disponibilidade

#### AppsFlyerValidationProvider
- [x] Implementar interface (stub)
- [x] TODO: Integração AppsFlyer API

#### VTEXValidationProvider
- [x] Implementar interface (stub)
- [x] TODO: Integração VTEX API

#### NotificationProviderInterface
- [x] send() - Enviar notificação
- [x] isAvailable() - Verificar disponibilidade

#### WhatsAppNotificationProvider
- [x] Implementar interface (stub)
- [x] TODO: Integração WhatsApp API

### CONTROLLER

#### ValidacaoController
- [x] adminIndex() - Listagem com filtros
- [x] view() - Detalhes de validação com histórico
- [x] start() - Iniciar validação
- [x] approve() - Aprovar validação
- [x] reject() - Rejeitar validação
- [x] cancel() - Cancelar validação
- [x] requireAdmin() - Middleware de admin

### ROTAS

#### Admin Validações
- [x] GET /admin/validacoes - Listagem
- [x] GET /admin/validacoes/{id} - Detalhes
- [x] POST /admin/validacoes/iniciar - Iniciar
- [x] POST /admin/validacoes/aprovar - Aprovar
- [x] POST /admin/validacoes/rejeitar - Rejeitar
- [x] POST /admin/validacoes/cancelar - Cancelar

### VIEWS

#### admin/validacoes.php
- [x] Filtros: status, cpf, nome, email, telefone, período
- [x] Cards de estatísticas: total, pendentes, em análise, validadas, inválidas, canceladas
- [x] Taxa de aprovação (progress bar)
- [x] Lista de validações com ações
- [x] Badges de status com ícones
- [x] Motivo exibido quando existir

#### admin/validacao-view.php
- [x] Informações da validação
- [x] Histórico de alterações (timeline)
- [x] Ações baseadas no status:
- [x] PENDENTE: Iniciar análise
- [x] EM_ANALISE: Aprovar, Rejeitar, Cancelar
- [x] Outros: Cancelar (se aplicável)

### DASHBOARD ADMIN

#### Indicadores de Validação
- [x] Indicações pendentes
- [x] Em análise
- [x] Validadas
- [x] Inválidas
- [x] Canceladas
- [x] Taxa de aprovação
- [x] Link para painel de validações

### DASHBOARD USUÁRIO

#### Status de Validação
- [x] Exibir ícone de status (🟡🟠🟢🔴)
- [x] Exibir motivo quando invalidado
- [x] Label legível do status

### EVENT LOGGING

#### Eventos de Validação
- [x] VALIDACAO_CRIADA - Validação criada
- [x] VALIDACAO_INICIADA - Validação iniciada
- [x] VALIDACAO_CONCLUIDA - Validação concluída
- [x] VALIDACAO_APROVADA - Validação aprovada
- [x] VALIDACAO_INVALIDADA - Validação invalidada (com motivo)
- [x] VALIDACAO_CANCELADA - Validação cancelada

#### EventLogger Methods
- [x] logValidacaoCriada()
- [x] logValidacaoIniciada()
- [x] logValidacaoConcluida()
- [x] logValidacaoAprovada()
- [x] logValidacaoInvalidada()
- [x] logValidacaoCancelada()

### REGRAS DE VALIDAÇÃO

#### Regras Implementadas
- [x] Indicação deve existir
- [x] Usuário deve existir
- [x] Indicador deve existir
- [x] Indicador ≠ Indicado (autoindicação)
- [x] CPF indicador ≠ CPF indicado
- [x] Telefone indicador ≠ Telefone indicado
- [x] Email indicador ≠ Email indicado
- [x] Campanha deve estar ativa
- [x] Indicação não pode estar já validada

### ARQUITETURA

#### Desacoplamento
- [x] ValidationService independente de providers
- [x] Interfaces para integrações futuras
- [x] Troca de provider sem alterar código existente
- [x] Lógica de validação centralizada

### ARQUIVOS ALTERADOS

#### Database
- `database/migration_etapa13.sql` - Migration das tabelas validacoes e historico_validacoes

#### Models
- `models/Validacao.php` - Model de validações
- `models/HistoricoValidacao.php` - Model de histórico
- `models/Evento.php` - Adicionados eventos de validação

#### Services
- `services/ValidationProviderInterface.php` - Interface de validação
- `services/AppsFlyerValidationProvider.php` - Provider AppsFlyer (stub)
- `services/VTEXValidationProvider.php` - Provider VTEX (stub)
- `services/NotificationProviderInterface.php` - Interface de notificação
- `services/WhatsAppNotificationProvider.php` - Provider WhatsApp (stub)
- `services/ValidationService.php` - Serviço de validação

#### Controllers
- `controllers/ValidacaoController.php` - CRUD de validações
- `controllers/AdminController.php` - Dashboard com indicadores de validação

#### Config
- `config/routes.php` - Rotas de validações

#### Views
- `views/admin/validacoes.php` - Listagem de validações
- `views/admin/validacao-view.php` - Detalhes de validação
- `views/admin/dashboard.php` - Dashboard com indicadores
- `views/indicacoes/index.php` - Dashboard usuário com motivo

#### Core
- `core/EventLogger.php` - Métodos de log de validação

### VALIDAÇÕES

#### Testar Migration
- [ ] Executar migration_etapa13.sql no phpMyAdmin
- [ ] Verificar tabela validacoes criada
- [ ] Verificar tabela historico_validacoes criada
- [ ] Verificar índices criados

#### Testar ValidationService
- [ ] Criar validação para indicação
- [ ] Validar indicação com regras
- [ ] Iniciar análise
- [ ] Aprovar validação
- [ ] Rejeitar validação com motivo
- [ ] Cancelar validação
- [ ] Verificar histórico de alterações

#### Testar Painel Admin
- [ ] Listar validações com filtros
- [ ] Ver estatísticas de validações
- [ ] Ver taxa de aprovação
- [ ] Aprovar validação manualmente
- [ ] Rejeitar validação com motivo
- [ ] Cancelar validação
- [ ] Ver histórico de alterações

#### Testar Dashboard Usuário
- [ ] Ver status de validação com ícone
- [ ] Ver motivo quando invalidado

#### Testar Event Logging
- [ ] Verificar eventos de validação registrados
- [ ] Verificar eventos no dashboard admin

### NÃO IMPLEMENTADO (FUTURO)
- [x] AppsFlyer (arquitetura preparada)
- [x] VTEX (arquitetura preparada)
- [x] WhatsApp (arquitetura preparada)
- [x] Push Notification
- [x] Disparo de cupons
- [x] Cashback

### MANUTIDO
- [x] Arquitetura MVC
- [x] Mobile First
- [x] Layout Minas Mais
- [x] Paleta atual

### INSTRUÇÕES DE DEPLOY

1. Executar migration_etapa13.sql no phpMyAdmin
2. Deploy para Hostinger
3. Testar criação de validações
4. Testar aprovação/rejeição manual
5. Verificar histórico de alterações
6. Verificar eventos de validação
7. Verificar indicadores no dashboard admin

### NOTAS

- Sistema completo de validação de indicações
- Arquitetura desacoplada para integrações futuras
- Histórico completo de alterações
- Painel admin com filtros avançados
- Dashboard com indicadores em tempo real
- Event logging para auditoria
- Regras de validação implementadas
- Taxa de aprovação calculada automaticamente

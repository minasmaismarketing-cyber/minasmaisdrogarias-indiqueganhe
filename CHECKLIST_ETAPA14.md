# CHECKLIST - Etapa 14 — Sistema de Cupons

### OBJETIVO
Criar todo o gerenciamento interno de cupons do programa Indique e Ganhe, preparando a arquitetura para integrações futuras (VTEX).

### MIGRAÇÃO

#### Tabela cupons
- [x] Criar migration migration_etapa14.sql
- [x] Campos: id, codigo, usuario_id, indicacao_id, campanha_id, tipo, valor, status, origem, validade, utilizado_em, created_at, updated_at
- [x] Status: DISPONIVEL, RESERVADO, UTILIZADO, EXPIRADO, CANCELADO
- [x] Tipo: PERCENTUAL, VALOR_FIXO
- [x] Índices: codigo (UNIQUE), usuario_id, indicacao_id, campanha_id, status, validade

#### Tabela historico_cupons
- [x] Criar migration migration_etapa14.sql
- [x] Campos: id, cupom_id, status_anterior, status_novo, descricao, usuario_admin, created_at
- [x] Índices: cupom_id, created_at

### MODELOS

#### Cupom Model
- [x] create() - Criar cupom
- [x] findById() - Buscar por ID
- [x] findByCodigo() - Buscar por código
- [x] findByUsuario() - Listar por usuário
- [x] findByIndicacao() - Buscar por indicação
- [x] findAll() - Listar com filtros (codigo, usuario_id, campanha_id, status, período)
- [x] updateStatus() - Atualizar status e utilizado_em
- [x] getStats() - Estatísticas de cupons
- [x] countByStatus() - Contar por.status
- [x] checkExpirados() - Expirar cupons vencidos automaticamente
- [x] statusLabel() - Label legível do status
- [x] statusIcon() - Ícone do status (emoji)
- [x] tipoLabel() - Label legível do tipo

#### HistoricoCupom Model
- [x] create() - Criar registro de histórico
- [x] findByCupom() - Listar histórico de cupom
- [x] findAll() - Listar com filtros

### REPOSITORY

#### CupomRepository
- [x] findById() - Buscar por ID
- [x] findByCodigo() - Buscar por código
- [x] findByUsuario() - Listar por usuário
- [x] findByIndicacao() - Buscar por indicação
- [x] findAll() - Listar com filtros
- [x] create() - Criar cupom
- [x] updateStatus() - Atualizar status
- [x] getStats() - Estatísticas
- [x] checkExpirados() - Expirar cupons
- [x] createHistorico() - Criar histórico
- [x] getHistorico() - Obter histórico
- [x] codigoExiste() - Verificar unicidade
- [x] cupomExisteParaIndicacao() - Verificar duplicidade

### SERVIÇOS

#### CupomService
- [x] setCouponProvider() - Configurar provider externo
- [x] generateForIndicacao() - Gerar cupom para indicação validada
- [x] reserve() - Reservar cupom
- [x] use() - Marcar como utilizado
- [x] cancel() - Cancelar cupom
- [x] expire() - Expirar cupom
- [x] reactivate() - Reativar cupom
- [x] checkExpiredCoupons() - Verificar expirados
- [x] getHistory() - Obter histórico

### INTERFACES FUTURAS

#### CouponProviderInterface
- [x] generateCode() - Gerar código
- [x] validate() - Validar código
- [x] isAvailable() - Verificar disponibilidade

#### InternalCouponProvider
- [x] Implementar interface
- [x] generateCode() - Gerar código MM-XXXXXXXX (8 caracteres)
- [x] validate() - Validar código
- [x] isAvailable() - Sempre disponível
- [x] Garantir unicidade (max 10 tentativas)

#### VTEXCouponProvider
- [x] Implementar interface (stub)
- [x] TODO: Integração VTEX API

### CONTROLLER

#### CuponsController
- [x] adminIndex() - Listagem com filtros
- [x] view() - Detalhes de cupom com histórico
- [x] cancel() - Cancelar cupom
- [x] expire() - Expirar cupom
- [x] reactivate() - Reativar cupom
- [x] userIndex() - Listagem de cupons do usuário
- [x] requireAdmin() - Middleware de admin

### ROTAS

#### Admin Cupons
- [x] GET /admin/cupons - Listagem
- [x] GET /admin/cupons/{id} - Detalhes
- [x] POST /admin/cupons/cancelar - Cancelar
- [x] POST /admin/cupons/expirar - Expirar
- [x] POST /admin/cupons/reativar - Reativar

#### Usuário Cupons
- [x] GET /meus-cupons - Listagem de cupons do usuário

### VIEWS

#### admin/cupons.php
- [x] Filtros: código, status, período
- [x] Cards de estatísticas: total, disponíveis, reservados, utilizados, expirados, cancelados
- [x] Lista de cupons com ações
- [x] Badges de status com ícones
- [x] Ações baseadas no status:
- [x] DISPONIVEL/RESERVADO: Cancelar
- [x] DISPONIVEL: Expirar
- [x] CANCELADO/EXPIRADO: Reativar

#### admin/cupom-view.php
- [x] Informações do cupom (código, usuário, campanha, valor, validade, origem)
- [x] Histórico de alterações (timeline)
- [x] Ações baseadas no status

#### cupons/index.php
- [x] Cards de estatísticas: disponíveis, utilizados, expirados
- [x] Lista de cupons do usuário
- [x] Código destacado
- [x] Botão copiar código (apenas para DISPONIVEL)
- [x] Exibir valor, campanha, validade
- [x] Expirados em cinza
- [x] JavaScript para copiar código

### DASHBOARD ADMIN

#### Links de Cupons
- [x] Link "Gerenciar Cupons" em Ações Rápidas

### DASHBOARD USUÁRIO

#### Links de Cupons
- [x] Link "Meus cupons" em Ações Rápidas
- [x] Link "Minhas indicações" em Ações Rápidas

### EVENT LOGGING

#### Eventos de Cupom
- [x] CUPOM_CRIADO - Cupom criado
- [x] CUPOM_CANCELADO - Cupom cancelado
- [x] CUPOM_EXPIRADO - Cupom expirado
- [x] CUPOM_RESERVADO - Cupom reservado
- [x] CUPOM_UTILIZADO - Cupom utilizado

#### EventLogger Methods
- [x] logCupomCriado()
- [x] logCupomCancelado()
- [x] logCupomExpirado()
- [x] logCupomReservado()
- [x] logCupomUtilizado()

### GERAÇÃO AUTOMÁTICA

#### Integração com ValidationService
- [x] Adicionar CupomService ao ValidationService
- [x] Gerar cupom automaticamente quando validação é aprovada
- [x] Tratamento de erro na geração (log)
- [x] Cupom gerado com base na campanha ativa
- [x] Validade de 30 dias
- [x] Origem: INDICACAO_VALIDADA

### REGRAS DE NEGÓCIO

#### Regras Implementadas
- [x] Um cupom pertence a 1 usuário
- [x] Um cupom pertence a 1 indicação
- [x] Um cupom pertence a 1 campanha
- [x] Não permitir duplicidade (verificar por indicação)
- [x] Código único (MM-XXXXXXXX)
- [x] Cupom gerado automaticamente ao validar indicação
- [x] Cupons expiram automaticamente após validade
- [x] Cupons utilizados não podem ser cancelados
- [x] Cupons cancelados/expirados podem ser reativados

### ARQUITETURA

#### Repository Pattern
- [x] CupomRepository separado do Model
- [x] Lógica de negócio no Service
- [x] Interface para providers externos
- [x] Troca de provider sem alterar código existente

#### Desacoplamento
- [x] InternalCouponProvider independente
- [x] VTEXCouponProvider (stub) preparado
- [x] Lógica de geração de código centralizada
- [x] Validação de código centralizada

### ARQUIVOS ALTERADOS

#### Database
- `database/migration_etapa14.sql` - Migration das tabelas cupons e historico_cupons

#### Models
- `models/Cupom.php` - Model de cupons
- `models/HistoricoCupom.php` - Model de histórico
- `models/Evento.php` - Adicionados eventos de cupom

#### Repositories
- `repositories/CupomRepository.php` - Repository de cupons

#### Services
- `services/CouponProviderInterface.php` - Interface de cupom
- `services/InternalCouponProvider.php` - Provider interno
- `services/VTEXCouponProvider.php` - Provider VTEX (stub)
- `services/CupomService.php` - Serviço de cupons
- `services/ValidationService.php` - Integração com geração automática

#### Controllers
- `controllers/CuponsController.php` - CRUD de cupons

#### Config
- `config/routes.php` - Rotas de cupons

#### Views
- `views/admin/cupons.php` - Listagem de cupons
- `views/admin/cupom-view.php` - Detalhes de cupom
- `views/admin/dashboard.php` - Link para cupons
- `views/cupons/index.php` - Lista de cupons do usuário
- `views/dashboard/index.php` - Link para meus cupons

#### Core
- `core/EventLogger.php` - Métodos de log de cupom

### VALIDAÇÕES

#### Testar Migration
- [ ] Executar migration_etapa14.sql no phpMyAdmin
- [ ] Verificar tabela cupons criada
- [ ] Verificar tabela historico_cupons criada
- [ ] Verificar índices criados
- [ ] Verificar constraint UNIQUE em codigo

#### Testar CupomService
- [ ] Gerar cupom para indicação validada
- [ ] Verificar unicidade do código
- [ ] Reservar cupom
- [ ] Marcar cupom como utilizado
- [ ] Cancelar cupom
- [ ] Expirar cupom
- [ ] Reativar cupom
- [ ] Verificar histórico de alterações
- [ ] Verificar expiração automática

#### Testar Painel Admin
- [ ] Listar cupons com filtros
- [ ] Ver estatísticas de cupons
- [ ] Cancelar cupom
- [ ] Expirar cupom
- [ ] Reativar cupom
- [ ] Ver histórico de alterações

#### Testar Dashboard Usuário
- [ ] Ver lista de cupons
- [ ] Copiar código
- [ ] Ver status com ícones
- [ ] Ver cupons expirados em cinza

#### Testar Geração Automática
- [ ] Aprovar validação
- [ ] Verificar cupom gerado
- [ ] Verificar código único
- [ ] Verificar validade de 30 dias
- [ ] Verificar origem INDICACAO_VALIDADA

#### Testar Event Logging
- [ ] Verificar eventos de cupom registrados
- [ ] Verificar eventos no dashboard admin

### NÃO IMPLEMENTADO (FUTURO)
- [x] VTEX (arquitetura preparada)
- [x] AppsFlyer
- [x] WhatsApp
- [x] E-mail
- [x] Cashback
- [x] Push Notification
- [x] PIX

### MANUTIDO
- [x] Arquitetura MVC
- [x] Repository Pattern
- [x] Services
- [x] Mobile First
- [x] Layout Minas Mais
- [x] Paleta atual

### INSTRUÇÕES DE DEPLOY

1. Executar migration_etapa14.sql no phpMyAdmin
2. Deploy para Hostinger
3. Testar geração de cupom ao aprovar validação
4. Testar ações admin (cancelar, expirar, reativar)
5. Testar dashboard usuário (copiar código)
6. Verificar histórico de alterações
7. Verificar eventos de cupom
8. Verificar expiração automática

### NOTAS

- Sistema completo de gerenciamento de cupons
- Arquitetura desacoplada para integrações futuras
- Histórico completo de alterações
- Painel admin com filtros avançados
- Dashboard usuário com cópia de código
- Event logging para auditoria
- Geração automática ao validar indicação
- Código único (MM-XXXXXXXX)
- Validade de 30 dias
- Repository Pattern implementado
- Interface para VTEX preparada

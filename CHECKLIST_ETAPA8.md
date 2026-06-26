# CHECKLIST - ETAPA 8
## Fluxo Completo do Indicado

### OBJETIVO
Criar fluxo completo do indicado, desde o recebimento do link até o cadastro, sem integrações externas (AppsFlyer, VTEX, KOBE, WhatsApp).

### MIGRAÇÃO DO BANCO DE DADOS
- [ ] Executar `database/migration_etapa8.sql` no phpMyAdmin
  - Banco: u352670812_indice (Hostinger)
  - Tabela: `indicados`

### ESTRUTURA DA TABELA indicados
```sql
CREATE TABLE IF NOT EXISTS indicados (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    codigo_indicador VARCHAR(10) NOT NULL,
    usuario_indicador_id INT UNSIGNED NULL,
    nome VARCHAR(150) NOT NULL,
    cpf VARCHAR(11) NOT NULL,
    telefone VARCHAR(15) NOT NULL,
    email VARCHAR(150) NOT NULL,
    senha_hash VARCHAR(255) NULL,
    status ENUM('LINK_ACESSADO', 'CADASTRO_INICIADO', 'CADASTRO_CONCLUIDO', 'AGUARDANDO_VALIDACAO', 'VALIDADO', 'INVALIDADO') NOT NULL DEFAULT 'LINK_ACESSADO',
    origem VARCHAR(50) NULL,
    aceite_lgpd TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY idx_indicados_cpf (cpf),
    UNIQUE KEY idx_indicados_email (email),
    UNIQUE KEY idx_indicados_telefone (telefone),
    KEY idx_indicados_codigo (codigo_indicador),
    KEY idx_indicados_usuario (usuario_indicador_id),
    KEY idx_indicados_status (status),
    CONSTRAINT fk_indicados_usuario
        FOREIGN KEY (usuario_indicador_id) REFERENCES usuarios (id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### MODELS CRIADOS

#### Indicado Model (`models/Indicado.php`)
- [x] `create()` - Criar indicado
- [x] `findByCodigo()` - Buscar por código
- [x] `findByCpf()` - Buscar por CPF
- [x] `findByEmail()` - Buscar por email
- [x] `findByTelefone()` - Buscar por telefone
- [x] `findById()` - Buscar por ID
- [x] `updateStatus()` - Atualizar status
- [x] `update()` - Atualizar dados
- [x] `listByIndicador()` - Listar por indicador
- [x] `countByIndicador()` - Contar por indicador
- [x] `cpfExists()` - Verificar se CPF existe
- [x] `emailExists()` - Verificar se email existe
- [x] `telefoneExists()` - Verificar se telefone existe
- [x] `codigoExists()` - Verificar se código existe
- [x] `statusLabel()` - Label do status
- [x] `getTimeline()` - Obter timeline do indicado

### MODELS ATUALIZADOS

#### Bootstrap (`config/bootstrap.php`)
- [x] Adicionado `models/Indicado.php`

### CONTROLLERS CRIADOS

#### IndicadosController (`controllers/IndicadosController.php`)
- [x] `index()` - Página de convite (/convite)
- [x] `participar()` - Processar participação (/participar)
- [x] `cadastro()` - Formulário de cadastro (/cadastro-indicado)
- [x] `salvar()` - Salvar cadastro (/salvar-indicado)
- [x] `finalizado()` - Página final (/finalizado)

### CONTROLLERS ATUALIZADOS

#### DashboardController (`controllers/DashboardController.php`)
- [x] Adicionado busca de indicados com timeline
- [x] Adicionado indicados aos dados da view

### VIEWS CRIADAS

#### Indicado Views
- [x] `views/indicados/convite.php` - Página de convite
- [x] `views/indicados/cadastro.php` - Formulário de cadastro
- [x] `views/indicados/finalizado.php` - Página final

### VIEWS ATUALIZADAS

#### Dashboard View
- [x] `views/dashboard/index.php` - Adicionado indicados com timeline

### ROTAS ADICIONADAS

#### Indicados Routes
- [x] `GET /convite` - Página de convite
- [x] `POST /participar` - Processar participação
- [x] `GET /cadastro-indicado` - Formulário de cadastro
- [x] `POST /salvar-indicado` - Salvar cadastro
- [x] `GET /finalizado` - Página final

### CSS ADICIONADO

#### Indicado Flow Styles
- [x] `.invite-content` - Conteúdo do convite
- [x] `.invite-content__text` - Texto do convite
- [x] `.invite-steps` - Passos do convite
- [x] `.invite-step` - Passo individual
- [x] `.invite-step__number` - Número do passo
- [x] `.invite-step__label` - Label do passo
- [x] `.benefits-list` - Lista de benefícios
- [x] `.benefits-list__item` - Item de benefício
- [x] `.benefits-list__icon` - Ícone de benefício
- [x] `.benefits-list__text` - Texto de benefício
- [x] `.success-content` - Conteúdo de sucesso
- [x] `.success-content__icon` - Ícone de sucesso
- [x] `.success-content__text` - Texto de sucesso
- [x] `.success-actions` - Ações de sucesso
- [x] `.next-steps` - Próximos passos
- [x] `.next-step` - Próximo passo individual
- [x] `.next-step__icon` - Ícone do próximo passo
- [x] `.next-step__content` - Conteúdo do próximo passo
- [x] `.next-step__title` - Título do próximo passo
- [x] `.next-step__text` - Texto do próximo passo
- [x] `.form-group--checkbox` - Grupo de checkbox
- [x] `.checkbox-label` - Label de checkbox
- [x] `.referral-list__timeline` - Timeline na lista
- [x] `.timeline--compact` - Timeline compacta

### FUNCIONALIDADES IMPLEMENTADAS

#### 1. Fluxo do Indicado
- [x] Recebimento do link com ?ref=CODIGO
- [x] Página de convite (/convite)
- [x] Botão "Participar"
- [x] Redirecionamento para cadastro
- [x] Formulário de cadastro completo
- [x] Página final de sucesso

#### 2. Formulário de Cadastro
- [x] Nome completo
- [x] CPF com máscara
- [x] Telefone com máscara
- [x] Email
- [x] Senha (mínimo 6 caracteres)
- [x] Aceite LGPD (checkbox obrigatório)

#### 3. Validações
- [x] CPF já cadastrado (indicados e usuarios)
- [x] CPF indicador = CPF indicado (auto-indicação bloqueada)
- [x] Telefone duplicado (indicados e usuarios)
- [x] Email duplicado (indicados e usuarios)
- [x] Código inválido
- [x] Reenvio formulário (CSRF)
- [x] Rate limit (3 tentativas por hora)

#### 4. Status do Indicado
- [x] LINK_ACESSADO - Link foi acessado
- [x] CADASTRO_INICIADO - Cadastro iniciado
- [x] CADASTRO_CONCLUIDO - Cadastro concluído
- [x] AGUARDANDO_VALIDACAO - Aguardando validação
- [x] VALIDADO - Validado
- [x] INVALIDADO - Invalidado

#### 5. Timeline do Indicado
- [x] Link acessado
- [x] Cadastro iniciado
- [x] Cadastro concluído
- [x] Aguardando validação
- [x] Validado
- [x] Invalidado

#### 6. Dashboard do Indicador
- [x] Mostrar indicados
- [x] Nome do indicado
- [x] Data
- [x] Status
- [x] Progresso (timeline)

#### 7. Página Final
- [x] Mensagem de sucesso
- [x] "Cadastro realizado."
- [x] "Agora falta concluir as próximas etapas para validar sua participação."
- [x] Botão "Entrar"
- [x] Botão "Voltar início"
- [x] Próximos passos

### NÃO IMPLEMENTADO (FUTURO)
- [x] AppsFlyer
- [x] VTEX
- [x] KOBE
- [x] WhatsApp
- [x] Validação de instalação
- [x] Validação de app
- [x] Geração de cupom
- [x] Integração API

### ARQUIVOS ALTERADOS/NOVOS

#### Novos Arquivos
- `database/migration_etapa8.sql`
- `models/Indicado.php`
- `controllers/IndicadosController.php`
- `views/indicados/convite.php`
- `views/indicados/cadastro.php`
- `views/indicados/finalizado.php`
- `CHECKLIST_ETAPA8.md`

#### Arquivos Alterados
- `config/bootstrap.php`
- `config/routes.php`
- `controllers/DashboardController.php`
- `views/dashboard/index.php`
- `assets/css/style.css`

### VALIDAÇÕES

#### Testar Migration
- [ ] Executar `database/migration_etapa8.sql`
- [ ] Verificar tabela criada
- [ ] Verificar índices e chaves estrangeiras

#### Testar Fluxo do Indicado
- [ ] Acessar `/convite?ref=CODIGO` com código válido
- [ ] Verificar página de convite
- [ ] Clicar em "Participar"
- [ ] Verificar redirecionamento para cadastro
- [ ] Preencher formulário
- [ ] Testar validações (CPF duplicado, email duplicado, telefone duplicado)
- [ ] Testar auto-indicação (CPF indicador = CPF indicado)
- [ ] Testar código inválido
- [ ] Testar rate limit (3 tentativas)
- [ ] Verificar página final
- [ ] Testar botão "Entrar"
- [ ] Testar botão "Voltar início"

#### Testar Dashboard
- [ ] Acessar `/dashboard`
- [ ] Verificar indicados listados
- [ ] Verificar timeline de cada indicado
- [ ] Verificar status correto
- [ ] Verificar nome e telefone

#### Testar Máscaras
- [ ] Testar máscara de CPF
- [ ] Testar máscara de telefone

### INSTRUÇÕES DE DEPLOY

1. **Executar migration**: `database/migration_etapa8.sql`
2. **Testar fluxo completo localmente**
3. **Deploy para Hostinger**
4. **Testar em produção**

### NOTAS

- Fluxo completo do indicado implementado sem integrações externas
- Validações robustas (CPF, email, telefone, código, auto-indicação)
- Rate limit para proteção contra spam
- Timeline completa do indicado
- Dashboard atualizado com indicados
- Ainda NÃO implementado: AppsFlyer, VTEX, KOBE, WhatsApp
- Ainda NÃO implementado: validação de instalação, validação de app, geração de cupom, integração API

### STATUS DO SISTEMA

#### Funcionalidades OK
- [x] Login funcionando
- [x] Cadastro funcionando
- [x] Sessão funcionando
- [x] Dashboard acessível
- [x] Geração de código de indicação
- [x] Validação de código
- [x] Proteção contra auto-indicação
- [x] Proteção contra duplicidade
- [x] Link de convite funcionando
- [x] Perfil completo com edição
- [x] Troca de senha
- [x] Listagem de indicações com paginação
- [x] Visualização de prêmios
- [x] Configurações de contato
- [x] Geração de link único por usuário
- [x] Rastreamento de cliques
- [x] Proteção contra spam
- [x] Proteção contra duplicidade de cliques
- [x] Compartilhamento nativo (mobile)
- [x] Compartilhamento via WhatsApp
- [x] Copiar link
- [x] Timeline de indicações
- [x] Estrutura de cupom
- [x] Central de notificações (model)
- [x] Informações de campanha no perfil
- [x] Logo aumentada (110px mobile, 150px desktop)
- [x] Dashboard simplificado (sem duplicidade)
- [x] Código com visual premium
- [x] Status compacto inline
- [x] Mobile mais compacto
- [x] Painel admin funcional
- [x] Gerenciamento de campanhas
- [x] Simulação de fluxo
- [x] Filtros e busca em indicações
- [x] Central de notificações view
- [x] Fluxo completo do indicado
- [x] Validações robustas
- [x] Rate limit
- [x] Timeline do indicado
- [x] Dashboard com indicados

#### Próximas Etapas (Futuro)
- [ ] Integração AppsFlyer
- [ ] Integração VTEX
- [ ] Integração WhatsApp completa
- [ ] Liberação de cupom real
- [ ] Validação de indicação
- [ ] Sistema de prêmios
- [ ] Renovação de link
- [ ] Redirecionamento para AppsFlyer
- [ ] Integração KOBE
- [ ] Integração App
- [ ] Envio de notificações real
- [ ] Validação de instalação
- [ ] Validação de app

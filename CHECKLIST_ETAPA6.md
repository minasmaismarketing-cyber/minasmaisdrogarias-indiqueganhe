# CHECKLIST - ETAPA 6
## Preparação para Validação de Indicações

### ATUALIZAÇÃO VISUAL OBRIGATÓRIA
- [x] Logo aumentada 2x em relação ao tamanho anterior
- [x] Mobile: altura máxima 52px
- [x] Desktop: altura máxima 72px
- [x] Proporção mantida
- [x] Centralizado verticalmente
- [x] Header continua fixo
- [x] Espaçamentos ajustados
- [x] Fundo branco mantido
- [x] Sombra leve mantida
- [x] Estilo inspirado na Minas Mais

### MIGRAÇÃO DO BANCO DE DADOS
- [ ] Executar `database/migration_etapa6.sql` no phpMyAdmin
  - Banco: u352670812_indice (Hostinger)
  - Cria tabelas: `validacoes` e `notificacoes`

### ESTRUTURA DA TABELA validacoes
```sql
CREATE TABLE IF NOT EXISTS validacoes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id INT UNSIGNED NOT NULL,
    cpf_indicado VARCHAR(14) NOT NULL,
    telefone_indicado VARCHAR(20) NOT NULL,
    status ENUM('PENDENTE', 'EM_ANALISE', 'VALIDADO', 'INVALIDADO', 'BENEFICIO_LIBERADO') NOT NULL DEFAULT 'PENDENTE',
    motivo TEXT NULL,
    premio_disponivel TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_validacoes_usuario (usuario_id),
    KEY idx_validacoes_cpf (cpf_indicado),
    KEY idx_validacoes_telefone (telefone_indicado),
    KEY idx_validacoes_status (status),
    CONSTRAINT fk_validacoes_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### ESTRUTURA DA TABELA notificacoes
```sql
CREATE TABLE IF NOT EXISTS notificacoes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id INT UNSIGNED NOT NULL,
    tipo ENUM('informacao', 'sucesso', 'alerta') NOT NULL DEFAULT 'informacao',
    mensagem VARCHAR(255) NOT NULL,
    link VARCHAR(255) NULL,
    lida TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_notificacoes_usuario (usuario_id),
    KEY idx_notificacoes_lida (lida),
    KEY idx_notificacoes_tipo (tipo),
    CONSTRAINT fk_notificacoes_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### STATUS DE VALIDAÇÃO
- PENDENTE - Aguardando validação
- EM_ANALISE - Em análise pela equipe
- VALIDADO - Indicação validada com sucesso
- INVALIDADO - Indicação invalidada
- BENEFICIO_LIBERADO - Benefício liberado para resgate

### MODELS CRIADOS
- [x] `models/Validacao.php`:
  - `create()` - Criar validação
  - `findByUsuario()` - Buscar validações por usuário
  - `findByCpf()` - Buscar por CPF
  - `findByTelefone()` - Buscar por telefone
  - `updateStatus()` - Atualizar status
  - `liberarPremio()` - Liberar prêmio
  - `countByStatus()` - Contar por status
  - `statusLabel()` - Label do status
  - `statusIcon()` - Ícone do status

- [x] `models/Notificacao.php`:
  - `create()` - Criar notificação
  - `findByUsuario()` - Buscar notificações por usuário
  - `markAsRead()` - Marcar como lida
  - `markAllAsRead()` - Marcar todas como lidas
  - `countUnread()` - Contar não lidas
  - `typeLabel()` - Label do tipo
  - `typeIcon()` - Ícone do tipo

### MODELS ATUALIZADOS
- [x] `models/Indicacao.php`:
  - `getTimeline()` - Obter timeline de uma indicação
  - `findById()` - Buscar indicação por ID
  - Timeline mostra: Link enviado → Clique registrado → Cadastro → Validação → Benefício

### CONTROLLERS ATUALIZADOS
- [x] `controllers/IndicacoesController.php`:
  - Passa timeline data para cada indicação na view

### VIEWS ATUALIZADAS
- [x] `views/indicacoes/index.php`:
  - Adicionada timeline em cada card de indicação
  - Mostra progresso com ícones e datas
  - Etapas: Link enviado, Clique registrado, Cadastro, Validação, Benefício

- [x] `views/premios/index.php`:
  - Adicionado box de cupom parcialmente oculto
  - Formato: MINAS-••••
  - Botão "Revelar benefício" (desabilitado)
  - Texto "Em breve"

- [x] `views/perfil/index.php`:
  - Adicionado "Aceite campanha: Sim"
  - Adicionado "Data cadastro"
  - Adicionado "Status participação: Ativo"

### CSS ATUALIZADO
- [x] `assets/css/style.css`:
  - Logo mobile: max-height 52px
  - Logo desktop: max-height 72px
  - `.referral-card__timeline` - Container da timeline
  - `.timeline-title` - Título da timeline
  - `.timeline` - Container dos itens
  - `.timeline__item` - Item da timeline
  - `.timeline__icon` - Ícone do item
  - `.timeline__content` - Conteúdo do item
  - `.timeline__label` - Label do item
  - `.timeline__date` - Data do item
  - `.coupon-box` - Box do cupom
  - `.coupon-code` - Código do cupom
  - `.coupon-code__prefix` - Prefixo MINAS-
  - `.coupon-code__hidden` - Parte oculta ••••
  - `.campaign-info` - Informações da campanha
  - `.campaign-info__item` - Item de informação
  - `.campaign-info__label` - Label
  - `.campaign-info__value` - Valor
  - `.campaign-info__value--success` - Valor verde
  - `.campaign-info__value--active` - Valor vermelho

### BOOTSTRAP ATUALIZADO
- [x] `config/bootstrap.php`:
  - Adicionado `models/Validacao.php`
  - Adicionado `models/Notificacao.php`

### FUNCIONALIDADES IMPLEMENTADAS

#### 1. Timeline de Indicações
- [x] Mostra progresso de cada indicação
- [x] Etapas com ícones emoji
- [x] Datas de cada etapa
- [x] Visualização clara do status atual

#### 2. Estrutura de Cupom
- [x] Box fechado com borda dashed
- [x] Código parcialmente oculto (MINAS-••••)
- [x] Botão "Revelar benefício" desabilitado
- [x] Texto "Em breve"

#### 3. Central de Notificações
- [x] Model criado para gerenciar notificações
- [x] Tipos: informação, sucesso, alerta
- [x] Marcação de lida/não lida
- [x] Contagem de não lidas
- [x] Links opcionais para redirecionamento

#### 4. Informações de Campanha no Perfil
- [x] Aceite campanha: Sim
- [x] Data cadastro
- [x] Status participação: Ativo

#### 5. Logo Aumentada
- [x] Mobile: 52px (era 36px)
- [x] Desktop: 72px (mantido)
- [x] Proporção mantida
- [x] Centralizado verticalmente

### NÃO IMPLEMENTADO (FUTURO)
- [ ] AppsFlyer
- [ ] VTEX
- [ ] WhatsApp
- [ ] KOBE
- [ ] Cupom real
- [ ] Link externo
- [ ] Integrações
- [ ] Envio de notificações
- [ ] Validação real de indicações

### VALIDAÇÕES

#### Testar Logo
- [ ] Logo aparece 2x maior no mobile
- [ ] Logo aparece maior no desktop
- [ ] Proporção mantida
- [ ] Centralizado verticalmente
- [ ] Header continua fixo
- [ ] Espaçamentos corretos

#### Testar Timeline
- [ ] Timeline aparece em cada indicação
- [ ] Ícones corretos por etapa
- [ ] Datas formatadas corretamente
- [ ] Progresso visual claro

#### Testar Cupom
- [ ] Box de cupom aparece quando benefício disponível
- [ ] Código parcialmente oculto
- [ ] Botão "Revelar benefício" desabilitado
- [ ] Texto "Em breve" aparece

#### Testar Perfil
- [ ] "Aceite campanha: Sim" aparece
- [ ] "Data cadastro" aparece
- [ ] "Status participação: Ativo" aparece

#### Testar Notificações (Model)
- [ ] Model pode criar notificação
- [ ] Model pode buscar por usuário
- [ ] Model pode marcar como lida
- [ ] Model pode contar não lidas

### INSTRUÇÕES DE DEPLOY

1. **Executar migration**: `database/migration_etapa6.sql`
2. **Testar ambiente local**:
   - Verificar logo tamanho mobile
   - Verificar logo tamanho desktop
   - Acessar /minhas-indicacoes
   - Verificar timeline nas indicações
   - Acessar /meus-premios
   - Verificar box de cupom
   - Acessar /perfil
   - Verificar informações de campanha
3. **Deploy para Hostinger**
4. **Testar em produção**

### ARQUIVOS ALTERADOS/NOVOS

#### Novos Arquivos
- `database/migration_etapa6.sql`
- `models/Validacao.php`
- `models/Notificacao.php`
- `CHECKLIST_ETAPA6.md`

#### Arquivos Alterados
- `config/bootstrap.php`
- `models/Indicacao.php`
- `controllers/IndicacoesController.php`
- `views/indicacoes/index.php`
- `views/premios/index.php`
- `views/perfil/index.php`
- `assets/css/style.css`

### NOTAS

- Logo aumentada 2x no mobile (36px → 52px)
- Logo aumentada no desktop (mantido 72px)
- Timeline preparada para validação futura
- Cupom parcialmente oculto preparado para revelação futura
- Central de notificações preparada sem envio
- Ainda NÃO implementado: AppsFlyer, VTEX, WhatsApp, KOBE
- Ainda NÃO implementado: cupom real, link externo, integrações

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
- [x] Logo aumentada

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
- [ ] Envio de notificações

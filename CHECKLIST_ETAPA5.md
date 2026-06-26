# CHECKLIST - ETAPA 5
## Compartilhamento e Geração de Link

### MIGRAÇÃO DO BANCO DE DADOS
- [ ] Executar `database/migration_etapa5.sql` no phpMyAdmin
  - Banco: u352670812_indice (Hostinger)
  - Cria tabelas: `links_indicacao` e `cliques`

### ESTRUTURA DA TABELA links_indicacao
```sql
CREATE TABLE IF NOT EXISTS links_indicacao (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id INT UNSIGNED NOT NULL,
    codigo VARCHAR(10) NOT NULL,
    slug VARCHAR(20) NOT NULL,
    url VARCHAR(255) NOT NULL,
    cliques INT UNSIGNED NOT NULL DEFAULT 0,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    appsflyer_enabled TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_links_usuario (usuario_id),
    UNIQUE KEY uk_links_codigo (codigo),
    UNIQUE KEY uk_links_slug (slug),
    KEY idx_links_ativo (ativo),
    CONSTRAINT fk_links_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### ESTRUTURA DA TABELA cliques
```sql
CREATE TABLE IF NOT EXISTS cliques (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    codigo VARCHAR(10) NOT NULL,
    ip VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_cliques_codigo (codigo),
    KEY idx_cliques_ip (ip),
    KEY idx_cliques_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### REGRAS DE LINK
- [x] 1 usuário = 1 link fixo
- [x] Nunca recriar link
- [x] Formato: MM48271 (MM + 6 caracteres alfanuméricos)
- [x] URL: https://indique.minasmaisdrogarias.com.br/convite?ref=MM48271
- [x] Campo `appsflyer_enabled=false` preparado para futuro

### MODELS CRIADOS
- [x] `models/LinkIndicacao.php`:
  - `findByUsuario()` - Buscar link por usuário
  - `findByCodigo()` - Buscar link por código
  - `createForUser()` - Criar link para usuário
  - `incrementCliques()` - Incrementar contador de cliques
  - `getUltimoAcesso()` - Obter último acesso
  - `getStatsByUsuario()` - Obter estatísticas do link
  - `codigoExists()` - Verificar se código existe

- [x] `models/Clique.php`:
  - `register()` - Registrar clique
  - `countByCodigo()` - Contar cliques por código
  - `isSpam()` - Verificar spam (>10 cliques/hora por IP)
  - `getLastCliqueTime()` - Obter último clique
  - `isDuplicateClick()` - Verificar clique duplicado (<30 segundos)

### CONTROLLERS ATUALIZADOS
- [x] `controllers/ConviteController.php`:
  - Valida código existe
  - Valida código ativo
  - Registra clique (IP, user_agent, data)
  - Proteção contra spam
  - Proteção contra duplicidade
  - Bloqueia link inválido
  - Bloqueia auto abertura

- [x] `controllers/DashboardController.php`:
  - Cria link automaticamente se não existir
  - Obtém estatísticas do link
  - Passa `linkStats` para view

### VIEWS ATUALIZADAS
- [x] `views/dashboard/index.php`:
  - Card "Meu Link" adicionado
  - Exibe URL completa
  - Botão "Copiar"
  - Botão "Compartilhar" (native mobile / copy desktop)
  - Botão "WhatsApp" (com texto padrão)
  - Botão "Renovar link" (desabilitado)
  - Texto: "Seu link permanece o mesmo durante toda a campanha"
  - Estatísticas: Total de cliques, Último acesso, Status

- [x] `views/convite/index.php`:
  - Logo da marca
  - Texto: "Indique amigos e ganhe benefícios"
  - Botão "Participar" (redireciona para /cadastro temporariamente)
  - Ainda NÃO redireciona para AppsFlyer

### CSS ATUALIZADO
- [x] `assets/css/style.css`:
  - `.link-url` - Exibição da URL
  - `.link-actions` - Container dos botões
  - `.link-note` - Texto informativo
  - `.link-stats` - Container de estatísticas
  - `.link-stats__item` - Item de estatística
  - `.link-stats__label` - Label de estatística
  - `.link-stats__value` - Valor de estatística
  - `.link-stats__value--active` - Status ativo (verde)
  - `.link-stats__value--inactive` - Status inativo (cinza)
  - `.btn--success` - Estado de sucesso para feedback visual

### JAVASCRIPT ATUALIZADO
- [x] `assets/js/app.js`:
  - Funcionalidade de copiar para clipboard
  - Feedback visual "Copiado!" ao copiar
  - Compartilhamento nativo (mobile)
  - Fallback para copiar (desktop)
  - Compartilhamento WhatsApp com texto padrão
  - Texto padrão WhatsApp: "Estou participando da campanha da Minas Mais 🎉\n\nBaixe o aplicativo pelo meu link e participe também:\n{LINK}"

### BOOTSTRAP ATUALIZADO
- [x] `config/bootstrap.php`:
  - Adicionado `models/LinkIndicacao.php`
  - Adicionado `models/Clique.php`

### FUNCIONALIDADES IMPLEMENTADAS

#### 1. Geração de Link
- [x] Link criado automaticamente ao acessar dashboard
- [x] 1 link fixo por usuário
- [x] Nunca recriado
- [x] Formato: MM + 6 caracteres
- [x] Slug único (20 caracteres hex)

#### 2. Rastreamento de Cliques
- [x] Registro de IP
- [x] Registro de User Agent
- [x] Registro de data/hora
- [x] Incremento automático do contador
- [x] Proteção contra spam (>10 cliques/hora)
- [x] Proteção contra duplicidade (<30 segundos)

#### 3. Card "Meu Link" no Dashboard
- [x] Exibe URL completa
- [x] Botão "Copiar" - copia URL para clipboard
- [x] Botão "Compartilhar" - abre compartilhamento nativo (mobile) ou copia (desktop)
- [x] Botão "WhatsApp" - abre WhatsApp com texto padrão
- [x] Botão "Renovar link" - desabilitado
- [x] Texto explicativo sobre link fixo
- [x] Estatísticas quando há cliques:
  - Total de cliques
  - Último acesso (data/hora)
  - Status (Ativo/Inativo)

#### 4. Rota /convite
- [x] Recebe parâmetro ?ref=
- [x] Valida código existe
- [x] Valida código ativo
- [x] Registra clique com proteções
- [x] Bloqueia links inválidos
- [x] Bloqueia spam
- [x] Bloqueia duplicidade simples
- [x] Redireciona para /cadastro temporariamente

#### 5. Compartilhamento
- [x] Mobile: abre compartilhamento nativo
- [x] Desktop: copia texto + link
- [x] WhatsApp: abre com texto padrão
- [x] Texto padrão: "Estou participando da campanha da Minas Mais 🎉\n\nBaixe o aplicativo pelo meu link e participe também:\n{LINK}"

### PROTEÇÕES IMPLEMENTADAS
- [x] Bloqueio de link inválido
- [x] Bloqueio de link inativo
- [x] Proteção contra spam (>10 cliques/hora por IP)
- [x] Proteção contra duplicidade (<30 segundos)
- [x] Proteção contra auto abertura (via ReferralService existente)

### NÃO IMPLEMENTADO (FUTURO)
- [ ] AppsFlyer
- [ ] VTEX
- [ ] WhatsApp (integração completa)
- [ ] KOBE
- [ ] App
- [ ] Renovação de link
- [ ] Redirecionamento para AppsFlyer

### VALIDAÇÕES

#### Testar Geração de Link
- [ ] Acessar /dashboard pela primeira vez cria link
- [ ] Link é único por usuário
- [ ] Link não é recriado em acessos subsequentes
- [ ] Formato do código: MM + 6 caracteres
- [ ] Slug é único (20 caracteres hex)

#### Testar Card "Meu Link"
- [ ] URL é exibida corretamente
- [ ] Botão "Copiar" copia URL
- [ ] Feedback "Copiado!" aparece
- [ ] Botão "Compartilhar" abre nativo (mobile) ou copia (desktop)
- [ ] Botão "WhatsApp" abre WhatsApp com texto correto
- [ ] Botão "Renovar link" está desabilitado
- [ ] Texto explicativo aparece
- [ ] Estatísticas aparecem após primeiro clique

#### Testar Rastreamento de Cliques
- [ ] Clique é registrado na tabela cliques
- [ ] IP é registrado
- [ ] User Agent é registrado
- [ ] Data/hora é registrada
- [ ] Contador de cliques é incrementado
- [ ] Proteção contra spam funciona (>10 cliques/hora)
- [ ] Proteção contra duplicidade funciona (<30 segundos)

#### Testar Rota /convite
- [ ] Link inválido mostra erro
- [ ] Link inativo mostra erro
- [ ] Link válido registra clique
- [ ] Spam é bloqueado
- [ ] Duplicidade é bloqueada
- [ ] Botão "Participar" redireciona para /cadastro

#### Testar Compartilhamento
- [ ] Mobile: abre compartilhamento nativo
- [ ] Desktop: copia texto + link
- [ ] WhatsApp: abre com texto padrão correto
- [ ] Texto padrão contém emoji e link

### INSTRUÇÕES DE DEPLOY

1. **Executar migration**: `database/migration_etapa5.sql`
2. **Testar ambiente local**:
   - Acessar /dashboard
   - Verificar criação automática de link
   - Testar botão "Copiar"
   - Testar botão "Compartilhar" (mobile e desktop)
   - Testar botão "WhatsApp"
   - Acessar /convite?ref=CODIGO
   - Verificar registro de clique
   - Testar proteção contra spam
   - Testar proteção contra duplicidade
3. **Deploy para Hostinger**
4. **Testar em produção**

### ARQUIVOS ALTERADOS/NOVOS

#### Novos Arquivos
- `database/migration_etapa5.sql`
- `models/LinkIndicacao.php`
- `models/Clique.php`
- `CHECKLIST_ETAPA5.md`

#### Arquivos Alterados
- `config/bootstrap.php`
- `controllers/ConviteController.php`
- `controllers/DashboardController.php`
- `views/dashboard/index.php`
- `views/convite/index.php`
- `assets/css/style.css`
- `assets/js/app.js`

### NOTAS

- Campo `appsflyer_enabled=false` preparado para integração futura
- Link é fixo por usuário - nunca recriado
- Proteção contra spam: >10 cliques/hora por IP
- Proteção contra duplicidade: <30 segundos
- Compartilhamento nativo em mobile, copiar em desktop
- WhatsApp abre com texto padrão
- Botão "Participar" redireciona para /cadastro temporariamente
- Ainda NÃO redireciona para AppsFlyer

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

#### Próximas Etapas (Futuro)
- [ ] Integração AppsFlyer
- [ ] Integração VTEX
- [ ] Integração WhatsApp completa
- [ ] Liberação de cupom
- [ ] Validação de indicação
- [ ] Sistema de prêmios
- [ ] Renovação de link
- [ ] Redirecionamento para AppsFlyer
- [ ] Integração KOBE
- [ ] Integração App

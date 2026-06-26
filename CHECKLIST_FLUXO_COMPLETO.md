# CHECKLIST - Fluxo Completo da Indicação

### OBJETIVO
Finalizar o fluxo completo da indicação, sem integrar ainda com AppsFlyer, VTEX ou WhatsApp.

### FLUXO IMPLEMENTADO

#### 1. Usuário A gera seu link de indicação
- [x] Link gerado automaticamente no cadastro
- [x] Código único por usuário
- [x] Link acessível via dashboard

#### 2. Usuário B acessa /convite?ref=CODIGO
- [x] Valida se o código existe
- [x] Registra clique na tabela de eventos (LINK_CLICADO)
- [x] Salva referência na sessão (referral_code, referral_user_id)
- [x] Cria registro em indicados com status LINK_ACESSADO
- [x] Impede autoindicação (mesmo CPF)

#### 3. Usuário B clica em "Quero participar"
- [x] Atualiza status para CADASTRO_INICIADO
- [x] Registra evento CADASTRO_INICIADO
- [x] Redireciona para cadastro

#### 4. Usuário B conclui o cadastro
- [x] Valida CPF, telefone, email
- [x] Impede duplicidade (CPF, telefone, email)
- [x] Impede autoindicação
- [x] Cria usuário na tabela usuarios
- [x] Atualiza indicado com status AGUARDANDO_VALIDACAO
- [x] Registra evento CADASTRO
- [x] Registra evento INDICADO_CADASTRADO
- [x] Auto-login do novo usuário

#### 5. Transições de Status
- [x] LINK_ACESSADO → CADASTRO_INICIADO
- [x] CADASTRO_INICIADO → AGUARDANDO_VALIDACAO
- [x] AGUARDANDO_VALIDACAO → VALIDADO (futuro)
- [x] AGUARDANDO_VALIDACAO → INVALIDADO (futuro)

#### 6. Dashboard do Indicador
- [x] Stats calculados em tempo real da tabela indicados
- [x] Total de indicações
- [x] Pendentes (AGUARDANDO_VALIDACAO)
- [x] Validadas (VALIDADO)
- [x] Cupons liberados (VALIDADO)
- [x] Timeline de cada indicado

#### 7. Minhas Indicações
- [x] Nome mascarado (ex.: João S.)
- [x] Data do cadastro
- [x] Status atual
- [x] Barra de progresso (timeline)
- [x] Suporte a indicados e indicacoes (backward compatibility)

#### 8. Regras de Segurança
- [x] Impedir autoindicação (mesmo CPF)
- [x] Impedir mesmo telefone
- [x] Impedir mesmo email
- [x] Impedir duplicidade de indicação
- [x] Rate limit no cadastro (3 tentativas por hora)

#### 9. Eventos Registrados
- [x] LINK_CLICADO - Quando usuário acessa /convite
- [x] CONVITE_ABERTO - Quando convite é aberto
- [x] CADASTRO_INICIADO - Quando clica em participar
- [x] CADASTRO - Quando usuário é criado
- [x] INDICADO_CADASTRADO - Quando indicado se cadastra
- [x] LINK_COMPARTILHADO - Quando indicador compartilha
- [x] LOGIN - Quando usuário faz login
- [x] PERFIL_EDITADO - Quando perfil é editado
- [x] SENHA_ALTERADA - Quando senha é alterada

### ARQUIVOS ALTERADOS

#### Controllers
- `controllers/IndicadosController.php`
  - Adicionado session reference handling (linhas 43-44)
  - Adicionado evento LINK_CLICADO (linha 63)
  - Adicionado evento CADASTRO_INICIADO (linha 95)
  - Alterado status para AGUARDANDO_VALIDACAO (linhas 233, 246)
  - Criado usuário na tabela usuarios (linhas 251-262)
  - Auto-login do novo usuário (linhas 282-286)
  - Adicionado evento CADASTRO (linhas 276-280)

- `controllers/DashboardController.php`
  - Calcula stats reais da tabela indicados (linhas 36-62)
  - Mantém backward compatibility com indicacoes (linhas 64-72)

#### Models
- `models/Indicado.php`
  - Adicionado método maskName() para mascarar nomes (linhas 188-204)

#### Views
- `views/indicacoes/index.php`
  - Atualizado para usar dados de indicados com nomes mascarados (linhas 55-116)
  - Suporte a indicados e indicacoes (backward compatibility)

### MIGRAÇÕES
- Nenhuma migration necessária (tabela indicados já existe da ETAPA 8)

### VALIDAÇÕES

#### Testar Fluxo Completo
- [ ] Usuário A gera link
- [ ] Usuário B acessa /convite?ref=CODIGO
- [ ] Verificar registro LINK_ACESSADO
- [ ] Verificar evento LINK_CLICADO
- [ ] Usuário B clica em "Quero participar"
- [ ] Verificar status CADASTRO_INICIADO
- [ ] Verificar evento CADASTRO_INICIADO
- [ ] Usuário B preenche cadastro
- [ ] Verificar criação de usuário
- [ ] Verificar status AGUARDANDO_VALIDACAO
- [ ] Verificar eventos CADASTRO e INDICADO_CADASTRADO
- [ ] Verificar auto-login

#### Testar Dashboard
- [ ] Verificar stats em tempo real
- [ ] Verificar timeline de indicados
- [ ] Verificar nomes mascarados

#### Testar Minhas Indicações
- [ ] Verificar nomes mascarados
- [ ] Verificar status
- [ ] Verificar timeline/progresso

#### Testar Regras de Segurança
- [ ] Tentar autoindicação (deve falhar)
- [ ] Tentar duplicidade de CPF (deve falhar)
- [ ] Tentar duplicidade de telefone (deve falhar)
- [ ] Tentar duplicidade de email (deve falhar)
- [ ] Testar rate limit

### NÃO IMPLEMENTADO (FUTURO)
- [x] AppsFlyer
- [x] VTEX
- [x] WhatsApp
- [x] Geração real de cupom
- [x] Push notification
- [x] Validação de indicação (manual)
- [x] Liberação de cupom real

### NOTAS

- Fluxo completo implementado sem integrações externas
- Sistema preparado para validação manual futura
- Eventos registrados para rastreamento completo
- Dashboard com stats em tempo real
- Nomes mascarados para privacidade
- Backward compatibility mantida com tabela indicacoes
- Auto-login após cadastro do indicado
- Sessão persiste referência durante navegação

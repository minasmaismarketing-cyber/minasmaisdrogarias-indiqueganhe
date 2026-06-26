# CHECKLIST - Etapa 12 — Painel Administrativo de Campanhas

### OBJETIVO
Permitir que a equipe da Minas Mais gerencie todas as campanhas de Indique e Ganhe sem alterar código.

### MIGRAÇÃO

#### Tabela campanhas
- [x] Criar migration migration_etapa12.sql
- [x] Campos: id, nome, slug, descricao, status, desconto, tipo_desconto, valor_minimo_compra, limite_indicacoes_usuario, inicio, fim, banner, cor_primaria, cor_secundaria, texto_botao, texto_landing, created_at, updated_at
- [x] Status: ATIVA, INATIVA, AGENDADA, FINALIZADA
- [x] Tipo desconto: PERCENTUAL, VALOR_FIXO
- [x] Índices: slug (único), status, periodo (inicio, fim)

### MODELO

#### Campanha Model
- [x] create() - Criar campanha com todos os campos
- [x] findAll() - Listar todas as campanhas
- [x] findActive() - Buscar campanha ativa (status ATIVA e dentro do período)
- [x] findById() - Buscar por ID
- [x] findBySlug() - Buscar por slug
- [x] update() - Atualizar campos
- [x] activate() - Ativar campanha (desativa todas as outras)
- [x] deactivate() - Desativar campanha
- [x] delete() - Excluir campanha
- [x] duplicate() - Duplicar campanha
- [x] getStats() - Estatísticas de campanhas
- [x] generateSlug() - Gerar slug a partir do nome
- [x] statusLabel() - Label legível do status
- [x] tipoDescontoLabel() - Label legível do tipo de desconto

### CONTROLLER

#### CampanhasController
- [x] index() - Listagem de campanhas
- [x] create() - Formulário de criação (GET/POST)
- [x] edit() - Formulário de edição (GET/POST)
- [x] activate() - Ativar campanha
- [x] deactivate() - Desativar campanha
- [x] duplicate() - Duplicar campanha
- [x] delete() - Excluir campanha
- [x] requireAdmin() - Middleware de admin

#### AdminController
- [x] index() - Dashboard com indicadores de campanha
- [x] Campanha ativa
- [x] Usuários participantes
- [x] Total de indicações
- [x] Indicações validadas
- [x] Cupons liberados
- [x] Taxa de conversão

### ROTAS

#### Admin Campanhas
- [x] GET /admin/campanhas - Listagem
- [x] GET/POST /admin/campanhas/criar - Criar
- [x] GET/POST /admin/campanhas/editar/{id} - Editar
- [x] POST /admin/campanhas/ativar/{id} - Ativar
- [x] POST /admin/campanhas/desativar/{id} - Desativar
- [x] POST /admin/campanhas/duplicar/{id} - Duplicar
- [x] POST /admin/campanhas/excluir/{id} - Excluir

### VIEWS

#### admin/campanhas.php
- [x] Listagem de campanhas
- [x] Nome, período, desconto
- [x] Status badge
- [x] Botões: Editar, Duplicar, Ativar/Desativar, Excluir

#### admin/campanha-form.php
- [x] Nome da campanha
- [x] Descrição
- [x] Texto da Landing Page
- [x] Texto do Botão
- [x] Desconto (valor)
- [x] Tipo de desconto (select)
- [x] Valor mínimo de compra
- [x] Limite de indicações por usuário
- [x] Data início
- [x] Data fim
- [x] Cor primária (color picker)
- [x] Cor secundária (color picker)
- [x] URL do banner
- [x] Status (select)

#### indicados/convite.php
- [x] Carregar dados da campanha ativa
- [x] Usar texto_landing como título
- [x] Usar descricao como descrição
- [x] Usar texto_botao no botão
- [x] Aplicar cor_primaria e cor_secundaria via CSS
- [x] Exibir banner se disponível
- [x] Fallback para valores padrão se não houver campanha

### CSS

#### style.css
- [x] .invite-banner - Container do banner
- [x] .invite-banner__image - Imagem do banner responsiva

### REGRAS

#### Uma campanha ativa
- [x] Ao ativar uma campanha, todas as outras ficam INATIVAS automaticamente
- [x] Implementado em Campanha::activate()

#### Validações
- [x] Nome obrigatório
- [x] Data início obrigatória
- [x] Data fim obrigatória
- [x] Data fim > Data início
- [x] Desconto >= 0
- [x] Valor mínimo >= 0
- [x] Limite >= 0
- [x] Tipo de desconto válido
- [x] Status válido

### INTEGRAÇÃO

#### Landing Page /convite
- [x] Carregar campanha ativa automaticamente
- [x] Aplicar título, descrição, botão da campanha
- [x] Aplicar cores da campanha
- [x] Exibir banner da campanha
- [x] Sem valores fixos no código

### ARQUIVOS ALTERADOS

#### Database
- `database/migration_etapa12.sql` - Migration da tabela campanhas

#### Models
- `models/Campanha.php` - Model completo de campanhas

#### Controllers
- `controllers/CampanhasController.php` - CRUD de campanhas
- `controllers/AdminController.php` - Dashboard com indicadores
- `controllers/IndicadosController.php` - Load campanha ativa no convite

#### Config
- `config/routes.php` - Rotas de campanhas

#### Views
- `views/admin/campanhas.php` - Listagem
- `views/admin/campanha-form.php` - Formulário
- `views/indicados/convite.php` - Landing page dinâmica

#### CSS
- `assets/css/style.css` - Banner CSS

### VALIDAÇÕES

#### Testar Migration
- [ ] Executar migration_etapa12.sql no phpMyAdmin
- [ ] Verificar tabela campanhas criada
- [ ] Verificar índices criados

#### Testar CRUD de Campanhas
- [ ] Criar nova campanha
- [ ] Editar campanha existente
- [ ] Duplicar campanha
- [ ] Ativar campanha (verificar outras ficam inativas)
- [ ] Desativar campanha
- [ ] Excluir campanha

#### Testar Landing Page
- [ ] Acessar /convite?ref=CODIGO sem campanha ativa (usar defaults)
- [ ] Criar campanha ativa
- [ ] Acessar /convite?ref=CODIGO com campanha ativa
- [ ] Verificar título dinâmico
- [ ] Verificar descrição dinâmica
- [ ] Verificar texto do botão dinâmico
- [ ] Verificar cores aplicadas
- [ ] Verificar banner exibido

#### Testar Dashboard Admin
- [ ] Verificar campanha ativa exibida
- [ ] Verificar usuários participantes
- [ ] Verificar total de indicações
- [ ] Verificar indicações validadas
- [ ] Verificar cupons liberados
- [ ] Verificar taxa de conversão

### NÃO IMPLEMENTADO (FUTURO)
- [x] AppsFlyer
- [x] VTEX
- [x] WhatsApp
- [x] Disparo de cupons
- [x] Push Notification

### MANUTIDO
- [x] Layout Mobile First
- [x] Identidade Minas Mais
- [x] Paleta: Vermelho, Cinza, Branco, Preto

### INSTRUÇÕES DE DEPLOY

1. Executar migration_etapa12.sql no phpMyAdmin
2. Deploy para Hostinger
3. Testar CRUD de campanhas
4. Criar primeira campanha ativa
5. Testar landing page /convite com campanha ativa
6. Verificar dashboard admin com indicadores

### NOTAS

- Sistema completo de gerenciamento de campanhas
- Landing page totalmente dinâmica
- Uma campanha ativa por vez
- Cores e textos customizáveis
- Banner opcional
- Desconto configurável (percentual ou valor fixo)
- Limite de indicações por usuário
- Período definido por datas
- Dashboard admin com indicadores em tempo real

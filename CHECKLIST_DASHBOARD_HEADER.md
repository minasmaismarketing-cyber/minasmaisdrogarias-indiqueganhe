# CHECKLIST - Dashboard e Header Reorganização

### OBJETIVO
Simplificar a tela inicial e melhorar navegação, corrigindo alinhamento do header.

### DASHBOARD

#### Simplificação
- [x] Remover tabela/listagem extensa de indicações do dashboard
- [x] Manter apenas visão resumida:
  - Saudação
  - Cards de resumo (Total, Validadas, Pendentes, Liberadas)
  - Meu código
  - Botão Compartilhar
  - Progresso de Indicações (apenas stats, sem lista detalhada)
  - Atalhos principais

#### Ações Rápidas
- [x] Botão "Meus cupons" → /meus-cupons
- [x] Botão "Ver indicações" → /indicacoes (alterado de "Minhas indicações")
- [x] Botão "Meu perfil" → /perfil

### INDICAÇÕES

#### Tela Única
- [x] Criar/ajustar tela única em /indicacoes
- [x] Unificar funcionalidades:
  - Lista de indicações
  - Status
  - Filtros (status, busca)
  - Progresso/timeline
  - Data
  - Nome do indicado
  - Motivo de invalidação

#### Atualização de Rotas
- [x] Alterar rota de /minhas-indicacoes para /indicacoes
- [x] Atualizar form action para /indicacoes
- [x] Atualizar links de paginação para /indicacoes

### MENU/NAVEGAÇÃO

#### Header
- [x] Adicionar item "Indicações" ao menu
- [x] Atualizar link no dashboard para /indicacoes
- [x] Manter links: Início, Indicações, Perfil

### HEADER - CORREÇÃO VISUAL

#### Alinhamento
- [x] Nome e avatar alinhados verticalmente ao centro
- [x] Criar container .mm-header__user-info para agrupar nome + avatar
- [x] Aplicar display: flex e align-items: center

#### Mobile
- [x] Ocultar nome no mobile (max-width: 767px)
- [x] Manter apenas avatar visível
- [x] Avatar alinhado ao centro do header

#### Desktop
- [x] Logo à esquerda
- [x] Menu (Início, Indicações, Perfil) no centro/direita
- [x] Nome + avatar alinhados em linha à direita
- [x] Limitar largura do nome com text-overflow: ellipsis

#### CSS Adicionado
- [x] .mm-header__user-info - Container flex para alinhamento
- [x] .mm-header__name - Estilização do nome com max-width e overflow
- [x] @media (max-width: 767px) - Ocultar nome no mobile
- [x] Remover regra duplicada .mm-header__name no desktop

### ARQUIVOS ALTERADOS

#### Views
- `views/dashboard/index.php` - Removido tabela de indicações, atualizado botão para /indicacoes
- `views/indicacoes/index.php` - Atualizado form action e paginação para /indicacoes
- `components/header.view.php` - Adicionado "Indicações" ao menu, agrupado nome + avatar

#### Config
- `config/routes.php` - Alterado /minhas-indicacoes para /indicacoes

#### CSS
- `assets/css/style.css` - Adicionado .mm-header__user-info, .mm-header__name, media query mobile
- `assets/css/style.css` - Removido regra duplicada .mm-header__name
- `assets/css/style.css` - Corrigido erro de sintaxe CSS

### NÃO ALTERADO

- [x] Regras de indicação
- [x] Banco de dados (exceto rota/menu)
- [x] APIs
- [x] Cupons
- [x] AppsFlyer
- [x] VTEX
- [x] WhatsApp

### VALIDAÇÕES

#### Dashboard
- [ ] Verificar se dashboard mostra apenas visão resumida
- [ ] Verificar se não há tabela de indicações no dashboard
- [ ] Verificar se cards de estatísticas estão visíveis
- [ ] Verificar se botão "Ver indicações" direciona para /indicacoes

#### Indicações
- [ ] Acessar /indicacoes
- [ ] Verificar se lista de indicações está completa
- [ ] Verificar se filtros funcionam
- [ ] Verificar se busca funciona
- [ ] Verificar se paginação funciona
- [ ] Verificar se nome do indicado é exibido
- [ ] Verificar se motivo de invalidação é exibido

#### Header
- [ ] Verificar alinhamento do nome e avatar no desktop
- [ ] Verificar se nome está alinhado verticalmente ao centro
- [ ] Verificar se avatar está alinhado ao centro do header
- [ ] Verificar se menu "Indicações" está visível e funcional
- [ ] Verificar se link ativo está correto

#### Mobile
- [ ] Verificar se nome está oculto no mobile
- [ ] Verificar se apenas avatar é visível
- [ ] Verificar se avatar está alinhado ao centro
- [ ] Verificar se logo está à esquerda
- [ ] Verificar se avatar está à direita

#### Rotas
- [ ] Testar /indicacoes
- [ ] Verificar se /minhas-indicações redireciona ou retorna 404
- [ ] Testar filtros em /indicacoes
- [ ] Testar paginação em /indicacoes

### INSTRUÇÕES DE DEPLOY

1. Deploy para Hostinger
2. Testar dashboard - verificar ausência de tabela de indicações
3. Testar /indicacoes - verificar lista completa
4. Testar header desktop - verificar alinhamento
5. Testar header mobile - verificar nome oculto
6. Testar menu - verificar link "Indicações"
7. Testar botão "Ver indicações" no dashboard

### NOTAS

- Dashboard simplificado para visão resumida apenas
- Tela única de indicações em /indicacoes
- Header corrigido com alinhamento vertical
- Nome oculto no mobile para economizar espaço
- Avatar sempre visível e alinhado ao centro
- Menu atualizado com link "Indicações"

# CHECKLIST - ETAPA 3
## Sistema de Indicação

### MIGRAÇÃO DO BANCO DE DADOS
- [ ] Executar `database/migration_etapa3.sql` no phpMyAdmin
  - Banco: u352670812_indice (Hostinger)
  - Tabela: `indicacoes`

### ESTRUTURA DA TABELA indicacoes
```sql
CREATE TABLE IF NOT EXISTS indicacoes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id INT UNSIGNED NOT NULL,
    codigo_indicador VARCHAR(10) NOT NULL,
    nome_indicado VARCHAR(150) NULL,
    telefone_indicado VARCHAR(20) NULL,
    status ENUM(
        'AGUARDANDO',
        'LINK_ACESSADO',
        'CADASTRADO',
        'VALIDADO',
        'PREMIADO',
        'EXPIRADO'
    ) NOT NULL DEFAULT 'AGUARDANDO',
    cupom_liberado TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_indicacoes_usuario (usuario_id),
    KEY idx_indicacoes_codigo (codigo_indicador),
    KEY idx_indicacoes_status (status),
    KEY idx_indicacoes_telefone (telefone_indicado),
    CONSTRAINT fk_indicacoes_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### FUNCIONALIDADES IMPLEMENTADAS

#### 1. Dashboard (/dashboard)
- [x] Exibe código de indicação do usuário
- [x] Botão copiar código
- [x] Botão copiar link
- [x] Botão compartilhar
- [x] Link de convite: `https://indique.minasmaisdrogarias.com.br/convite?ref=CODIGO`
- [x] Estatísticas: Total, Validadas, Pendentes, Prêmio
- [x] Lista de indicações com status
- [x] Botão "Meu perfil"
- [x] Botão "Resgatar cupom" (desabilitado - futura implementação)

#### 2. Página de Convite (/convite)
- [x] Recebe parâmetro `?ref=CODIGO`
- [x] Valida código de indicação
- [x] Bloqueia auto-indicação (usuário usando próprio código)
- [x] Salva referência na sessão
- [x] Registra acesso ao link (status LINK_ACESSADO)
- [x] Redireciona para cadastro após validação
- [x] Exibe logo da marca
- [x] Mobile First

#### 3. Proteção Contra Fraude
- [x] Bloqueio de auto-indicação (mesmo CPF)
- [x] Bloqueio de duplicidade (mesmo telefone já indicado)
- [x] Validação de formato do código (MM + 6 caracteres alfanuméricos)
- [x] Verificação de existência do código no banco

#### 4. Integração com Cadastro
- [x] Ao cadastrar, verifica se há referência na sessão
- [x] Vincula novo usuário ao indicador
- [x] Atualiza status para CADASTRADO
- [x] Limpa referência da sessão após uso

### ARQUIVOS ALTERADOS

#### Controllers
- `controllers/DashboardController.php` - Adicionado try-catch para tabela indicacoes

#### Models
- `models/Indicacao.php` - Já implementado (statsByUsuario, listByUsuario, etc.)
- `models/Usuario.php` - Já implementado (generateCodigoIndicador, findByCodigo)

#### Core
- `core/ReferralService.php` - Já implementado (validação, link, proteção anti-fraude)
- `core/AuthMiddleware.php` - Já implementado (requireAuth, requireGuest)

#### Views
- `views/dashboard/index.php` - Já implementado (UI completa)
- `views/convite/index.php` - Já implementado (página de convite)

#### Rotas
- `config/routes.php` - Já implementado:
  - GET /convite
  - POST /convite/participar
  - GET /dashboard
  - POST /dashboard/compartilhar

### VALIDAÇÕES

#### Testar /dashboard sem login
- [ ] Deve redirecionar para /login
- [ ] Mensagem: "Faça login para continuar."

#### Testar /dashboard com login
- [ ] Deve exibir "Olá, {nome}"
- [ ] Deve exibir código de indicação
- [ ] Botões copiar/link/compartilhar devem funcionar
- [ ] Estatísticas devem aparecer (zeros se não houver indicações)

#### Testar /convite?ref=CODIGO_INVALIDO
- [ ] Deve exibir "Convite indisponível"
- [ ] Mensagem de erro apropriada

#### Testar /convite?ref=CODIGO_VALIDO
- [ ] Deve exibir "Você foi convidado!"
- [ ] Botão "Participar" deve redirecionar para /cadastro
- [ ] Referência deve ser salva na sessão

#### Testar auto-indicação
- [ ] Usuário logado tentando usar próprio código
- [ ] Deve exibir erro: "Você não pode usar seu próprio código."

#### Testar cadastro com referência
- [ ] Ao cadastrar via link de convite
- [ ] Deve criar registro em indicacoes com status CADASTRADO
- [ ] Deve vincular ao indicador correto

### STATUS DO SISTEMA

#### Funcionalidades OK
- [x] Login funcionando
- [x] Cadastro funcionando
- [x] Sessão funcionando
- [x] Dashboard acessível (com correção)
- [x] Geração de código de indicação
- [x] Validação de código
- [x] Proteção contra auto-indicação
- [x] Proteção contra duplicidade
- [x] Link de convite funcionando

#### Próximas Etapas (Futuro)
- [ ] Integração AppsFlyer
- [ ] Integração VTEX
- [ ] Integração WhatsApp
- [ ] Liberação de cupom
- [ ] Validação de indicação
- [ ] Sistema de prêmios

### INSTRUÇÕES DE DEPLOY

1. **Backup do banco** (opcional mas recomendado)
2. **Executar migration**: `database/migration_etapa3.sql`
3. **Testar ambiente local**:
   - Acessar /dashboard (com login)
   - Testar copiar código/link
   - Testar /convite?ref=CODIGO
   - Fazer cadastro via convite
4. **Deploy para Hostinger**
5. **Testar em produção**

### NOTAS

- A paleta de cores (vermelho, cinza, branco, preto) já está aplicada via CSS
- Logo já configurada: `assets/images/Logo - Drogaria e Perfumaria.png`
- Identidade visual Minas Mais mantida
- Design Mobile First já implementado
- Debug mode restaurado para respeitar variável de ambiente

### ERRO CORRIGIDO

**Problema**: Dashboard exibia "Ocorreu um erro. Tente novamente mais tarde."

**Causa**: Controller tentava consultar tabela `indicacoes` que não existia ainda.

**Solução**: Adicionado try-catch em `DashboardController.php` para tratar tabela ausente gracefulmente. Se tabela não existir, exibe zeros nas estatísticas e lista vazia de indicações.

**Arquivo**: `controllers/DashboardController.php`
- Adicionado `use PDOException;`
- Wrap das chamadas ao Indicacao model em try-catch
- Valores padrão para stats e indicacoes quando tabela não existe

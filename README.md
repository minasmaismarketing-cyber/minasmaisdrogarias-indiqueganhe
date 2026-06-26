# Indique e Ganhe

Sistema web em PHP puro para campanha de indicação.

## Stack

- PHP 8.1+
- MySQL 8+
- HTML, CSS, JavaScript (sem framework)

## Estrutura

```
/
├── config/          # Configurações, bootstrap e rotas
├── controllers/     # Controllers MVC
├── core/            # Classes base (Router, Session, CSRF, etc.)
├── models/          # Models (próximas etapas)
├── views/           # Templates
├── api/             # Endpoints da API
├── admin/           # Painel administrativo
├── uploads/         # Arquivos enviados
├── assets/          # CSS, JS e imagens
├── database/        # Scripts SQL
└── index.php        # Front controller
```

## Requisitos

- PHP 8.1 ou superior (extensões: `pdo`, `pdo_mysql`, `mbstring`, `json`)
- MySQL 8 ou MariaDB 10.4+
- Apache com `mod_rewrite` **ou** PHP built-in server

## Deploy — Hostinger

### Domínio

```
https://indique.minasmaisdrogarias.com.br
```

Configure o subdomínio no painel Hostinger apontando para a pasta do projeto (geralmente `public_html/` na raiz do subdomínio). Com isso, `APP_BASE_PATH` fica vazio e o sistema detecta o caminho via `SCRIPT_NAME`.

### Variáveis de ambiente (produção)

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://indique.minasmaisdrogarias.com.br
APP_BASE_PATH=
SESSION_SECURE=true

DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u146248277_indicacao
DB_USERNAME=u146248277_indiqueganhe
DB_PASSWORD=<senha do painel Hostinger>
```

### Importar banco (phpMyAdmin)

Use `database/schema.hostinger.sql` — **não** cria banco (já existe na Hostinger). Selecione o banco `u146248277_indicacao` e execute as migrations em ordem (`migration_etapa2.sql` em diante).

### Links de convite já existentes

Se houver URLs antigas salvas em `links_indicacao.url`, revise e execute manualmente `database/migration_domain_links.sql`.

### Segurança

- `.env` bloqueado via `.htaccess`
- Pastas `config/`, `core/`, `database/`, `controllers/`, `models/`, `views/` bloqueadas
- `uploads/` sem execução de PHP
- `server.php` apenas para desenvolvimento local

---

## Instalação local

### 1. Clonar / copiar o projeto

Coloque os arquivos no diretório desejado do servidor web.

### 2. Configurar ambiente

Copie o arquivo de exemplo e ajuste as variáveis:

```bash
cp .env.example .env
```

Edite o `.env` com os dados do banco:

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=indique_e_ganhe
DB_USER=root
DB_PASS=sua_senha
APP_URL=http://localhost:8000
```

### 3. Criar o banco de dados

```bash
mysql -u root -p < database/schema.sql
```

Ou execute o conteúdo de `database/schema.sql` no phpMyAdmin / MySQL Workbench.

### 4. Subir o servidor

**Opção A — PHP built-in (desenvolvimento):**

```bash
php -S localhost:8000 server.php
```

Acesse: http://localhost:8000

**Opção B — Apache:**

Configure o `DocumentRoot` para a pasta do projeto. O arquivo `.htaccess` já redireciona todas as rotas para `index.php`.

## Funcionalidades (Etapa 1)

- [x] Arquitetura MVC simples
- [x] Conexão MySQL via PDO
- [x] Arquivo `.env` para configuração
- [x] Roteamento simples
- [x] Sessão PHP
- [x] Proteção CSRF (classe `Csrf` + helper `csrf_field()`)
- [x] Pasta `uploads/`
- [x] Página inicial com status do sistema

## Rotas

| Método | Rota | Descrição        |
|--------|------|------------------|
| GET    | `/`  | Página inicial   |

## Uso de CSRF

Em formulários POST (próximas etapas), inclua o token:

```php
<form method="POST" action="<?= url('/exemplo') ?>">
    <?= csrf_field() ?>
    <!-- campos -->
</form>
```

Validação no controller:

```php
if (!Csrf::validateRequest()) {
    // rejeitar requisição
}
```

## Próximas etapas

1. ~~Estrutura base~~
2. Cadastro e login
3. Dashboard do indicador
4. Sistema de código
5. Compartilhamento
6. Integração AppsFlyer
7. API para KOBE
8. Regras de validação
9. Cupom do indicado
10. Importação cupons VTEX
11. Benefício indicador
12. WhatsApp
13. Resgate
14. Painel Admin
15. Produção

## Licença

Uso interno — Minas Mais.

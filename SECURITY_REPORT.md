# SECURITY_REPORT.md — Indique e Ganhe

**Data:** 26/06/2026  
**Fase:** 0 — Diagnóstico (análise estática, sem pentest)

---

## Resumo executivo

| Área | Status | Nota |
|------|--------|------|
| CSRF | ✅ Implementado | Forms POST protegidos |
| SQL Injection | ✅ Mitigado | PDO prepared statements predominante |
| Password Hash | ✅ Adequado | bcrypt via `password_hash()` |
| Sessão | ✅ Boa config | httponly, samesite, regenerate |
| Cookies Remember-me | ✅ Seguro | Token hashed SHA-256, hash_equals |
| Middleware HTTP | ⚠️ Parcial | Auth manual; API middleware quebrado |
| Upload | ✅ N/A baixo risco | Apenas logs; .htaccess protege |
| .env | ⚠️ Atenção | Protegido por htaccess; presente localmente |
| Admin ACL | ❌ Fraco | Email hardcoded único |
| Rate Limiting | ⚠️ Parcial | Login OK; outros fluxos quebrados |
| Headers segurança | ⚠️ Ausente | Sem CSP, HSTS explícito |
| Integrações API | ❌ Crítico | ApiAuth não funcional |

**Classificação geral:** Segurança **adequada para MVP interno** nas camadas auth/CSRF/SQL, com **lacunas críticas** em autorização admin e API externa.

---

## 1. CSRF (Cross-Site Request Forgery)

### Implementação

**Arquivo:** `core/Csrf.php`

- Token 32 bytes (`random_bytes`) armazenado em sessão
- Campo hidden `_csrf_token` via `csrf_field()`
- Validação com `hash_equals()` (timing-safe)
- Suporte header `X-CSRF-TOKEN`

### Cobertura verificada

| Ação | CSRF |
|------|------|
| Login, cadastro, logout | ✅ |
| Perfil, senha, excluir conta | ✅ |
| Dashboard compartilhar | ✅ |
| Convite participar | ✅ |
| Admin POST actions | ✅ (controllers verificados) |
| API JSON KOBE | N/A (Bearer token) |

### Riscos

- **Baixo:** GET endpoints idempotentes sem side-effects significativos
- Endpoints POST admin dependem de CSRF + email admin

---

## 2. SQL Injection

### Implementação

**Arquivo:** `config/database.php`

```php
PDO::ATTR_EMULATE_PREPARES => false
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
```

### Análise models

- ~99% queries via `$stmt->prepare()` + bind
- Correções recentes em `Usuario::countAll()` e `Indicacao::countAll()` (AUDIT_ERRORS)
- Exceção: `Campanha::activate()` usa `$this->db->exec()` com string de constante interna (risco negligible)

### Riscos

- **Baixo** em código atual
- Filtros dinâmicos em `Validacao::findAll()` constroem SQL com placeholders — OK
- `Indicado::update()` concatena campos whitelisted — OK

---

## 3. Password Hash

### Implementação

- Cadastro/login: `PASSWORD_BCRYPT`
- IndicadosController salvar: `PASSWORD_DEFAULT` (bcrypt)
- Verificação: `password_verify()`
- Reset senha: token 32 bytes, hash SHA-256 armazenado, expira 1h

### Política de senha

- Indicador: mínimo 8 chars, 1 letra, 1 número (`Validator::senha`)
- Indicado (fluxo alternativo): mínimo 6 chars — **inconsistente**

### Riscos

- **Baixo** hash strength
- **Médio** política inconsistente entre fluxos

---

## 4. Sessão e cookies

### Session (`core/Session.php`)

| Parâmetro | Valor |
|-----------|-------|
| httponly | true |
| samesite | Lax |
| secure | auto-detect HTTPS ou SESSION_SECURE |
| regenerate | no login |

### Remember-me (`core/Auth.php`)

| Parâmetro | Valor |
|-----------|-------|
| Cookie | indique_remember |
| Token | 64 hex chars; DB stores SHA-256 |
| Comparação | hash_equals |
| Expiração | 30 dias |
| httponly | true |

### Riscos

- **Baixo** configuração sessão
- **Médio:** Sem rotação periódica de sessão além do login
- Session fixation mitigado por regenerate no login

---

## 5. Autorização e autenticação

### Usuário comum

- `AuthMiddleware::requireAuth()` — redirect /login
- Soft delete: campo `ativo` verificado no login

### Admin

**Arquivo:** múltiplos controllers

```php
if ($user['email'] !== 'admin@minasmais.com.br') { deny }
```

### Riscos — **ALTOS**

| ID | Risco | Severidade |
|----|-------|------------|
| SEC-001 | Admin único por email hardcoded — sem RBAC | Alta |
| SEC-002 | Qualquer usuário com email admin tem acesso total | Alta |
| SEC-003 | Sem audit log de ações admin (exceto eventos genéricos) | Média |
| SEC-004 | Sem 2FA admin | Média |

**Recomendação:** Coluna `is_admin` + bcrypt; nunca confiar só em email; considerar IP allowlist admin.

---

## 6. API externa (KOBE)

### Design previsto

- Bearer token (`API_TOKEN` no .env)
- Content-Type JSON obrigatório
- Log em `api_logs`

### Estado atual — **CRÍTICO**

| Problema | Impacto |
|----------|---------|
| ApiAuth middleware não registrado | API aberta ou boot error |
| ApiAuth não no bootstrap | Class not found |
| Sem rate limit na API | Brute force token |
| Token default em .env.example | Risco se copiado sem alterar |

### Riscos

- **Crítico:** Endpoint pode estar desprotegido
- **Alto:** Token em .env — garantir entropia e rotação

**Recomendação Fase 1:**
1. Corrigir middleware
2. Gerar token ≥ 32 bytes aleatórios
3. Rate limit por IP na API
4. Validar payload size limit

---

## 7. Proteção de arquivos sensíveis

### .htaccess raiz

```
RewriteRule ^(\.env|composer\.json|composer\.lock)$ - [F,L]
RewriteRule ^(core|database|controllers|models|config)/ - [F,L]
```

### .gitignore

```
.env
uploads/*
*.log
```

### uploads/

- `.htaccess` bloqueia execução PHP
- Logs em `uploads/logs/` protegidos

### Riscos

- **Baixo** se Apache + AllowOverride habilitado
- **Médio:** nginx sem equivalente — depende config server
- **Alto operacional:** `.env` no workspace — nunca commitar; rotacionar credenciais se expostas

---

## 8. XSS (Cross-Site Scripting)

### Mitigações

- Helper `e()` → `htmlspecialchars(ENT_QUOTES, UTF-8)` usado nas views
- `Validator::sanitizeString()` strip_tags nos inputs
- ApiIndicacaoController sanitiza payload JSON

### Riscos

- **Baixo** em views que usam `e()` consistentemente
- **Médio:** Verificar views admin com dados rich text (`texto_landing`, `motivo`) — escapar na output
- JSON responses API — OK

---

## 9. Rate limiting

| Fluxo | Implementação | Status |
|-------|---------------|--------|
| Login | RateLimiter estático, 5 tentativas / 15 min | ✅ |
| Convite cliques | Clique::isSpam | ✅ |
| Cadastro indicado | RateLimiter::attempt (instância) | ❌ Quebrado |
| API KOBE | Nenhum | ❌ |
| Admin | Nenhum | ⚠️ |

---

## 10. Upload de arquivos

- Sistema **não** implementa upload de usuário (banner campanha é URL externa)
- Diretório uploads apenas para logs

**Risco:** N/A no estado atual

---

## 11. Integrações futuras — superfície de ataque

### AppsFlyer webhook (planejado)

- `AppsFlyerSignatureValidator` preparado
- `webhook.validate_signature` default false — **deve ser true em prod**
- `raw_payload` armazenado — cuidado com PII

### VTEX

- Credenciais API ainda não configuradas
- Quando implementado: secrets em .env, nunca em código

---

## 12. Headers HTTP de segurança (ausentes)

Recomendado adicionar no `.htaccess` ou PHP:

| Header | Recomendação |
|--------|--------------|
| Strict-Transport-Security | max-age=31536000 (após SSL estável) |
| X-Content-Type-Options | nosniff |
| X-Frame-Options | DENY ou SAMEORIGIN |
| Content-Security-Policy | Gradual — pode quebrar inline scripts |
| Referrer-Policy | strict-origin-when-cross-origin |

**Status atual:** Não implementados

---

## 13. LGPD

- Checkbox aceite LGPD no cadastro (indicador e indicado)
- Campo `aceite_lgpd` persistido
- Exclusão de conta (soft delete) implementada em ProfileController
- EventLogger registra ações — revisar retenção e anonimização

**Pendente:** Política de privacidade linkada; DPO; exportação de dados.

---

## 14. Checklist pré-produção (segurança)

- [ ] `APP_DEBUG=false`
- [ ] `SESSION_SECURE=true`
- [ ] API_TOKEN forte e único
- [ ] DB password forte (Hostinger panel)
- [ ] Corrigir ApiAuth middleware
- [ ] Admin RBAC (não só email)
- [ ] SMTP TLS para reset senha
- [ ] SSL/TLS ativo no domínio
- [ ] Remover credenciais de documentação/checklists
- [ ] Rotacionar credenciais se .env ever committed
- [ ] Headers segurança básicos
- [ ] Rate limit API
- [ ] Revisar logs não gravam senhas/tokens

---

## 15. Matriz de riscos consolidada

| ID | Risco | Probabilidade | Impacto | Prioridade |
|----|-------|---------------|---------|------------|
| SEC-001 | Admin email hardcoded | Média | Alto | P1 |
| SEC-API | API sem auth funcional | Alta | Crítico | P0 |
| SEC-ENV | Credenciais em .env local | Média | Alto | P1 |
| SEC-HDR | Headers segurança ausentes | Alta | Médio | P2 |
| SEC-RST | Reset senha sem e-mail | Alta | Médio | P2 |
| SEC-POL | Política senha inconsistente | Média | Baixo | P3 |

---

## Conclusão

O núcleo de segurança web (CSRF, SQL, sessão, hash) está **bem implementado** para um PHP MVC custom. Os maiores riscos estão na **superfície admin** (autorização frágil) e **API KOBE** (middleware quebrado). Antes de produção com integrações externas, a Fase 1 do ROADMAP deve endereçar SEC-API e SEC-001 como prioridade máxima.

**Nota:** Este relatório não inclui credenciais, senhas ou tokens. Configurações sensíveis devem existir apenas no `.env` do servidor.

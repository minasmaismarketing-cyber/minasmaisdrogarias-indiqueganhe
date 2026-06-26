-- FASE 1.1 — Migração de domínio (links persistidos)
-- NÃO executar automaticamente. Revisar no phpMyAdmin antes de aplicar.
-- Banco alvo: u146248277_indicacao
--
-- Contexto: a coluna links_indicacao.url grava URL absoluta no momento da criação.
-- Após alterar APP_URL no .env, links já existentes podem continuar apontando
-- para o domínio anterior até este script ser executado.

-- 1) Verificar registros afetados (somente leitura)
SELECT id, codigo, url
FROM links_indicacao
WHERE url LIKE '%indique.nexden.com.br%'
   OR url LIKE '%nexden.com.br%';

-- 2) Atualizar domínio principal de convite
UPDATE links_indicacao
SET url = REPLACE(url, 'https://indique.nexden.com.br', 'https://indique.minasmaisdrogarias.com.br'),
    updated_at = NOW()
WHERE url LIKE '%indique.nexden.com.br%';

-- 3) Variante http (se existir)
UPDATE links_indicacao
SET url = REPLACE(url, 'http://indique.nexden.com.br', 'https://indique.minasmaisdrogarias.com.br'),
    updated_at = NOW()
WHERE url LIKE '%http://indique.nexden.com.br%';

-- 4) Confirmar que não restaram URLs antigas
SELECT id, codigo, url
FROM links_indicacao
WHERE url LIKE '%nexden%';

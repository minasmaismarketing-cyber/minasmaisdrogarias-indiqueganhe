-- Sprint 4.6 — Automação do fluxo de indicações (API KOBE)
-- Idempotente. Não recria tabelas. Não apaga dados.
-- Execute no phpMyAdmin (Hostinger). NÃO executar automaticamente em produção via app.

SET @schema = DATABASE();

-- 1) indicacoes.cpf_indicado
SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE indicacoes ADD COLUMN cpf_indicado VARCHAR(14) NULL AFTER telefone_indicado',
        'SELECT ''Coluna cpf_indicado já existe'' AS info'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema AND TABLE_NAME = 'indicacoes' AND COLUMN_NAME = 'cpf_indicado'
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

-- 2) indicacoes.email_indicado
SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE indicacoes ADD COLUMN email_indicado VARCHAR(191) NULL AFTER cpf_indicado',
        'SELECT ''Coluna email_indicado já existe'' AS info'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema AND TABLE_NAME = 'indicacoes' AND COLUMN_NAME = 'email_indicado'
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

-- 3) indicacoes.tipo_evento (auditoria API)
SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE indicacoes ADD COLUMN tipo_evento VARCHAR(20) NULL AFTER origem',
        'SELECT ''Coluna tipo_evento já existe'' AS info'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema AND TABLE_NAME = 'indicacoes' AND COLUMN_NAME = 'tipo_evento'
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

-- 4) indicacoes.plataforma (auditoria API)
SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE indicacoes ADD COLUMN plataforma VARCHAR(20) NULL AFTER tipo_evento',
        'SELECT ''Coluna plataforma já existe'' AS info'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema AND TABLE_NAME = 'indicacoes' AND COLUMN_NAME = 'plataforma'
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

-- 5) indicacoes.idempotency_key
SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE indicacoes ADD COLUMN idempotency_key VARCHAR(64) NULL AFTER plataforma',
        'SELECT ''Coluna idempotency_key já existe'' AS info'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema AND TABLE_NAME = 'indicacoes' AND COLUMN_NAME = 'idempotency_key'
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

-- 6) Índice CPF indicado
SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE indicacoes ADD KEY idx_indicacoes_cpf_indicado (cpf_indicado)',
        'SELECT ''Índice idx_indicacoes_cpf_indicado já existe'' AS info'
    )
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = @schema
      AND TABLE_NAME = 'indicacoes'
      AND INDEX_NAME = 'idx_indicacoes_cpf_indicado'
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

-- 7) Índice e-mail indicado
SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE indicacoes ADD KEY idx_indicacoes_email_indicado (email_indicado)',
        'SELECT ''Índice idx_indicacoes_email_indicado já existe'' AS info'
    )
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = @schema
      AND TABLE_NAME = 'indicacoes'
      AND INDEX_NAME = 'idx_indicacoes_email_indicado'
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

-- 8) Índice único idempotency_key (permite vários NULL)
SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE indicacoes ADD UNIQUE KEY uk_indicacoes_idempotency_key (idempotency_key)',
        'SELECT ''Índice uk_indicacoes_idempotency_key já existe'' AS info'
    )
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = @schema
      AND TABLE_NAME = 'indicacoes'
      AND INDEX_NAME = 'uk_indicacoes_idempotency_key'
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

-- 9) Tabela de respostas idempotentes (fallback / reenvio)
CREATE TABLE IF NOT EXISTS api_idempotency (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    idempotency_key VARCHAR(64) NOT NULL,
    endpoint VARCHAR(120) NOT NULL,
    status_code INT NOT NULL,
    response_json TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_api_idempotency (idempotency_key, endpoint)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'Sprint 4.6 — automação indicações OK' AS status;

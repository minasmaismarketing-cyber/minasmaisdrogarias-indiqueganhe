-- Sprint 4.7 — Consolidação automática e rastreabilidade
-- Idempotente. Não recria tabelas. Não apaga dados.
-- Execute no phpMyAdmin (Hostinger). NÃO executar automaticamente em produção via app.

SET @schema = DATABASE();

-- 1) data_ultimo_clique
SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE indicacoes ADD COLUMN data_ultimo_clique TIMESTAMP NULL AFTER updated_at',
        'SELECT ''Coluna data_ultimo_clique já existe'' AS info'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema AND TABLE_NAME = 'indicacoes' AND COLUMN_NAME = 'data_ultimo_clique'
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

-- 2) customer_id (opcional da KOBE)
SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE indicacoes ADD COLUMN customer_id VARCHAR(64) NULL AFTER email_indicado',
        'SELECT ''Coluna customer_id já existe'' AS info'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema AND TABLE_NAME = 'indicacoes' AND COLUMN_NAME = 'customer_id'
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

-- 3) associacao_estrategia (auditoria de matching da chamada API)
SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE indicacoes ADD COLUMN associacao_estrategia VARCHAR(64) NULL AFTER idempotency_key',
        'SELECT ''Coluna associacao_estrategia já existe'' AS info'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema AND TABLE_NAME = 'indicacoes' AND COLUMN_NAME = 'associacao_estrategia'
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

-- 4) Índice auxiliar: indicador + status (busca de indicação aberta)
SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE indicacoes ADD KEY idx_indicacoes_usuario_status (usuario_id, status)',
        'SELECT ''Índice idx_indicacoes_usuario_status já existe'' AS info'
    )
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = @schema
      AND TABLE_NAME = 'indicacoes'
      AND INDEX_NAME = 'idx_indicacoes_usuario_status'
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

-- 5) Índice customer_id
SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE indicacoes ADD KEY idx_indicacoes_customer_id (customer_id)',
        'SELECT ''Índice idx_indicacoes_customer_id já existe'' AS info'
    )
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = @schema
      AND TABLE_NAME = 'indicacoes'
      AND INDEX_NAME = 'idx_indicacoes_customer_id'
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

SELECT 'Sprint 4.7 — consolidação indicações OK' AS status;

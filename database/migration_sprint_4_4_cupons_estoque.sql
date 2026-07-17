-- Sprint 4.4 — Banco de cupons da campanha (estoque importável)
-- Idempotente. Não recria tabela. Não apaga dados.
-- Execute no phpMyAdmin (Hostinger) se necessário.

SET @schema = DATABASE();

-- 1) Widen codigo (códigos importados podem chegar a ~20+ chars)
SET @stmt = (
    SELECT IF(
        COUNT(*) > 0 AND MAX(CHARACTER_MAXIMUM_LENGTH) < 64,
        'ALTER TABLE cupons MODIFY COLUMN codigo VARCHAR(64) NOT NULL',
        'SELECT ''Coluna codigo já compatível'' AS info'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema AND TABLE_NAME = 'cupons' AND COLUMN_NAME = 'codigo'
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

-- 2) usuario_id nullable (estoque ainda não atribuído)
SET @stmt = (
    SELECT IF(
        COUNT(*) > 0 AND MAX(IS_NULLABLE) = 'NO',
        'ALTER TABLE cupons MODIFY COLUMN usuario_id INT UNSIGNED NULL',
        'SELECT ''Coluna usuario_id já nullable ou inexistente'' AS info'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema AND TABLE_NAME = 'cupons' AND COLUMN_NAME = 'usuario_id'
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

-- 3) indicacao_id nullable
SET @stmt = (
    SELECT IF(
        COUNT(*) > 0 AND MAX(IS_NULLABLE) = 'NO',
        'ALTER TABLE cupons MODIFY COLUMN indicacao_id INT UNSIGNED NULL',
        'SELECT ''Coluna indicacao_id já nullable ou inexistente'' AS info'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema AND TABLE_NAME = 'cupons' AND COLUMN_NAME = 'indicacao_id'
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

-- 4) atribuido_em
SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE cupons ADD COLUMN atribuido_em TIMESTAMP NULL AFTER utilizado_em',
        'SELECT ''Coluna atribuido_em já existe'' AS info'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema AND TABLE_NAME = 'cupons' AND COLUMN_NAME = 'atribuido_em'
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

-- 5) Índice composto para claim concorrente do estoque
SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE cupons ADD KEY idx_cupons_estoque (campanha_id, status, usuario_id)',
        'SELECT ''Índice idx_cupons_estoque já existe'' AS info'
    )
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = @schema
      AND TABLE_NAME = 'cupons'
      AND INDEX_NAME = 'idx_cupons_estoque'
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

SELECT 'Sprint 4.4 — cupons estoque OK' AS status;

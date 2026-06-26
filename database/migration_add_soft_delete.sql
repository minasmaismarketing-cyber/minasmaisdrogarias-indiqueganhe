-- Soft delete oficial — tabela usuarios
-- Sprint 1.2.1 | Execute manualmente no phpMyAdmin (banco u146248277_indicacao)
-- Idempotente: seguro executar mais de uma vez (MySQL / MariaDB)

SET @schema = DATABASE();

-- Coluna ativo
SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE usuarios ADD COLUMN ativo TINYINT(1) NOT NULL DEFAULT 1 AFTER whatsapp',
        'SELECT ''Coluna ativo já existe'' AS info'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema
      AND TABLE_NAME = 'usuarios'
      AND COLUMN_NAME = 'ativo'
);
PREPARE s FROM @stmt;
EXECUTE s;
DEALLOCATE PREPARE s;

-- Coluna deleted_at
SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE usuarios ADD COLUMN deleted_at DATETIME NULL AFTER ativo',
        'SELECT ''Coluna deleted_at já existe'' AS info'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema
      AND TABLE_NAME = 'usuarios'
      AND COLUMN_NAME = 'deleted_at'
);
PREPARE s FROM @stmt;
EXECUTE s;
DEALLOCATE PREPARE s;

-- Índice para consultas por status
SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE usuarios ADD INDEX idx_usuarios_ativo (ativo)',
        'SELECT ''Índice idx_usuarios_ativo já existe'' AS info'
    )
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = @schema
      AND TABLE_NAME = 'usuarios'
      AND INDEX_NAME = 'idx_usuarios_ativo'
);
PREPARE s FROM @stmt;
EXECUTE s;
DEALLOCATE PREPARE s;

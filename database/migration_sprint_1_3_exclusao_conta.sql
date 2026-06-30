-- Sprint 1.3 — Suporte à anonimização na exclusão de conta
-- Execute manualmente no phpMyAdmin APÓS migration_add_soft_delete.sql
-- Idempotente: seguro executar mais de uma vez (MySQL / MariaDB)

SET @schema = DATABASE();

-- cpf: valores anonimizados DEL_<id>_<timestamp> excedem CHAR(11)
SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'SELECT ''Coluna cpf já ampliada'' AS info',
        'ALTER TABLE usuarios MODIFY COLUMN cpf VARCHAR(64) NOT NULL'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema
      AND TABLE_NAME = 'usuarios'
      AND COLUMN_NAME = 'cpf'
      AND CHARACTER_MAXIMUM_LENGTH <= 11
);
PREPARE s FROM @stmt;
EXECUTE s;
DEALLOCATE PREPARE s;

-- telefone: nullable (anonimização) e sem unicidade
SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'SELECT ''Índice uk_usuarios_telefone já removido'' AS info',
        'ALTER TABLE usuarios DROP INDEX uk_usuarios_telefone'
    )
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = @schema
      AND TABLE_NAME = 'usuarios'
      AND INDEX_NAME = 'uk_usuarios_telefone'
);
PREPARE s FROM @stmt;
EXECUTE s;
DEALLOCATE PREPARE s;

SET @stmt = (
    SELECT IF(
        COUNT(*) > 0 AND IS_NULLABLE = 'NO',
        'ALTER TABLE usuarios MODIFY COLUMN telefone VARCHAR(20) NULL',
        'SELECT ''Coluna telefone já nullable'' AS info'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema
      AND TABLE_NAME = 'usuarios'
      AND COLUMN_NAME = 'telefone'
);
PREPARE s FROM @stmt;
EXECUTE s;
DEALLOCATE PREPARE s;

-- whatsapp: nullable na anonimização
SET @stmt = (
    SELECT IF(
        COUNT(*) > 0 AND IS_NULLABLE = 'NO',
        'ALTER TABLE usuarios MODIFY COLUMN whatsapp VARCHAR(20) NULL',
        'SELECT ''Coluna whatsapp já nullable'' AS info'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema
      AND TABLE_NAME = 'usuarios'
      AND COLUMN_NAME = 'whatsapp'
);
PREPARE s FROM @stmt;
EXECUTE s;
DEALLOCATE PREPARE s;

-- codigo_indicador: nullable na anonimização
SET @stmt = (
    SELECT IF(
        COUNT(*) > 0 AND IS_NULLABLE = 'NO',
        'ALTER TABLE usuarios MODIFY COLUMN codigo_indicador VARCHAR(10) NULL',
        'SELECT ''Coluna codigo_indicador já nullable'' AS info'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema
      AND TABLE_NAME = 'usuarios'
      AND COLUMN_NAME = 'codigo_indicador'
);
PREPARE s FROM @stmt;
EXECUTE s;
DEALLOCATE PREPARE s;

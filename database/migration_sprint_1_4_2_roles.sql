-- Sprint 1.4.2 — Papéis de usuário (RBAC base)
-- Execute manualmente no phpMyAdmin
-- Idempotente: seguro executar mais de uma vez (MySQL / MariaDB)

SET @schema = DATABASE();

SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE usuarios ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT ''cliente''',
        'SELECT ''Coluna role já existe'' AS info'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema
      AND TABLE_NAME = 'usuarios'
      AND COLUMN_NAME = 'role'
);
PREPARE s FROM @stmt;
EXECUTE s;
DEALLOCATE PREPARE s;

SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE usuarios ADD INDEX idx_usuarios_role (role)',
        'SELECT ''Índice idx_usuarios_role já existe'' AS info'
    )
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = @schema
      AND TABLE_NAME = 'usuarios'
      AND INDEX_NAME = 'idx_usuarios_role'
);
PREPARE s FROM @stmt;
EXECUTE s;
DEALLOCATE PREPARE s;

-- Dado de migração: promover conta legada (não é regra de runtime)
UPDATE usuarios
SET role = 'admin'
WHERE email = 'admin@minasmais.com.br'
  AND role <> 'admin';

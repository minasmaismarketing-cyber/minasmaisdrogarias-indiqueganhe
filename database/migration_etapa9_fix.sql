-- Fix da Etapa 9 — Ajuste para não recriar tabela eventos se já existir
-- Esta migration deve ser executada em um ambiente onde a tabela eventos já existe

-- Adiciona colunas ou índices faltantes somente se necessário

-- Verifica se a coluna referencia existe e caso não, adiciona
SET @col_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'eventos'
      AND COLUMN_NAME = 'referencia'
);

SET @sql = IF(
    @col_exists = 0,
    'ALTER TABLE eventos ADD COLUMN referencia VARCHAR(100) NULL AFTER evento',
    'SELECT ''Coluna referencia já existe'' AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Garanta índice em referencia apenas se não existir
SET @idx_exists = (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'eventos'
      AND INDEX_NAME = 'idx_eventos_referencia'
);

SET @sql = IF(
    @idx_exists = 0,
    'ALTER TABLE eventos ADD KEY idx_eventos_referencia (referencia)',
    'SELECT ''Índice idx_eventos_referencia já existe'' AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Adiciona constraint FK apenas se não existir
SET @fk_exists = (
    SELECT COUNT(*)
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'eventos'
      AND CONSTRAINT_NAME = 'fk_eventos_usuario'
);

SET @sql = IF(
    @fk_exists = 0,
    'ALTER TABLE eventos ADD CONSTRAINT fk_eventos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE SET NULL',
    'SELECT ''Constraint fk_eventos_usuario já existe'' AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'Migration Etapa 9 Fix OK — eventos atualizada sem recriar tabela.' AS status;

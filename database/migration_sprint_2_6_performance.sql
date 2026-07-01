-- Sprint 2.6 — Índices para consultas administrativas
-- Execute no phpMyAdmin selecionando o banco da aplicação.
-- Apenas índices ausentes e usados em filtros/ordenação do Admin.

SET @db := DATABASE();

-- validacao_indicacoes.status — filtros da listagem admin
SELECT COUNT(*) INTO @idx_exists
FROM information_schema.statistics
WHERE table_schema = @db
  AND table_name = 'validacao_indicacoes'
  AND index_name = 'idx_validacao_indicacoes_status';

SET @sql := IF(
    @idx_exists = 0,
    'ALTER TABLE validacao_indicacoes ADD INDEX idx_validacao_indicacoes_status (status)',
    'SELECT ''idx_validacao_indicacoes_status já existe'' AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- validacao_indicacoes.created_at — ordenação e filtros por período
SELECT COUNT(*) INTO @idx_exists
FROM information_schema.statistics
WHERE table_schema = @db
  AND table_name = 'validacao_indicacoes'
  AND index_name = 'idx_validacao_indicacoes_created_at';

SET @sql := IF(
    @idx_exists = 0,
    'ALTER TABLE validacao_indicacoes ADD INDEX idx_validacao_indicacoes_created_at (created_at)',
    'SELECT ''idx_validacao_indicacoes_created_at já existe'' AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- indicacoes.created_at — ordenação da listagem admin
SELECT COUNT(*) INTO @idx_exists
FROM information_schema.statistics
WHERE table_schema = @db
  AND table_name = 'indicacoes'
  AND index_name = 'idx_indicacoes_created_at';

SET @sql := IF(
    @idx_exists = 0,
    'ALTER TABLE indicacoes ADD INDEX idx_indicacoes_created_at (created_at)',
    'SELECT ''idx_indicacoes_created_at já existe'' AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'Migration Sprint 2.6 OK — índices de performance admin revisados.' AS status;

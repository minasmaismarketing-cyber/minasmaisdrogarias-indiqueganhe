-- Hotfix — Coluna slug ausente em campanhas
-- Execute no phpMyAdmin (banco de produção / Hostinger)
-- Idempotente: seguro executar mais de uma vez (MySQL / MariaDB)
--
-- Schema oficial (migration_etapa12.sql):
--   slug VARCHAR(150) NOT NULL
--   UNIQUE KEY uk_campanhas_slug (slug)
--
-- Não recria a tabela. Não altera dados de negócio existentes.
-- Apenas preenche slug NULL com valor derivado do id (necessário para NOT NULL + UNIQUE).

SET @schema = DATABASE();

-- 1) Adicionar coluna slug (NULL inicialmente para não falhar em linhas existentes)
SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE campanhas ADD COLUMN slug VARCHAR(150) NULL AFTER nome',
        'SELECT ''Coluna slug já existe'' AS info'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema
      AND TABLE_NAME = 'campanhas'
      AND COLUMN_NAME = 'slug'
);
PREPARE s FROM @stmt;
EXECUTE s;
DEALLOCATE PREPARE s;

-- 2) Preencher slugs vazios/nulos sem sobrescrever valores já definidos
UPDATE campanhas
SET slug = CONCAT('campanha-', id)
WHERE slug IS NULL OR slug = '';

-- 3) Garantir NOT NULL (alinha ao schema oficial)
SET @stmt = (
    SELECT IF(
        COUNT(*) > 0 AND MAX(IS_NULLABLE) = 'YES',
        'ALTER TABLE campanhas MODIFY COLUMN slug VARCHAR(150) NOT NULL',
        'SELECT ''Coluna slug já é NOT NULL ou inexistente'' AS info'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema
      AND TABLE_NAME = 'campanhas'
      AND COLUMN_NAME = 'slug'
);
PREPARE s FROM @stmt;
EXECUTE s;
DEALLOCATE PREPARE s;

-- 4) Índice UNIQUE previsto no schema oficial
SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE campanhas ADD UNIQUE KEY uk_campanhas_slug (slug)',
        'SELECT ''Índice uk_campanhas_slug já existe'' AS info'
    )
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = @schema
      AND TABLE_NAME = 'campanhas'
      AND INDEX_NAME = 'uk_campanhas_slug'
);
PREPARE s FROM @stmt;
EXECUTE s;
DEALLOCATE PREPARE s;

SELECT 'Migration hotfix campanhas.slug OK' AS status;

-- Sprint 4.2 — Sincronização definitiva da tabela campanhas
-- Schema oficial: models/Campanha.php + migration_etapa12.sql
-- Execute no phpMyAdmin (Hostinger / produção)
-- Idempotente: seguro executar mais de uma vez (MySQL / MariaDB)
--
-- Não recria a tabela.
-- Não remove colunas legadas (ativa, cupom_indicado).
-- Não sobrescreve dados já preenchidos.

SET @schema = DATABASE();

-- ============================================================================
-- 1) Colunas oficiais (adicionar somente se ausentes)
-- ============================================================================

SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE campanhas ADD COLUMN slug VARCHAR(150) NULL AFTER nome',
        'SELECT ''Coluna slug já existe'' AS info'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema AND TABLE_NAME = 'campanhas' AND COLUMN_NAME = 'slug'
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE campanhas ADD COLUMN descricao TEXT NULL AFTER slug',
        'SELECT ''Coluna descricao já existe'' AS info'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema AND TABLE_NAME = 'campanhas' AND COLUMN_NAME = 'descricao'
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE campanhas ADD COLUMN desconto DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER status',
        'SELECT ''Coluna desconto já existe'' AS info'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema AND TABLE_NAME = 'campanhas' AND COLUMN_NAME = 'desconto'
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE campanhas ADD COLUMN tipo_desconto ENUM(''PERCENTUAL'',''VALOR_FIXO'') NOT NULL DEFAULT ''PERCENTUAL'' AFTER desconto',
        'SELECT ''Coluna tipo_desconto já existe'' AS info'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema AND TABLE_NAME = 'campanhas' AND COLUMN_NAME = 'tipo_desconto'
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE campanhas ADD COLUMN valor_minimo_compra DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER tipo_desconto',
        'SELECT ''Coluna valor_minimo_compra já existe'' AS info'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema AND TABLE_NAME = 'campanhas' AND COLUMN_NAME = 'valor_minimo_compra'
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE campanhas ADD COLUMN limite_indicacoes_usuario INT UNSIGNED NOT NULL DEFAULT 0 AFTER valor_minimo_compra',
        'SELECT ''Coluna limite_indicacoes_usuario já existe'' AS info'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema AND TABLE_NAME = 'campanhas' AND COLUMN_NAME = 'limite_indicacoes_usuario'
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE campanhas ADD COLUMN banner VARCHAR(255) NULL AFTER fim',
        'SELECT ''Coluna banner já existe'' AS info'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema AND TABLE_NAME = 'campanhas' AND COLUMN_NAME = 'banner'
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE campanhas ADD COLUMN cor_primaria VARCHAR(7) NOT NULL DEFAULT ''#D71920'' AFTER banner',
        'SELECT ''Coluna cor_primaria já existe'' AS info'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema AND TABLE_NAME = 'campanhas' AND COLUMN_NAME = 'cor_primaria'
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE campanhas ADD COLUMN cor_secundaria VARCHAR(7) NOT NULL DEFAULT ''#7A7A7A'' AFTER cor_primaria',
        'SELECT ''Coluna cor_secundaria já existe'' AS info'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema AND TABLE_NAME = 'campanhas' AND COLUMN_NAME = 'cor_secundaria'
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE campanhas ADD COLUMN texto_botao VARCHAR(50) NOT NULL DEFAULT ''Quero participar'' AFTER cor_secundaria',
        'SELECT ''Coluna texto_botao já existe'' AS info'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema AND TABLE_NAME = 'campanhas' AND COLUMN_NAME = 'texto_botao'
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE campanhas ADD COLUMN texto_landing TEXT NULL AFTER texto_botao',
        'SELECT ''Coluna texto_landing já existe'' AS info'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema AND TABLE_NAME = 'campanhas' AND COLUMN_NAME = 'texto_landing'
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

-- ============================================================================
-- 2) Dados antigos — preencher apenas vazios (nunca sobrescrever)
-- ============================================================================

UPDATE campanhas
SET slug = CONCAT('campanha-', id)
WHERE slug IS NULL OR slug = '';

-- ============================================================================
-- 3) ENUM status — expandir → migrar legado → reduzir ao oficial
-- ============================================================================

-- 3a) Expandir ENUM para união (legado + oficial), se ainda não estiver no formato final
SET @status_type = (
    SELECT COLUMN_TYPE
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema
      AND TABLE_NAME = 'campanhas'
      AND COLUMN_NAME = 'status'
    LIMIT 1
);

SET @stmt = (
    SELECT IF(
        @status_type IS NOT NULL
        AND @status_type <> 'enum(''ATIVA'',''INATIVA'',''AGENDADA'',''FINALIZADA'')',
        'ALTER TABLE campanhas MODIFY COLUMN status ENUM(''RASCUNHO'',''ATIVA'',''PAUSADA'',''FINALIZADA'',''INATIVA'',''AGENDADA'') NOT NULL DEFAULT ''INATIVA''',
        'SELECT ''ENUM status já no formato final ou coluna ausente — skip expand'' AS info'
    )
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

-- 3b) Migrar valores legados
UPDATE campanhas
SET status = 'INATIVA'
WHERE status IN ('RASCUNHO', 'PAUSADA');

-- 3c) Reduzir ENUM ao schema oficial
SET @status_type = (
    SELECT COLUMN_TYPE
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema
      AND TABLE_NAME = 'campanhas'
      AND COLUMN_NAME = 'status'
    LIMIT 1
);

SET @stmt = (
    SELECT IF(
        @status_type IS NOT NULL
        AND @status_type <> 'enum(''ATIVA'',''INATIVA'',''AGENDADA'',''FINALIZADA'')',
        'ALTER TABLE campanhas MODIFY COLUMN status ENUM(''ATIVA'',''INATIVA'',''AGENDADA'',''FINALIZADA'') NOT NULL DEFAULT ''INATIVA''',
        'SELECT ''ENUM status já sincronizado'' AS info'
    )
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

-- ============================================================================
-- 4) slug NOT NULL (após backfill)
-- ============================================================================

SET @stmt = (
    SELECT IF(
        COUNT(*) > 0 AND MAX(IS_NULLABLE) = 'YES',
        'ALTER TABLE campanhas MODIFY COLUMN slug VARCHAR(150) NOT NULL',
        'SELECT ''Coluna slug já é NOT NULL ou inexistente'' AS info'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema AND TABLE_NAME = 'campanhas' AND COLUMN_NAME = 'slug'
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

-- ============================================================================
-- 5) Índices oficiais (somente se inexistentes)
-- ============================================================================

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
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE campanhas ADD KEY idx_campanhas_periodo (inicio, fim)',
        'SELECT ''Índice idx_campanhas_periodo já existe'' AS info'
    )
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = @schema
      AND TABLE_NAME = 'campanhas'
      AND INDEX_NAME = 'idx_campanhas_periodo'
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE campanhas ADD KEY idx_campanhas_status (status)',
        'SELECT ''Índice idx_campanhas_status já existe'' AS info'
    )
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = @schema
      AND TABLE_NAME = 'campanhas'
      AND INDEX_NAME = 'idx_campanhas_status'
);
PREPARE s FROM @stmt; EXECUTE s; DEALLOCATE PREPARE s;

SELECT 'Sprint 4.2 — campanhas sincronizada com schema oficial' AS status;

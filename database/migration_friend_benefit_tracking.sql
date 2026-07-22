-- Rastreio de visualização e compartilhamento do benefício 5% do amigo (por indicação).
-- Não executar em produção sem backup. Rodar manualmente após revisão.
-- Idempotente: só adiciona colunas se não existirem.

SET @schema = DATABASE();

SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE indicacoes ADD COLUMN friend_benefit_seen_at DATETIME NULL AFTER status_message_dismissed_at',
        'SELECT ''Coluna friend_benefit_seen_at já existe'' AS info'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema
      AND TABLE_NAME = 'indicacoes'
      AND COLUMN_NAME = 'friend_benefit_seen_at'
);
PREPARE s FROM @stmt;
EXECUTE s;
DEALLOCATE PREPARE s;

SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE indicacoes ADD COLUMN friend_benefit_shared_at DATETIME NULL AFTER friend_benefit_seen_at',
        'SELECT ''Coluna friend_benefit_shared_at já existe'' AS info'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema
      AND TABLE_NAME = 'indicacoes'
      AND COLUMN_NAME = 'friend_benefit_shared_at'
);
PREPARE s FROM @stmt;
EXECUTE s;
DEALLOCATE PREPARE s;

-- Garante status_message_dismissed_at caso a migration anterior ainda não tenha sido aplicada.
SET @stmt = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE indicacoes ADD COLUMN status_message_dismissed_at DATETIME NULL AFTER updated_at',
        'SELECT ''Coluna status_message_dismissed_at já existe'' AS info'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema
      AND TABLE_NAME = 'indicacoes'
      AND COLUMN_NAME = 'status_message_dismissed_at'
);
PREPARE s FROM @stmt;
EXECUTE s;
DEALLOCATE PREPARE s;

SELECT 'Migration friend_benefit_tracking OK' AS status;

-- Ciência do card de status ("Entendi") por indicação.
-- Não executar em produção sem backup. Rodar manualmente após revisão.
-- Idempotente: só adiciona se a coluna não existir.

SET @schema = DATABASE();

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

SELECT 'Migration status_message_dismissed_at OK' AS status;

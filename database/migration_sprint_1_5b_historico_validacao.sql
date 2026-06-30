-- Sprint 1.5B — Alinhar historico_validacoes ao fluxo validacao_indicacoes
-- Execute manualmente no phpMyAdmin. Não remove registros existentes.

ALTER TABLE historico_validacoes
    MODIFY COLUMN status_anterior VARCHAR(40) NOT NULL,
    MODIFY COLUMN status_novo VARCHAR(40) NOT NULL;

SELECT 'Migration Sprint 1.5B OK — historico_validacoes preparado para validacao_indicacoes.' AS status;

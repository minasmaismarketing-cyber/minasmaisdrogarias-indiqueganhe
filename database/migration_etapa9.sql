-- Etapa 9 — Sistema de Eventos
-- Execute no phpMyAdmin selecionando o banco u146248277_indicacao
-- Esta migration é idempotente - pode ser executada múltiplas vezes

-- Tabela de eventos
CREATE TABLE IF NOT EXISTS eventos (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id INT UNSIGNED NULL,
    evento VARCHAR(50) NOT NULL,
    referencia VARCHAR(100) NULL,
    payload TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_eventos_usuario (usuario_id),
    KEY idx_eventos_tipo (evento),
    KEY idx_eventos_referencia (referencia),
    KEY idx_eventos_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Adicionar constraint FK apenas se não existir
SET @fk_exists = (
    SELECT COUNT(*)
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'eventos'
    AND CONSTRAINT_NAME = 'fk_eventos_usuario'
);

SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE eventos ADD CONSTRAINT fk_eventos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE SET NULL',
    'SELECT ''Constraint fk_eventos_usuario já existe'' AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'Migration Etapa 9 OK — tabela eventos criada/atualizada.' AS status;

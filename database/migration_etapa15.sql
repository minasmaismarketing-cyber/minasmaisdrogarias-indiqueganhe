-- Etapa 15 — API de Confirmação do Indicado
-- Execute no phpMyAdmin selecionando o banco u146248277_indicacao

-- Tabela de logs da API
CREATE TABLE IF NOT EXISTS api_logs (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    endpoint VARCHAR(255) NOT NULL,
    payload TEXT NULL,
    response TEXT NULL,
    ip VARCHAR(45) NULL,
    status_code INT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_api_logs_endpoint (endpoint),
    KEY idx_api_logs_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'Migration Etapa 15 OK — tabela api_logs criada.' AS status;

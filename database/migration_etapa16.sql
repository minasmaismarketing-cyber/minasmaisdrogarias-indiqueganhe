-- Etapa 16 — Preparação para Integração AppsFlyer
-- Execute no phpMyAdmin selecionando o banco u146248277_indicacao

-- Tabela de eventos AppsFlyer
CREATE TABLE IF NOT EXISTS appsflyer_events (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id INT UNSIGNED NULL,
    indicacao_id INT UNSIGNED NULL,
    appsflyer_id VARCHAR(255) NULL,
    event_name VARCHAR(100) NULL,
    event_value DECIMAL(10, 2) NULL,
    install_type ENUM('FIRST_INSTALL', 'REINSTALL', 'REENGAGEMENT', 'UNKNOWN') NULL,
    media_source VARCHAR(100) NULL,
    campaign VARCHAR(255) NULL,
    campaign_id VARCHAR(100) NULL,
    af_status ENUM('PENDING', 'RECEIVED', 'VALIDATED', 'INVALID') NOT NULL DEFAULT 'PENDING',
    platform VARCHAR(20) NULL,
    raw_payload TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_appsflyer_usuario (usuario_id),
    KEY idx_appsflyer_indicacao (indicacao_id),
    KEY idx_appsflyer_appsflyer_id (appsflyer_id),
    KEY idx_appsflyer_status (af_status),
    KEY idx_appsflyer_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'Migration Etapa 16 OK — tabela appsflyer_events criada.' AS status;

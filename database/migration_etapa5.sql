-- Etapa 5 — Compartilhamento e Geração de Link
-- Execute no phpMyAdmin selecionando o banco u146248277_indicacao

-- Tabela de links de indicação (1 por usuário)
CREATE TABLE IF NOT EXISTS links_indicacao (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id INT UNSIGNED NOT NULL,
    codigo VARCHAR(10) NOT NULL,
    slug VARCHAR(20) NOT NULL,
    url VARCHAR(255) NOT NULL,
    cliques INT UNSIGNED NOT NULL DEFAULT 0,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    appsflyer_enabled TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_links_usuario (usuario_id),
    UNIQUE KEY uk_links_codigo (codigo),
    UNIQUE KEY uk_links_slug (slug),
    KEY idx_links_ativo (ativo),
    CONSTRAINT fk_links_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de cliques nos links
CREATE TABLE IF NOT EXISTS cliques (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    codigo VARCHAR(10) NOT NULL,
    ip VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_cliques_codigo (codigo),
    KEY idx_cliques_ip (ip),
    KEY idx_cliques_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'Migration Etapa 5 OK — tabelas links_indicacao e cliques criadas.' AS status;

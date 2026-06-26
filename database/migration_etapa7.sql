-- Etapa 7 — Simulação Completa do Fluxo da Campanha
-- Execute no phpMyAdmin selecionando o banco u146248277_indicacao

-- Tabela de campanhas
CREATE TABLE IF NOT EXISTS campanhas (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome VARCHAR(150) NOT NULL,
    ativa TINYINT(1) NOT NULL DEFAULT 0,
    inicio DATE NOT NULL,
    fim DATE NOT NULL,
    cupom_indicado VARCHAR(50) NULL,
    status ENUM('RASCUNHO', 'ATIVA', 'PAUSADA', 'FINALIZADA') NOT NULL DEFAULT 'RASCUNHO',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_campanhas_ativa (ativa),
    KEY idx_campanhas_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de eventos de indicação
CREATE TABLE IF NOT EXISTS eventos_indicacao (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id INT UNSIGNED NOT NULL,
    evento VARCHAR(50) NOT NULL,
    dados TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_eventos_usuario (usuario_id),
    KEY idx_eventos_tipo (evento),
    KEY idx_eventos_created (created_at),
    CONSTRAINT fk_eventos_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'Migration Etapa 7 OK — tabelas campanhas e eventos_indicacao criadas.' AS status;

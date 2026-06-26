-- Etapa 14 — Sistema de Cupons
-- Execute no phpMyAdmin selecionando o banco u146248277_indicacao

-- Tabela de cupons
CREATE TABLE IF NOT EXISTS cupons (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    codigo VARCHAR(20) NOT NULL,
    usuario_id INT UNSIGNED NOT NULL,
    indicacao_id INT UNSIGNED NOT NULL,
    campanha_id INT UNSIGNED NOT NULL,
    tipo ENUM('PERCENTUAL', 'VALOR_FIXO') NOT NULL,
    valor DECIMAL(10, 2) NOT NULL,
    status ENUM('DISPONIVEL', 'RESERVADO', 'UTILIZADO', 'EXPIRADO', 'CANCELADO') NOT NULL DEFAULT 'DISPONIVEL',
    origem VARCHAR(50) NULL,
    validade DATE NULL,
    utilizado_em TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY idx_cupons_codigo (codigo),
    KEY idx_cupons_usuario (usuario_id),
    KEY idx_cupons_indicacao (indicacao_id),
    KEY idx_cupons_campanha (campanha_id),
    KEY idx_cupons_status (status),
    KEY idx_cupons_validade (validade)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de histórico de cupons
CREATE TABLE IF NOT EXISTS historico_cupons (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    cupom_id INT UNSIGNED NOT NULL,
    status_anterior ENUM('DISPONIVEL', 'RESERVADO', 'UTILIZADO', 'EXPIRADO', 'CANCELADO') NOT NULL,
    status_novo ENUM('DISPONIVEL', 'RESERVADO', 'UTILIZADO', 'EXPIRADO', 'CANCELADO') NOT NULL,
    descricao TEXT NULL,
    usuario_admin VARCHAR(150) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_historico_cupom (cupom_id),
    KEY idx_historico_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'Migration Etapa 14 OK — tabelas cupons e historico_cupons criadas.' AS status;

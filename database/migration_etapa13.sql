-- Etapa 13 — Motor de Validação das Indicações
-- Execute no phpMyAdmin selecionando o banco u146248277_indicacao

-- Tabela de validações
CREATE TABLE IF NOT EXISTS validacoes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    indicacao_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NOT NULL,
    status ENUM('PENDENTE', 'EM_ANALISE', 'VALIDADO', 'INVALIDADO', 'CANCELADO') NOT NULL DEFAULT 'PENDENTE',
    motivo TEXT NULL,
    validado_em TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_validacoes_indicacao (indicacao_id),
    KEY idx_validacoes_usuario (usuario_id),
    KEY idx_validacoes_status (status),
    KEY idx_validacoes_validado_em (validado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de histórico de validações
CREATE TABLE IF NOT EXISTS historico_validacoes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    validacao_id INT UNSIGNED NOT NULL,
    status_anterior ENUM('PENDENTE', 'EM_ANALISE', 'VALIDADO', 'INVALIDADO', 'CANCELADO') NOT NULL,
    status_novo ENUM('PENDENTE', 'EM_ANALISE', 'VALIDADO', 'INVALIDADO', 'CANCELADO') NOT NULL,
    descricao TEXT NULL,
    usuario_admin VARCHAR(150) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_historico_validacao (validacao_id),
    KEY idx_historico_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'Migration Etapa 13 OK — tabelas validacoes e historico_validacoes criadas.' AS status;

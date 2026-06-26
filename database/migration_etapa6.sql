-- Etapa 6 — Preparação para Validação de Indicações
-- Execute no phpMyAdmin selecionando o banco u146248277_indicacao

-- Tabela de validações de indicações
CREATE TABLE IF NOT EXISTS validacoes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id INT UNSIGNED NOT NULL,
    cpf_indicado VARCHAR(14) NOT NULL,
    telefone_indicado VARCHAR(20) NOT NULL,
    status ENUM('PENDENTE', 'EM_ANALISE', 'VALIDADO', 'INVALIDADO', 'BENEFICIO_LIBERADO') NOT NULL DEFAULT 'PENDENTE',
    motivo TEXT NULL,
    premio_disponivel TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_validacoes_usuario (usuario_id),
    KEY idx_validacoes_cpf (cpf_indicado),
    KEY idx_validacoes_telefone (telefone_indicado),
    KEY idx_validacoes_status (status),
    CONSTRAINT fk_validacoes_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de notificações
CREATE TABLE IF NOT EXISTS notificacoes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id INT UNSIGNED NOT NULL,
    tipo ENUM('informacao', 'sucesso', 'alerta') NOT NULL DEFAULT 'informacao',
    mensagem VARCHAR(255) NOT NULL,
    link VARCHAR(255) NULL,
    lida TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_notificacoes_usuario (usuario_id),
    KEY idx_notificacoes_lida (lida),
    KEY idx_notificacoes_tipo (tipo),
    CONSTRAINT fk_notificacoes_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'Migration Etapa 6 OK — tabelas validacoes e notificacoes criadas.' AS status;

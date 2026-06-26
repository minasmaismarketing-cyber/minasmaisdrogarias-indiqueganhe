-- Etapa 4 — Área do Usuário e Sistema de Indicações
-- Execute no phpMyAdmin selecionando o banco u146248277_indicacao

-- Drop existing table to recreate with new structure
DROP TABLE IF EXISTS indicacoes;

-- Create updated indicacoes table
CREATE TABLE IF NOT EXISTS indicacoes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id INT UNSIGNED NOT NULL,
    codigo_indicador VARCHAR(10) NOT NULL,
    codigo_referencia VARCHAR(20) NULL,
    status ENUM(
        'AGUARDANDO',
        'LINK_ACESSADO',
        'CADASTRO_PENDENTE',
        'VALIDADO',
        'PREMIO_LIBERADO',
        'INVALIDO',
        'EXPIRADO'
    ) NOT NULL DEFAULT 'AGUARDANDO',
    origem VARCHAR(50) NULL DEFAULT 'WEB',
    premio_liberado TINYINT(1) NOT NULL DEFAULT 0,
    nome_indicado VARCHAR(150) NULL,
    telefone_indicado VARCHAR(20) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_indicacoes_usuario (usuario_id),
    KEY idx_indicacoes_codigo (codigo_indicador),
    KEY idx_indicacoes_referencia (codigo_referencia),
    KEY idx_indicacoes_status (status),
    KEY idx_indicacoes_telefone (telefone_indicado),
    KEY idx_indicacoes_origem (origem),
    CONSTRAINT fk_indicacoes_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'Migration Etapa 4 OK — tabela indicacoes atualizada.' AS status;

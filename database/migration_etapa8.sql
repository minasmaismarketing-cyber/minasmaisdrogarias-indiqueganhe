-- Etapa 8 — Fluxo Completo do Indicado
-- Execute no phpMyAdmin selecionando o banco u146248277_indicacao

-- Tabela de indicados
CREATE TABLE IF NOT EXISTS indicados (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    codigo_indicador VARCHAR(10) NOT NULL,
    usuario_indicador_id INT UNSIGNED NULL,
    nome VARCHAR(150) NOT NULL,
    cpf VARCHAR(11) NOT NULL,
    telefone VARCHAR(15) NOT NULL,
    email VARCHAR(150) NOT NULL,
    senha_hash VARCHAR(255) NULL,
    status ENUM('LINK_ACESSADO', 'CADASTRO_INICIADO', 'CADASTRO_CONCLUIDO', 'AGUARDANDO_VALIDACAO', 'VALIDADO', 'INVALIDADO') NOT NULL DEFAULT 'LINK_ACESSADO',
    origem VARCHAR(50) NULL,
    aceite_lgpd TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY idx_indicados_cpf (cpf),
    UNIQUE KEY idx_indicados_email (email),
    UNIQUE KEY idx_indicados_telefone (telefone),
    KEY idx_indicados_codigo (codigo_indicador),
    KEY idx_indicados_usuario (usuario_indicador_id),
    KEY idx_indicados_status (status),
    CONSTRAINT fk_indicados_usuario
        FOREIGN KEY (usuario_indicador_id) REFERENCES usuarios (id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'Migration Etapa 8 OK — tabela indicados criada.' AS status;

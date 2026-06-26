-- Migration: Criar tabela indicados
-- Executa seguramente com IF NOT EXISTS

CREATE TABLE IF NOT EXISTS indicados (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    codigo_indicador VARCHAR(10) NOT NULL,
    usuario_indicador_id INT UNSIGNED NOT NULL,
    nome VARCHAR(150) NOT NULL,
    cpf CHAR(11) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    email VARCHAR(180) NOT NULL,
    senha_hash VARCHAR(255) NULL,
    status ENUM(
        'LINK_ACESSADO',
        'CADASTRO_INICIADO',
        'CADASTRO_CONCLUIDO',
        'AGUARDANDO_VALIDACAO',
        'VALIDADO',
        'INVALIDADO'
    ) NOT NULL DEFAULT 'LINK_ACESSADO',
    origem VARCHAR(255) NULL,
    aceite_lgpd TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_indicados_codigo_indicador (codigo_indicador),
    KEY idx_indicados_usuario_indicador (usuario_indicador_id),
    UNIQUE KEY uk_indicados_cpf (cpf),
    UNIQUE KEY uk_indicados_email (email),
    UNIQUE KEY uk_indicados_telefone (telefone),
    CONSTRAINT fk_indicados_usuario
        FOREIGN KEY (usuario_indicador_id) REFERENCES usuarios (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

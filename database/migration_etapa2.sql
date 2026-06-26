-- Etapa 2 — Cadastro e Login
-- Execute no phpMyAdmin selecionando o banco u146248277_indicacao

CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome VARCHAR(150) NOT NULL,
    cpf CHAR(11) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    email VARCHAR(180) NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    aceite_lgpd TINYINT(1) NOT NULL DEFAULT 0,
    codigo_indicador VARCHAR(10) NOT NULL,
    cupom_recebido TINYINT(1) NOT NULL DEFAULT 0,
    whatsapp VARCHAR(20) NOT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_usuarios_cpf (cpf),
    UNIQUE KEY uk_usuarios_telefone (telefone),
    UNIQUE KEY uk_usuarios_email (email),
    UNIQUE KEY uk_usuarios_codigo_indicador (codigo_indicador)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS senha_recuperacao (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id INT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_senha_recuperacao_usuario (usuario_id),
    KEY idx_senha_recuperacao_token (token_hash),
    CONSTRAINT fk_senha_recuperacao_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS login_tentativas (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    chave VARCHAR(128) NOT NULL,
    tentativas INT UNSIGNED NOT NULL DEFAULT 1,
    bloqueado_ate DATETIME NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_login_tentativas_chave (chave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Etapa 3 — Sistema de Indicação
-- Execute no phpMyAdmin selecionando o banco u146248277_indicacao

CREATE TABLE IF NOT EXISTS indicacoes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id INT UNSIGNED NOT NULL,
    codigo_indicador VARCHAR(10) NOT NULL,
    nome_indicado VARCHAR(150) NULL,
    telefone_indicado VARCHAR(20) NULL,
    status ENUM(
        'AGUARDANDO',
        'LINK_ACESSADO',
        'CADASTRADO',
        'VALIDADO',
        'PREMIADO',
        'EXPIRADO'
    ) NOT NULL DEFAULT 'AGUARDANDO',
    cupom_liberado TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_indicacoes_usuario (usuario_id),
    KEY idx_indicacoes_codigo (codigo_indicador),
    KEY idx_indicacoes_status (status),
    KEY idx_indicacoes_telefone (telefone_indicado),
    CONSTRAINT fk_indicacoes_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

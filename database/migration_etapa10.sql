-- Etapa 10 — Motor de Validação de Indicações
-- Cria a tabela de validação interna para controlar o fluxo de validação

CREATE TABLE IF NOT EXISTS validacao_indicacoes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    indicacao_id INT UNSIGNED NOT NULL,
    usuario_indicador_id INT UNSIGNED NULL,
    usuario_indicado_id INT UNSIGNED NULL,
    possui_app TINYINT(1) NOT NULL DEFAULT 0,
    cadastro_concluido TINYINT(1) NOT NULL DEFAULT 0,
    primeiro_acesso TINYINT(1) NOT NULL DEFAULT 0,
    elegivel TINYINT(1) NOT NULL DEFAULT 0,
    motivo_bloqueio VARCHAR(100) NULL,
    beneficio_disponivel TINYINT(1) NOT NULL DEFAULT 0,
    status VARCHAR(40) NOT NULL DEFAULT 'PENDENTE',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_validacao_indicacao (indicacao_id),
    KEY idx_validacao_indicador (usuario_indicador_id),
    KEY idx_validacao_indicado (usuario_indicado_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'Migration Etapa 10 OK — tabela validacao_indicacoes criada/atualizada.' AS status;

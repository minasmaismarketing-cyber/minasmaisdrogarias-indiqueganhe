-- Etapa 12 — Painel Administrativo de Campanhas
-- Execute no phpMyAdmin selecionando o banco u146248277_indicacao

-- Tabela de campanhas
CREATE TABLE IF NOT EXISTS campanhas (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome VARCHAR(150) NOT NULL,
    slug VARCHAR(150) NOT NULL,
    descricao TEXT NULL,
    status ENUM('ATIVA', 'INATIVA', 'AGENDADA', 'FINALIZADA') NOT NULL DEFAULT 'INATIVA',
    desconto DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    tipo_desconto ENUM('PERCENTUAL', 'VALOR_FIXO') NOT NULL DEFAULT 'PERCENTUAL',
    valor_minimo_compra DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    limite_indicacoes_usuario INT UNSIGNED NOT NULL DEFAULT 0,
    inicio DATE NOT NULL,
    fim DATE NOT NULL,
    banner VARCHAR(255) NULL,
    cor_primaria VARCHAR(7) NOT NULL DEFAULT '#D71920',
    cor_secundaria VARCHAR(7) NOT NULL DEFAULT '#7A7A7A',
    texto_botao VARCHAR(50) NOT NULL DEFAULT 'Quero participar',
    texto_landing TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_campanhas_slug (slug),
    KEY idx_campanhas_status (status),
    KEY idx_campanhas_periodo (inicio, fim)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'Migration Etapa 12 OK — tabela campanhas criada.' AS status;

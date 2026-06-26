-- Migration para adicionar colunas de soft delete na tabela usuarios
-- Execute no phpMyAdmin selecionando o banco u146248277_indicacao

-- Adicionar coluna ativo se não existir
ALTER TABLE usuarios
ADD COLUMN IF NOT EXISTS ativo TINYINT(1) NOT NULL DEFAULT 1
AFTER whatsapp;

-- Adicionar coluna deleted_at se não existir
ALTER TABLE usuarios
ADD COLUMN IF NOT EXISTS deleted_at DATETIME NULL
AFTER ativo;

-- Adicionar índice para busca de usuários ativos
ALTER TABLE usuarios
ADD INDEX IF NOT EXISTS idx_usuarios_ativo (ativo);

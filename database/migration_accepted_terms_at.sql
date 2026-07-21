-- Aceite legal no cadastro
-- Reutiliza usuarios.aceite_lgpd (boolean existente).
-- Adiciona apenas o timestamp do aceite dos documentos legais.
-- Não executar em produção sem backup. Rodar manualmente após revisão.

ALTER TABLE usuarios
    ADD COLUMN accepted_terms_at DATETIME NULL AFTER aceite_lgpd;

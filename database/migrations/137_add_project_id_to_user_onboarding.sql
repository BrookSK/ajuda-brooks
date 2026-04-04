-- Adiciona project_id ao onboarding mobile para vincular projeto ao usuário
ALTER TABLE user_onboarding
    ADD COLUMN project_id INT UNSIGNED NULL AFTER wants_documents;

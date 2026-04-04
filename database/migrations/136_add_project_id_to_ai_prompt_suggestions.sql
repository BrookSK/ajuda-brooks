-- Adiciona project_id às sugestões de prompt para vincular ao projeto
ALTER TABLE ai_prompt_suggestions
    ADD COLUMN project_id INT UNSIGNED NULL AFTER id,
    ADD COLUMN source_conversation_id INT UNSIGNED NULL AFTER rationale,
    ADD INDEX idx_project_status (project_id, status);

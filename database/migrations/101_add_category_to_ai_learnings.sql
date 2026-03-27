ALTER TABLE ai_learnings
    ADD COLUMN IF NOT EXISTS category VARCHAR(80) NULL COMMENT 'categoria semântica do aprendizado (ex: costura, tecidos, marketing_digital)',
    ADD INDEX IF NOT EXISTS idx_category (category);

ALTER TABLE ai_learnings
    ADD COLUMN IF NOT EXISTS embedding_vector MEDIUMTEXT NULL COMMENT 'JSON array float32 (1536 dims) gerado por text-embedding-3-small',
    ADD COLUMN IF NOT EXISTS quality_score TINYINT UNSIGNED NULL COMMENT 'score 0-10 de qualidade atribuído pelo Claude na extração',
    ADD COLUMN IF NOT EXISTS is_consolidated TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = aprendizado consolidado (resumo de múltiplos)',
    ADD INDEX IF NOT EXISTS idx_quality (quality_score),
    ADD INDEX IF NOT EXISTS idx_consolidated (is_consolidated);

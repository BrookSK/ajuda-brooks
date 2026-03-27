ALTER TABLE ai_learnings
    ADD COLUMN IF NOT EXISTS learning_type ENUM('fact','experience','warning') NOT NULL DEFAULT 'fact'
        COMMENT 'fact=conhecimento geral; experience=padrão de problema/solução vivido por usuário; warning=alerta proativo para situações recorrentes',
    ADD INDEX IF NOT EXISTS idx_learning_type (learning_type);

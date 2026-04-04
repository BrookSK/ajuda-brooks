-- Corrige migrations 101, 104, 105 que usavam ADD COLUMN IF NOT EXISTS (sintaxe MariaDB)
-- Adiciona colunas faltantes na tabela ai_learnings com sintaxe MySQL padrão

-- Migration 101: category
SET @col_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ai_learnings' AND COLUMN_NAME = 'category'
);
SET @sql := IF(@col_exists = 0,
    'ALTER TABLE ai_learnings ADD COLUMN category VARCHAR(80) NULL COMMENT \'categoria semântica do aprendizado\', ADD INDEX idx_category (category)',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Migration 104: embedding_vector, quality_score, is_consolidated
SET @col_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ai_learnings' AND COLUMN_NAME = 'embedding_vector'
);
SET @sql := IF(@col_exists = 0,
    'ALTER TABLE ai_learnings ADD COLUMN embedding_vector MEDIUMTEXT NULL COMMENT \'JSON array float32 (1536 dims)\'',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ai_learnings' AND COLUMN_NAME = 'quality_score'
);
SET @sql := IF(@col_exists = 0,
    'ALTER TABLE ai_learnings ADD COLUMN quality_score TINYINT UNSIGNED NULL COMMENT \'score 0-10 de qualidade\', ADD INDEX idx_quality (quality_score)',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ai_learnings' AND COLUMN_NAME = 'is_consolidated'
);
SET @sql := IF(@col_exists = 0,
    'ALTER TABLE ai_learnings ADD COLUMN is_consolidated TINYINT(1) NOT NULL DEFAULT 0 COMMENT \'1 = aprendizado consolidado\', ADD INDEX idx_consolidated (is_consolidated)',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Migration 105: learning_type
SET @col_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ai_learnings' AND COLUMN_NAME = 'learning_type'
);
SET @sql := IF(@col_exists = 0,
    'ALTER TABLE ai_learnings ADD COLUMN learning_type ENUM(\'fact\',\'experience\',\'warning\') NOT NULL DEFAULT \'fact\', ADD INDEX idx_learning_type (learning_type)',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Marca as migrations originais como executadas para não tentar de novo
INSERT IGNORE INTO _migrations (migration) VALUES
    ('database/migrations/101_add_category_to_ai_learnings.sql'),
    ('database/migrations/104_add_embedding_to_ai_learnings.sql'),
    ('database/migrations/105_add_learning_type_to_ai_learnings.sql'),
    ('database/migrations/089_create_news_user_preferences.sql'),
    ('database/migrations/090_create_news_email_deliveries.sql'),
    ('database/migrations/095_create_pages.sql'),
    ('database/migrations/096_create_page_shares.sql'),
    ('database/migrations/100_create_kanban_boards.sql'),
    ('database/migrations/101_create_kanban_lists.sql'),
    ('database/migrations/102_create_kanban_cards.sql');

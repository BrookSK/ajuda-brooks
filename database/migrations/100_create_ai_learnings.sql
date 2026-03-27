CREATE TABLE IF NOT EXISTS ai_learnings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    scope ENUM('global', 'personality') NOT NULL DEFAULT 'global',
    scope_id INT NULL COMMENT 'personality_id quando scope=personality',
    content TEXT NOT NULL,
    source_conversation_id INT NULL,
    usage_count INT NOT NULL DEFAULT 0,
    last_used_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_scope_active (scope, scope_id, deleted_at),
    INDEX idx_usage (usage_count DESC),
    INDEX idx_created (created_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

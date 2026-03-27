CREATE TABLE IF NOT EXISTS ai_prompt_suggestions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    suggestion TEXT NOT NULL COMMENT 'texto sugerido para adicionar/modificar no system prompt',
    rationale TEXT NULL COMMENT 'justificativa gerada pela IA',
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    reviewed_by_admin_at TIMESTAMP NULL,
    applied_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_status (status),
    INDEX idx_created (created_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_suggestion_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT UNSIGNED NOT NULL,
    conversation_id INT UNSIGNED NOT NULL,
    user_message TEXT NOT NULL,
    assistant_reply TEXT NOT NULL,
    status ENUM('pending', 'running', 'done', 'error') NOT NULL DEFAULT 'pending',
    error_text TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    started_at TIMESTAMP NULL,
    done_at TIMESTAMP NULL,
    INDEX idx_status (status),
    INDEX idx_project (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

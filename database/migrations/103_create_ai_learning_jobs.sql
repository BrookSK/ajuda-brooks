CREATE TABLE IF NOT EXISTS ai_learning_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL,
    user_message TEXT NOT NULL,
    assistant_reply TEXT NOT NULL,
    persona_id INT NULL,
    model VARCHAR(100) NULL,
    status ENUM('pending','running','done','error') NOT NULL DEFAULT 'pending',
    error_text TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    started_at TIMESTAMP NULL,
    done_at TIMESTAMP NULL,
    INDEX idx_status_created (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

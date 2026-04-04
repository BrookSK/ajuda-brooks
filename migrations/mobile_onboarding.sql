-- Tabela de onboarding mobile
CREATE TABLE IF NOT EXISTS `user_onboarding` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `preferred_name` VARCHAR(100) NULL,
    `tool_name` VARCHAR(100) NULL DEFAULT 'Assistente',
    `personality_id` INT UNSIGNED NULL,
    `wants_projects` TINYINT(1) NOT NULL DEFAULT 0,
    `wants_documents` TINYINT(1) NOT NULL DEFAULT 0,
    `voice_enabled` TINYINT(1) NOT NULL DEFAULT 1,
    `completed_at` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_user_onboarding_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings para ElevenLabs (rodar manualmente se necessário)
INSERT IGNORE INTO `settings` (`key`, `value`) VALUES
    ('elevenlabs_api_key', ''),
    ('elevenlabs_voice_id', 'EXAVITQu4vr4xnSDxMaL');

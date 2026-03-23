CREATE TABLE partner_personalities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(120) NOT NULL,
    area VARCHAR(120) NOT NULL,
    slug VARCHAR(160) NOT NULL,
    prompt TEXT NOT NULL,
    image_path VARCHAR(255) NULL,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uq_partner_personalities_user_slug (user_id, slug),
    INDEX idx_partner_personalities_user_id (user_id),
    INDEX idx_partner_personalities_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

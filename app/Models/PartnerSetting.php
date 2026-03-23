<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class PartnerSetting
{
    public static function get(int $userId, string $key, $default = null)
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT setting_value FROM partner_settings WHERE user_id = :user_id AND setting_key = :key LIMIT 1');
        $stmt->execute(['user_id' => $userId, 'key' => $key]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['setting_value'] : $default;
    }

    public static function set(int $userId, string $key, $value): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('INSERT INTO partner_settings (user_id, setting_key, setting_value) 
            VALUES (:user_id, :key, :value) 
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()');
        $stmt->execute(['user_id' => $userId, 'key' => $key, 'value' => $value]);
    }

    public static function getAllByUserId(int $userId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT setting_key, setting_value FROM partner_settings WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    public static function delete(int $userId, string $key): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM partner_settings WHERE user_id = :user_id AND setting_key = :key');
        $stmt->execute(['user_id' => $userId, 'key' => $key]);
    }

    public static function clearAllByUserId(int $userId): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM partner_settings WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
    }
}

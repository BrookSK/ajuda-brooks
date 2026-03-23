<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class PartnerApiKey
{
    public static function findByUserId(int $userId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM partner_api_keys WHERE user_id = :user_id ORDER BY provider ASC');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function findByUserIdAndProvider(int $userId, string $provider): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM partner_api_keys WHERE user_id = :user_id AND provider = :provider LIMIT 1');
        $stmt->execute(['user_id' => $userId, 'provider' => $provider]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function getActiveKey(int $userId, string $provider): ?string
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT api_key FROM partner_api_keys WHERE user_id = :user_id AND provider = :provider AND is_active = 1 LIMIT 1');
        $stmt->execute(['user_id' => $userId, 'provider' => $provider]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['api_key'] : null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('INSERT INTO partner_api_keys (user_id, provider, api_key, model, is_active) 
            VALUES (:user_id, :provider, :api_key, :model, :is_active)');
        $stmt->execute([
            'user_id' => (int)($data['user_id'] ?? 0),
            'provider' => $data['provider'] ?? '',
            'api_key' => $data['api_key'] ?? '',
            'model' => $data['model'] ?? null,
            'is_active' => !empty($data['is_active']) ? 1 : 0,
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE partner_api_keys SET 
            api_key = :api_key, 
            model = :model, 
            is_active = :is_active,
            updated_at = NOW()
            WHERE id = :id');
        $stmt->execute([
            'id' => $id,
            'api_key' => $data['api_key'] ?? '',
            'model' => $data['model'] ?? null,
            'is_active' => !empty($data['is_active']) ? 1 : 0,
        ]);
    }

    public static function delete(int $id): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM partner_api_keys WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function toggleActive(int $id): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE partner_api_keys SET is_active = NOT is_active, updated_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function hasAnyActiveKey(int $userId): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT 1 FROM partner_api_keys WHERE user_id = :user_id AND is_active = 1 LIMIT 1');
        $stmt->execute(['user_id' => $userId]);
        return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    }
}

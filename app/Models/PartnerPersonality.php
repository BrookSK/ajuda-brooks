<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class PartnerPersonality
{
    public static function allByUserId(int $userId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM partner_personalities WHERE user_id = :user_id ORDER BY is_default DESC, name ASC');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function allActiveByUserId(int $userId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM partner_personalities WHERE user_id = :user_id AND active = 1 ORDER BY is_default DESC, name ASC');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM partner_personalities WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function findByUserIdAndSlug(int $userId, string $slug): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM partner_personalities WHERE user_id = :user_id AND slug = :slug LIMIT 1');
        $stmt->execute(['user_id' => $userId, 'slug' => $slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function findDefaultByUserId(int $userId): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM partner_personalities WHERE user_id = :user_id AND is_default = 1 AND active = 1 ORDER BY id ASC LIMIT 1');
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();

        // Se for default, remove default dos outros
        if (!empty($data['is_default'])) {
            $stmt = $pdo->prepare('UPDATE partner_personalities SET is_default = 0 WHERE user_id = :user_id');
            $stmt->execute(['user_id' => (int)($data['user_id'] ?? 0)]);
        }

        $stmt = $pdo->prepare('INSERT INTO partner_personalities (user_id, name, area, slug, prompt, image_path, is_default, active) 
            VALUES (:user_id, :name, :area, :slug, :prompt, :image_path, :is_default, :active)');
        $stmt->execute([
            'user_id' => (int)($data['user_id'] ?? 0),
            'name' => $data['name'] ?? '',
            'area' => $data['area'] ?? '',
            'slug' => $data['slug'] ?? '',
            'prompt' => $data['prompt'] ?? '',
            'image_path' => $data['image_path'] ?? null,
            'is_default' => !empty($data['is_default']) ? 1 : 0,
            'active' => !empty($data['active']) ? 1 : 0,
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $pdo = Database::getConnection();

        $personality = self::findById($id);
        if ($personality && !empty($data['is_default']) && empty($personality['is_default'])) {
            // Se estiver setando como default, remove default dos outros do mesmo usuário
            $stmt = $pdo->prepare('UPDATE partner_personalities SET is_default = 0 WHERE user_id = :user_id AND id <> :id');
            $stmt->execute(['user_id' => (int)$personality['user_id'], 'id' => $id]);
        }

        $stmt = $pdo->prepare('UPDATE partner_personalities SET 
            name = :name, 
            area = :area, 
            slug = :slug, 
            prompt = :prompt, 
            image_path = :image_path, 
            is_default = :is_default, 
            active = :active,
            updated_at = NOW()
            WHERE id = :id');
        $stmt->execute([
            'id' => $id,
            'name' => $data['name'] ?? '',
            'area' => $data['area'] ?? '',
            'slug' => $data['slug'] ?? '',
            'prompt' => $data['prompt'] ?? '',
            'image_path' => $data['image_path'] ?? null,
            'is_default' => !empty($data['is_default']) ? 1 : 0,
            'active' => !empty($data['active']) ? 1 : 0,
        ]);
    }

    public static function delete(int $id): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM partner_personalities WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function toggleActive(int $id): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE partner_personalities SET active = NOT active, updated_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function setDefault(int $id): void
    {
        $pdo = Database::getConnection();
        $personality = self::findById($id);
        if ($personality) {
            // Remove default de todos do mesmo usuário
            $stmt = $pdo->prepare('UPDATE partner_personalities SET is_default = 0 WHERE user_id = :user_id');
            $stmt->execute(['user_id' => (int)$personality['user_id']]);
            
            // Define como default
            $stmt = $pdo->prepare('UPDATE partner_personalities SET is_default = 1, updated_at = NOW() WHERE id = :id');
            $stmt->execute(['id' => $id]);
        }
    }

    public static function isSlugUnique(int $userId, string $slug, ?int $excludeId = null): bool
    {
        $pdo = Database::getConnection();
        if ($excludeId) {
            $stmt = $pdo->prepare('SELECT id FROM partner_personalities WHERE user_id = :user_id AND slug = :slug AND id <> :id LIMIT 1');
            $stmt->execute(['user_id' => $userId, 'slug' => $slug, 'id' => $excludeId]);
        } else {
            $stmt = $pdo->prepare('SELECT id FROM partner_personalities WHERE user_id = :user_id AND slug = :slug LIMIT 1');
            $stmt->execute(['user_id' => $userId, 'slug' => $slug]);
        }
        return !(bool)$stmt->fetch(PDO::FETCH_ASSOC);
    }
}

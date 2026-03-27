<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class AiPromptSuggestion
{
    public static function allPending(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query('SELECT * FROM ai_prompt_suggestions
            WHERE status = \'pending\' AND deleted_at IS NULL
            ORDER BY created_at DESC');
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public static function allForAdmin(int $limit = 100, int $offset = 0): array
    {
        $limit = max(1, min(500, $limit));
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM ai_prompt_suggestions
            WHERE deleted_at IS NULL
            ORDER BY
                CASE status WHEN \'pending\' THEN 0 WHEN \'approved\' THEN 1 ELSE 2 END,
                created_at DESC
            LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function countPending(): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query('SELECT COUNT(*) FROM ai_prompt_suggestions WHERE status = \'pending\' AND deleted_at IS NULL');
        return $stmt ? (int)$stmt->fetchColumn() : 0;
    }

    public static function create(string $suggestion, string $rationale = ''): int
    {
        $suggestion = trim(str_replace(["\r\n", "\r"], "\n", $suggestion));
        if ($suggestion === '') {
            return 0;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('INSERT INTO ai_prompt_suggestions (suggestion, rationale) VALUES (:s, :r)');
        $stmt->execute([
            's' => $suggestion,
            'r' => $rationale !== '' ? $rationale : null,
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function approve(int $id): void
    {
        if ($id <= 0) {
            return;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE ai_prompt_suggestions
            SET status = \'approved\', reviewed_by_admin_at = NOW()
            WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute(['id' => $id]);
    }

    public static function applyApproved(int $id): void
    {
        if ($id <= 0) {
            return;
        }
        $row = self::findById($id);
        if (!$row || ($row['status'] ?? '') !== 'approved') {
            return;
        }

        $existing = Setting::get('tuquinha_system_prompt_extra', '');
        $addition = trim((string)($row['suggestion'] ?? ''));
        if ($addition === '') {
            return;
        }

        $updated = $existing !== '' ? ($existing . "\n\n" . $addition) : $addition;
        Setting::set('tuquinha_system_prompt_extra', $updated);

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE ai_prompt_suggestions SET applied_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function reject(int $id): void
    {
        if ($id <= 0) {
            return;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE ai_prompt_suggestions
            SET status = \'rejected\', reviewed_by_admin_at = NOW()
            WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute(['id' => $id]);
    }

    public static function softDelete(int $id): void
    {
        if ($id <= 0) {
            return;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE ai_prompt_suggestions SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute(['id' => $id]);
    }

    public static function findById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM ai_prompt_suggestions WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function existsSimilar(string $suggestion): bool
    {
        $suggestion = trim($suggestion);
        if ($suggestion === '') {
            return false;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT 1 FROM ai_prompt_suggestions
            WHERE deleted_at IS NULL AND suggestion = :s AND status = \'pending\' LIMIT 1');
        $stmt->execute(['s' => $suggestion]);
        return (bool)$stmt->fetchColumn();
    }
}

<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class ProjectSuggestionJob
{
    public static function enqueue(int $projectId, int $conversationId, string $userMessage, string $assistantReply): int
    {
        if ($projectId <= 0 || $conversationId <= 0 || trim($userMessage) === '' || trim($assistantReply) === '') {
            return 0;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            'INSERT INTO project_suggestion_jobs (project_id, conversation_id, user_message, assistant_reply, status)
             VALUES (:pid, :cid, :um, :ar, \'pending\')'
        );
        $stmt->execute([
            'pid' => $projectId,
            'cid' => $conversationId,
            'um'  => mb_substr($userMessage, 0, 2000, 'UTF-8'),
            'ar'  => mb_substr($assistantReply, 0, 4000, 'UTF-8'),
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function fetchPendingBatch(int $limit = 5): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query(
            'SELECT * FROM project_suggestion_jobs WHERE status = \'pending\' ORDER BY created_at ASC LIMIT ' . (int)$limit
        );
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public static function markRunning(int $id): void
    {
        $pdo = Database::getConnection();
        $pdo->prepare('UPDATE project_suggestion_jobs SET status = \'running\', started_at = NOW() WHERE id = :id')
            ->execute(['id' => $id]);
    }

    public static function markDone(int $id): void
    {
        $pdo = Database::getConnection();
        $pdo->prepare('UPDATE project_suggestion_jobs SET status = \'done\', done_at = NOW() WHERE id = :id')
            ->execute(['id' => $id]);
    }

    public static function markError(int $id, string $error): void
    {
        $pdo = Database::getConnection();
        $pdo->prepare('UPDATE project_suggestion_jobs SET status = \'error\', done_at = NOW(), error_text = :e WHERE id = :id')
            ->execute(['id' => $id, 'e' => mb_substr($error, 0, 2000, 'UTF-8')]);
    }

    public static function countPending(): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM project_suggestion_jobs WHERE status IN ('pending','running')");
        return $stmt ? (int)$stmt->fetchColumn() : 0;
    }
}

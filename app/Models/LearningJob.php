<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class LearningJob
{
    public static function enqueue(
        int $conversationId,
        string $userMessage,
        string $assistantReply,
        ?int $personaId,
        ?string $model
    ): int {
        if ($conversationId <= 0 || trim($userMessage) === '' || trim($assistantReply) === '') {
            return 0;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            'INSERT INTO ai_learning_jobs
                (conversation_id, user_message, assistant_reply, persona_id, model, status)
             VALUES (:conv, :um, :ar, :pid, :model, \'pending\')'
        );
        $stmt->execute([
            'conv'  => $conversationId,
            'um'    => mb_substr($userMessage, 0, 4000, 'UTF-8'),
            'ar'    => mb_substr($assistantReply, 0, 8000, 'UTF-8'),
            'pid'   => ($personaId && $personaId > 0) ? $personaId : null,
            'model' => $model ? mb_substr($model, 0, 100, 'UTF-8') : null,
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function fetchPendingBatch(int $limit = 10): array
    {
        $limit = max(1, min(50, $limit));
        $pdo = Database::getConnection();
        $stmt = $pdo->query(
            'SELECT * FROM ai_learning_jobs
             WHERE status = \'pending\'
             ORDER BY created_at ASC
             LIMIT ' . (int)$limit
        );
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public static function markRunning(int $id): void
    {
        $pdo = Database::getConnection();
        $pdo->prepare(
            'UPDATE ai_learning_jobs SET status = \'running\', started_at = NOW() WHERE id = :id LIMIT 1'
        )->execute(['id' => $id]);
    }

    public static function markDone(int $id): void
    {
        $pdo = Database::getConnection();
        $pdo->prepare(
            'UPDATE ai_learning_jobs SET status = \'done\', done_at = NOW() WHERE id = :id LIMIT 1'
        )->execute(['id' => $id]);
    }

    public static function markError(int $id, string $error): void
    {
        $pdo = Database::getConnection();
        $pdo->prepare(
            'UPDATE ai_learning_jobs SET status = \'error\', done_at = NOW(), error_text = :e WHERE id = :id LIMIT 1'
        )->execute(['id' => $id, 'e' => mb_substr($error, 0, 2000, 'UTF-8')]);
    }

    public static function countPending(): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM ai_learning_jobs WHERE status IN ('pending','running')");
        return $stmt ? (int)$stmt->fetchColumn() : 0;
    }

    public static function resetStuck(int $olderThanMinutes = 15): void
    {
        $pdo = Database::getConnection();
        $pdo->prepare(
            "UPDATE ai_learning_jobs
             SET status = 'pending', started_at = NULL
             WHERE status = 'running' AND started_at < NOW() - INTERVAL :m MINUTE"
        )->execute(['m' => $olderThanMinutes]);
    }
}

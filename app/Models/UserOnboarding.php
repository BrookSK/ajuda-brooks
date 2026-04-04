<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class UserOnboarding
{
    /**
     * Retorna o onboarding do usuário ou null se não existir.
     */
    public static function findByUserId(int $userId): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM user_onboarding WHERE user_id = :uid LIMIT 1');
        $stmt->execute(['uid' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Verifica se o usuário já completou o onboarding mobile.
     */
    public static function isComplete(int $userId): bool
    {
        $row = self::findByUserId($userId);
        return $row && !empty($row['completed_at']);
    }

    /**
     * Cria ou atualiza o onboarding do usuário.
     */
    public static function upsert(int $userId, array $data): void
    {
        $pdo = Database::getConnection();
        $existing = self::findByUserId($userId);

        $fields = [
            'preferred_name'   => $data['preferred_name'] ?? null,
            'tool_name'        => $data['tool_name'] ?? null,
            'personality_id'   => isset($data['personality_id']) ? (int)$data['personality_id'] : null,
            'wants_projects'   => isset($data['wants_projects']) ? (int)$data['wants_projects'] : 0,
            'wants_documents'  => isset($data['wants_documents']) ? (int)$data['wants_documents'] : 0,
            'voice_enabled'    => isset($data['voice_enabled']) ? (int)$data['voice_enabled'] : 1,
            'completed_at'     => $data['completed_at'] ?? null,
        ];

        if ($existing) {
            $sets = [];
            $params = ['uid' => $userId];
            foreach ($fields as $col => $val) {
                if ($val !== null || $col === 'completed_at') {
                    $sets[] = "`{$col}` = :{$col}";
                    $params[$col] = $val;
                }
            }
            if ($sets) {
                $sql = 'UPDATE user_onboarding SET ' . implode(', ', $sets) . ' WHERE user_id = :uid';
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
            }
        } else {
            $fields['user_id'] = $userId;
            $cols = array_keys($fields);
            $placeholders = array_map(fn($c) => ":{$c}", $cols);
            $sql = 'INSERT INTO user_onboarding (' . implode(', ', $cols) . ') VALUES (' . implode(', ', $placeholders) . ')';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($fields);
        }
    }

    /**
     * Marca o onboarding como completo.
     */
    public static function markComplete(int $userId): void
    {
        self::upsert($userId, ['completed_at' => date('Y-m-d H:i:s')]);
    }
}

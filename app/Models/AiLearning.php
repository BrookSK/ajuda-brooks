<?php

namespace App\Models;

use App\Core\Database;
use App\Services\EmbeddingService;
use PDO;

class AiLearning
{
    public static function allGlobal(int $limit = 80): array
    {
        $limit = max(1, min(500, $limit));
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM ai_learnings
            WHERE scope = \'global\' AND deleted_at IS NULL
            ORDER BY usage_count DESC, created_at DESC
            LIMIT ' . (int)$limit);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function allForPersonality(int $personalityId, int $limit = 100): array
    {
        if ($personalityId <= 0) {
            return [];
        }
        $limit = max(1, min(500, $limit));
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM ai_learnings
            WHERE scope = \'personality\' AND scope_id = :sid AND deleted_at IS NULL
            ORDER BY usage_count DESC, created_at DESC
            LIMIT ' . (int)$limit);
        $stmt->execute(['sid' => $personalityId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Recupera aprendizados relevantes para uma mensagem usando keywords e categorias.
     * Retorna TODOS os registros das categorias/keywords encontradas — sem limite arbitrário.
     */
    public static function allRelevantForMessage(string $message, ?int $personalityId = null): array
    {
        $message = trim($message);
        if ($message === '') {
            return [];
        }

        // Extrai keywords da mensagem (palavras com 4+ chars, sem stopwords)
        $stopwords = ['para', 'como', 'qual', 'quais', 'que', 'este', 'esta', 'isso', 'uma', 'fazer', 'pode',
                      'mais', 'muito', 'quando', 'onde', 'sobre', 'tenho', 'quero', 'preciso', 'favor',
                      'the', 'and', 'for', 'that', 'with', 'have', 'from', 'this', 'your'];
        $words = preg_split('/[\s\.,;:!?\/\(\)\[\]\"\']+/', mb_strtolower($message, 'UTF-8'));
        $keywords = [];
        foreach ((array)$words as $w) {
            $w = trim((string)$w);
            if (mb_strlen($w, 'UTF-8') >= 4 && !in_array($w, $stopwords, true)) {
                $keywords[] = $w;
            }
        }
        $keywords = array_values(array_unique(array_slice($keywords, 0, 20)));

        if (empty($keywords)) {
            return self::allGlobal(120);
        }

        // I2 — Tenta busca semântica por embedding (RAG real)
        $messageVector = EmbeddingService::embed($message);
        if ($messageVector !== null) {
            return self::allRelevantByEmbedding($messageVector, $personalityId);
        }

        $pdo = Database::getConnection();

        // Fallback: busca por keywords LIKE
        $conditions = [];
        $params = [];
        foreach ($keywords as $i => $kw) {
            $conditions[] = '(content LIKE :kw' . $i . ' OR category LIKE :kw' . $i . ')';
            $params['kw' . $i] = '%' . $kw . '%';
        }

        $scopeConditions = ['scope = \'global\''];
        if ($personalityId && $personalityId > 0) {
            $scopeConditions[] = '(scope = \'personality\' AND scope_id = ' . (int)$personalityId . ')';
        }
        $scopeWhere   = '(' . implode(' OR ', $scopeConditions) . ')';
        $keywordWhere = '(' . implode(' OR ', $conditions) . ')';

        $sql = 'SELECT * FROM ai_learnings
                WHERE deleted_at IS NULL AND ' . $scopeWhere . ' AND ' . $keywordWhere . '
                ORDER BY usage_count DESC, quality_score DESC, created_at DESC
                LIMIT 300';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $relevant = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        if (count($relevant) < 20) {
            $existingIds = array_column($relevant, 'id');
            $fallback = self::allGlobal(60);
            foreach ($fallback as $lr) {
                if (!in_array($lr['id'], $existingIds, true)) {
                    $relevant[] = $lr;
                }
            }
        }

        return $relevant;
    }

    /**
     * I2 — Busca por similaridade coseno. Retorna top-100 mais relevantes.
     */
    public static function allRelevantByEmbedding(array $queryVector, ?int $personalityId = null): array
    {
        $pdo = Database::getConnection();

        $scopeConditions = ["scope = 'global'"];
        if ($personalityId && $personalityId > 0) {
            $scopeConditions[] = '(scope = \'personality\' AND scope_id = ' . (int)$personalityId . ')';
        }
        $scopeWhere = '(' . implode(' OR ', $scopeConditions) . ')';

        // Carrega apenas registros com embedding para calcular cosine similarity
        $stmt = $pdo->prepare(
            'SELECT id, scope, scope_id, content, category, usage_count, quality_score,
                    is_consolidated, created_at, last_used_at, embedding_vector
             FROM ai_learnings
             WHERE deleted_at IS NULL AND ' . $scopeWhere . ' AND embedding_vector IS NOT NULL
             ORDER BY usage_count DESC, quality_score DESC
             LIMIT 2000'
        );
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $scored = [];
        foreach ($rows as $row) {
            $ev = EmbeddingService::deserializeVector((string)($row['embedding_vector'] ?? ''));
            if ($ev === null) {
                continue;
            }
            $sim = EmbeddingService::cosineSimilarity($queryVector, $ev);
            if ($sim >= 0.30) { // só inclui se minimamente relevante
                $row['_similarity'] = $sim;
                $scored[] = $row;
            }
        }

        // Ordena por similaridade desc
        usort($scored, fn($a, $b) => $b['_similarity'] <=> $a['_similarity']);
        $relevant = array_slice($scored, 0, 120);

        // Remove a chave interna antes de retornar
        foreach ($relevant as &$r) {
            unset($r['embedding_vector'], $r['_similarity']);
        }
        unset($r);

        // Complementa com itens sem embedding se poucos resultados
        if (count($relevant) < 15) {
            $existingIds = array_column($relevant, 'id');
            $fallback = self::allGlobal(40);
            foreach ($fallback as $lr) {
                if (!in_array($lr['id'], $existingIds, true)) {
                    $relevant[] = $lr;
                }
            }
        }

        return $relevant;
    }

    public static function allByCategory(string $category, int $limit = 200): array
    {
        $category = trim($category);
        if ($category === '') {
            return [];
        }
        $limit = max(1, min(1000, $limit));
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM ai_learnings
            WHERE deleted_at IS NULL AND category = :cat
            ORDER BY usage_count DESC, created_at DESC
            LIMIT ' . (int)$limit);
        $stmt->execute(['cat' => $category]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function distinctCategories(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query('SELECT category, COUNT(*) as total FROM ai_learnings
            WHERE deleted_at IS NULL AND category IS NOT NULL AND category != \'\'
            GROUP BY category ORDER BY total DESC');
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public static function allForAdmin(int $limit = 100, int $offset = 0, string $category = ''): array
    {
        $limit = max(1, min(500, $limit));
        $pdo = Database::getConnection();
        $catWhere = '';
        $params   = [];
        if ($category !== '') {
            $catWhere  = ' AND al.category = :cat';
            $params['cat'] = $category;
        }
        $stmt = $pdo->prepare('SELECT al.*, p.name AS personality_name
            FROM ai_learnings al
            LEFT JOIN personalities p ON p.id = al.scope_id AND al.scope = \'personality\'
            WHERE al.deleted_at IS NULL' . $catWhere . '
            ORDER BY al.quality_score DESC, al.usage_count DESC, al.created_at DESC
            LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function countActive(string $category = ''): int
    {
        $pdo = Database::getConnection();
        if ($category !== '') {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM ai_learnings WHERE deleted_at IS NULL AND category = :cat');
            $stmt->execute(['cat' => $category]);
            return $stmt ? (int)$stmt->fetchColumn() : 0;
        }
        $stmt = $pdo->query('SELECT COUNT(*) FROM ai_learnings WHERE deleted_at IS NULL');
        return $stmt ? (int)$stmt->fetchColumn() : 0;
    }

    public static function createConsolidated(string $scope, ?int $scopeId, string $content, string $category = ''): int
    {
        $content = trim(str_replace(["\r\n", "\r"], "\n", $content));
        if ($content === '') {
            return 0;
        }
        $category = trim(mb_strtolower(str_replace([' ', '-'], '_', $category), 'UTF-8'));
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            'INSERT INTO ai_learnings (scope, scope_id, content, category, is_consolidated, quality_score)
             VALUES (:scope, :scope_id, :content, :category, 1, 9)'
        );
        $stmt->execute([
            'scope'    => $scope,
            'scope_id' => ($scope === 'global') ? null : $scopeId,
            'content'  => mb_substr($content, 0, 200, 'UTF-8'),
            'category' => $category !== '' ? $category : null,
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function create(string $scope, ?int $scopeId, string $content, ?int $sourceConversationId, string $category = '', string $type = 'fact'): int
    {
        $content = trim(str_replace(["\r\n", "\r"], "\n", $content));
        $category = trim(mb_strtolower(str_replace([' ', '-'], '_', $category), 'UTF-8'));
        if (mb_strlen($category, 'UTF-8') > 80) {
            $category = mb_substr($category, 0, 80, 'UTF-8');
        }
        if (!in_array($type, ['fact', 'experience', 'warning'], true)) {
            $type = 'fact';
        }
        if ($content === '' || !in_array($scope, ['global', 'personality'], true)) {
            return 0;
        }
        if ($scope !== 'global' && (!is_int($scopeId) || $scopeId <= 0)) {
            return 0;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('INSERT INTO ai_learnings (scope, scope_id, content, source_conversation_id, category, learning_type)
            VALUES (:scope, :scope_id, :content, :source_conv, :category, :ltype)');
        $stmt->execute([
            'scope'      => $scope,
            'scope_id'   => ($scope === 'global') ? null : $scopeId,
            'content'    => $content,
            'source_conv'=> ($sourceConversationId && $sourceConversationId > 0) ? $sourceConversationId : null,
            'category'   => $category !== '' ? $category : null,
            'ltype'      => $type,
        ]);
        return (int)$pdo->lastInsertId();
    }

    /**
     * I3 — Deduplicação semântica via cosine similarity.
     * Se não há embeddings disponíveis, retorna false (deixa o fallback por texto exato).
     */
    public static function existsSimilarSemantic(string $scope, ?int $scopeId, array $vector, float $threshold = 0.92): bool
    {
        $pdo = Database::getConnection();
        $params = ['scope' => $scope];
        $scopeWhere = 'scope = :scope';
        if ($scope === 'personality' && $scopeId !== null) {
            $scopeWhere .= ' AND scope_id = :sid';
            $params['sid'] = $scopeId;
        }

        $stmt = $pdo->prepare(
            'SELECT id, embedding_vector FROM ai_learnings
             WHERE deleted_at IS NULL AND ' . $scopeWhere . '
             AND embedding_vector IS NOT NULL
             ORDER BY created_at DESC LIMIT 500'
        );
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        foreach ($rows as $row) {
            $ev = EmbeddingService::deserializeVector((string)($row['embedding_vector'] ?? ''));
            if ($ev === null) {
                continue;
            }
            $sim = EmbeddingService::cosineSimilarity($vector, $ev);
            if ($sim >= $threshold) {
                return true;
            }
        }
        return false;
    }

    public static function existsSimilar(string $scope, ?int $scopeId, string $content): bool
    {
        $content = trim(str_replace(["\r\n", "\r"], "\n", $content));
        if ($content === '') {
            return false;
        }
        $pdo = Database::getConnection();
        if ($scope === 'global') {
            $stmt = $pdo->prepare('SELECT 1 FROM ai_learnings
                WHERE scope = \'global\' AND deleted_at IS NULL AND content = :content LIMIT 1');
            $stmt->execute(['content' => $content]);
        } else {
            $stmt = $pdo->prepare('SELECT 1 FROM ai_learnings
                WHERE scope = :scope AND scope_id = :sid AND deleted_at IS NULL AND content = :content LIMIT 1');
            $stmt->execute(['scope' => $scope, 'sid' => $scopeId, 'content' => $content]);
        }
        return (bool)$stmt->fetchColumn();
    }

    public static function markUsed(array $ids): void
    {
        $ids = array_filter(array_map('intval', $ids));
        if (empty($ids)) {
            return;
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE ai_learnings
            SET usage_count = usage_count + 1, last_used_at = NOW()
            WHERE id IN (' . $placeholders . ') AND deleted_at IS NULL');
        $stmt->execute(array_values($ids));
    }

    public static function updateContent(int $id, string $content): void
    {
        $content = trim(str_replace(["\r\n", "\r"], "\n", $content));
        if ($id <= 0 || $content === '') {
            return;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE ai_learnings
            SET content = :content, updated_at = NOW()
            WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute(['content' => $content, 'id' => $id]);
    }

    public static function softDelete(int $id): void
    {
        if ($id <= 0) {
            return;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE ai_learnings SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute(['id' => $id]);
    }

    public static function pruneIfNeeded(int $maxCount): void
    {
        if ($maxCount < 10) {
            return;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->query('SELECT COUNT(*) FROM ai_learnings WHERE deleted_at IS NULL');
        $current = $stmt ? (int)$stmt->fetchColumn() : 0;

        if ($current <= $maxCount) {
            return;
        }

        // Prune to 80% of maxCount to avoid pruning on every single insert
        $target = max(10, (int)floor($maxCount * 0.8));
        $toDelete = $current - $target;
        if ($toDelete <= 0) {
            return;
        }

        // Delete least used (usage_count ASC), then oldest (created_at ASC)
        $stmt = $pdo->prepare(
            'UPDATE ai_learnings SET deleted_at = NOW()
             WHERE deleted_at IS NULL
             ORDER BY usage_count ASC, created_at ASC
             LIMIT ' . (int)$toDelete
        );
        $stmt->execute();
    }

    public static function findById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM ai_learnings WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}

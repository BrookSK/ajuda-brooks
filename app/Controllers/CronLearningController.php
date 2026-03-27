<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AiLearning;
use App\Models\AiPromptSuggestion;
use App\Models\LearningJob;
use App\Models\Message;
use App\Models\Conversation;
use App\Models\Setting;
use App\Models\TuquinhaEngine;
use App\Services\EmbeddingService;

class CronLearningController extends Controller
{
    private function ensureToken(): bool
    {
        $expected = trim((string)Setting::get('news_cron_token', ''));
        $provided  = trim((string)($_GET['token'] ?? $_POST['token'] ?? ''));
        if ($expected === '' || $provided === '' || !hash_equals($expected, $provided)) {
            // Admin session also accepted
            if (empty($_SESSION['is_admin'])) {
                http_response_code(403);
                echo json_encode(['error' => 'forbidden']);
                return false;
            }
        }
        return true;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // I1 — Processa fila de extração assíncrona de aprendizados
    // ─────────────────────────────────────────────────────────────────────────
    public function process(): void
    {
        if (!$this->ensureToken()) {
            return;
        }

        // Sem limite de tempo: o processo roda em background e pode demorar com Claude
        @set_time_limit(0);

        LearningJob::resetStuck(15);

        $batchSize = max(1, min(20, (int)($_GET['batch'] ?? 5)));
        $jobs = LearningJob::fetchPendingBatch($batchSize);

        if (empty($jobs)) {
            echo json_encode(['ok' => true, 'processed' => 0, 'message' => 'no_pending_jobs']);
            return;
        }

        $customFocus   = trim((string)Setting::get('ai_learning_prompt', ''));
        $focusLine     = $customFocus !== ''
            ? "Foco personalizado: " . $customFocus . "\n"
            : "Foco: fatos, conceitos, marcas, técnicas, preferências de resposta, padrões de uso reutilizáveis por qualquer usuário.\n";
        $processed     = 0;
        $savedTotal    = 0;

        // Modelo rápido para extração — não usa o modelo do chat (pode ser Claude 4.x lento)
        $extractionModel = 'claude-3-5-sonnet-latest';

        foreach ($jobs as $job) {
            $jobId = (int)($job['id'] ?? 0);
            if ($jobId <= 0) {
                continue;
            }

            LearningJob::markRunning($jobId);

            try {
                $conversationId = (int)($job['conversation_id'] ?? 0);
                $personaId      = (int)($job['persona_id'] ?? 0);
                $userMsg        = (string)($job['user_message'] ?? '');
                $assistantReply = (string)($job['assistant_reply'] ?? '');

                // ── Extração via Claude com quality score, category e type ───
                $instruction = "Analise esta troca e extraia conhecimentos para uma biblioteca de aprendizado permanente.\n"
                    . $focusLine
                    . "Retorne APENAS JSON válido:\n"
                    . "{\"items\":[{\"content\":\"...\",\"scope\":\"global\",\"category\":\"...\",\"quality\":8,\"type\":\"fact\"}]}\n"
                    . "Campos obrigatórios:\n"
                    . "- content: até 200 chars, sem nomes, emails, dados pessoais ou identificadores do usuário\n"
                    . "- scope: 'global' (qualquer usuário) ou 'personality' (personalidade ativa)\n"
                    . "- category: 1-3 palavras minúsculas (ex: costura, financas_pessoais, erros_frequentes)\n"
                    . "- quality: int 1-10 (só inclua itens com quality >= 7)\n"
                    . "- type: um dos três valores abaixo:\n"
                    . "    'fact'       → conhecimento, conceito, marca, técnica ou informação geral reutilizável\n"
                    . "    'experience' → padrão de problema, erro ou dificuldade que usuário enfrentou + como foi resolvido (sem PII)\n"
                    . "                   Ex: 'Usuários frequentemente confundem X com Y ao fazer Z; a solução é...'\n"
                    . "    'warning'    → situação de risco, armadilha recorrente ou alerta que outros usuários devem receber proativamente\n"
                    . "                   Ex: 'Ao fazer X, é comum o erro Y que causa Z; recomenda-se verificar W antes'\n"
                    . "Regras: NUNCA inclua nome, CPF, email, telefone ou qualquer dado que identifique o usuário.\n"
                    . "Prefira 'experience' e 'warning' para problemas relatados — são mais valiosos que fatos simples.\n"
                    . "Se não houver nada com quality >= 7, retorne {\"items\":[]}.\n\n"
                    . "MENSAGEM DO USUÁRIO:\n" . mb_substr($userMsg, 0, 600, 'UTF-8') . "\n\n"
                    . "RESPOSTA DA IA:\n" . mb_substr($assistantReply, 0, 800, 'UTF-8');

                $engine  = new TuquinhaEngine();
                $result  = $engine->generateResponseWithContext(
                    [['role' => 'user', 'content' => $instruction]],
                    $extractionModel,
                    null, null, null, null
                );
                $text = is_array($result)
                    ? trim((string)($result['content'] ?? ''))
                    : trim((string)$result);

                $saved = 0;
                if ($text !== '' && $text[0] === '{') {
                    $json = json_decode($text, true);
                    if (is_array($json) && isset($json['items']) && is_array($json['items'])) {
                        foreach ($json['items'] as $li) {
                            if (!is_array($li)) {
                                continue;
                            }
                            $content  = trim((string)($li['content'] ?? ''));
                            $scope    = trim((string)($li['scope'] ?? 'global'));
                            $category = trim((string)($li['category'] ?? ''));
                            $quality  = (int)($li['quality'] ?? 0);
                            $ltype    = trim((string)($li['type'] ?? 'fact'));
                            if (!in_array($ltype, ['fact', 'experience', 'warning'], true)) {
                                $ltype = 'fact';
                            }

                            if ($content === '' || $quality < 7) {
                                continue;
                            }
                            if (mb_strlen($content, 'UTF-8') > 200) {
                                $content = mb_substr($content, 0, 200, 'UTF-8');
                            }
                            if (!in_array($scope, ['global', 'personality'], true)) {
                                $scope = 'global';
                            }
                            $scopeId = ($scope === 'personality' && $personaId > 0) ? $personaId : null;
                            if ($scope === 'personality' && $personaId <= 0) {
                                $scope = 'global';
                            }

                            // I3 — Deduplicação semântica via embeddings (fallback: exact)
                            if (!$this->isDuplicate($scope, $scopeId, $content)) {
                                $newId = AiLearning::create($scope, $scopeId, $content, $conversationId, $category, $ltype);
                                if ($newId > 0) {
                                    // I2 — Gera embedding e salva
                                    $this->attachEmbedding($newId, $content, $quality);
                                    $saved++;
                                    $savedTotal++;
                                }
                            }
                        }
                    }
                }

                // Gera sugestões de prompt periodicamente (a cada N aprendizados)
                $this->maybeGeneratePromptSuggestions($model, $saved);

                LearningJob::markDone($jobId);
                $processed++;
            } catch (\Throwable $e) {
                LearningJob::markError($jobId, $e->getMessage());
                error_log('[CronLearning] Job ' . $jobId . ' falhou: ' . $e->getMessage());
            }
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok'        => true,
            'processed' => $processed,
            'saved'     => $savedTotal,
            'remaining' => LearningJob::countPending(),
        ], JSON_UNESCAPED_UNICODE);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // I4 — Consolidação: agrupa aprendizados similares por categoria via Claude
    // ─────────────────────────────────────────────────────────────────────────
    public function consolidate(): void
    {
        if (!$this->ensureToken()) {
            return;
        }

        $categories = AiLearning::distinctCategories();
        $consolidated = 0;

        foreach (array_slice($categories, 0, 20) as $catRow) {
            $category = (string)($catRow['category'] ?? '');
            $count    = (int)($catRow['total'] ?? 0);

            if ($category === '' || $count < 5) {
                continue;
            }

            $items = AiLearning::allByCategory($category, 100);
            if (count($items) < 5) {
                continue;
            }

            // Só consolida se não há entrada consolidada recente (última semana)
            $hasRecent = array_filter($items, function ($i) {
                return !empty($i['is_consolidated']) && strtotime((string)($i['updated_at'] ?? $i['created_at'] ?? '')) > time() - 604800;
            });
            if (!empty($hasRecent)) {
                continue;
            }

            $itemText = implode("\n", array_map(fn($i) => '- ' . $i['content'], $items));
            $instruction = "Consolide os seguintes aprendizados da categoria '{$category}' em 3-5 itens sintetizados e enriquecidos.\n"
                . "Retorne APENAS JSON: {\"consolidated\":[{\"content\":\"...\",\"category\":\"{$category}\"}]}\n"
                . "Regras: cada item até 200 chars; preserve o máximo de informação; elimine redundâncias.\n\n"
                . "APRENDIZADOS:\n" . mb_substr($itemText, 0, 4000, 'UTF-8');

            try {
                $engine = new TuquinhaEngine();
                $result = $engine->generateResponseWithContext(
                    [['role' => 'user', 'content' => $instruction]],
                    null, null, null, null, null
                );
                $text = is_array($result)
                    ? trim((string)($result['content'] ?? ''))
                    : trim((string)$result);

                if ($text !== '' && $text[0] === '{') {
                    $json = json_decode($text, true);
                    if (is_array($json) && isset($json['consolidated']) && is_array($json['consolidated'])) {
                        foreach ($json['consolidated'] as $ci) {
                            $content = trim((string)($ci['content'] ?? ''));
                            if ($content === '') {
                                continue;
                            }
                            if (!AiLearning::existsSimilar('global', null, $content)) {
                                $newId = AiLearning::createConsolidated('global', null, $content, $category);
                                if ($newId > 0) {
                                    $this->attachEmbedding($newId, $content, 9);
                                    $consolidated++;
                                }
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                error_log('[CronLearning] Consolidação falhou para ' . $category . ': ' . $e->getMessage());
            }
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => true, 'consolidated' => $consolidated], JSON_UNESCAPED_UNICODE);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // I5 — Mineração de conversas históricas em batch
    // ─────────────────────────────────────────────────────────────────────────
    public function mineHistory(): void
    {
        if (!$this->ensureToken()) {
            return;
        }

        $daysBack   = max(1, min(365, (int)($_GET['days'] ?? 30)));
        $batchLimit = max(1, min(50, (int)($_GET['batch'] ?? 10)));

        $pdo = \App\Core\Database::getConnection();

        // Busca conversas com mensagens suficientes nos últimos N dias, não mineradas ainda
        $stmt = $pdo->prepare(
            "SELECT c.id, c.persona_id
             FROM conversations c
             INNER JOIN messages m ON m.conversation_id = c.id AND m.role = 'assistant'
             WHERE c.created_at >= NOW() - INTERVAL :days DAY
               AND c.id NOT IN (
                   SELECT DISTINCT source_conversation_id
                   FROM ai_learnings
                   WHERE source_conversation_id IS NOT NULL
               )
             GROUP BY c.id
             HAVING COUNT(m.id) >= 2
             ORDER BY c.created_at DESC
             LIMIT :lim"
        );
        $stmt->execute(['days' => $daysBack, 'lim' => $batchLimit]);
        $conversations = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $enqueued = 0;
        foreach ($conversations as $conv) {
            $convId   = (int)($conv['id'] ?? 0);
            $personaId = (int)($conv['persona_id'] ?? 0);
            if ($convId <= 0) {
                continue;
            }

            // Busca pares user/assistant da conversa
            $mStmt = $pdo->prepare(
                "SELECT role, content FROM messages
                 WHERE conversation_id = :cid AND deleted_at IS NULL
                 ORDER BY created_at ASC LIMIT 20"
            );
            $mStmt->execute(['cid' => $convId]);
            $messages = $mStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            // Agrupa em pares user→assistant
            $userMsg   = '';
            $assistMsg = '';
            foreach ($messages as $m) {
                if ($m['role'] === 'user') {
                    $userMsg = (string)($m['content'] ?? '');
                } elseif ($m['role'] === 'assistant' && $userMsg !== '') {
                    $assistMsg = (string)($m['content'] ?? '');
                    if (mb_strlen($userMsg, 'UTF-8') >= 20 && mb_strlen($assistMsg, 'UTF-8') >= 120) {
                        LearningJob::enqueue($convId, $userMsg, $assistMsg, $personaId ?: null, null);
                        $enqueued++;
                    }
                    $userMsg = '';
                }
            }
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok'           => true,
            'conversations'=> count($conversations),
            'enqueued'     => $enqueued,
        ], JSON_UNESCAPED_UNICODE);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Gera embeddings para aprendizados que ainda não têm (backfill)
    // ─────────────────────────────────────────────────────────────────────────
    public function embedBackfill(): void
    {
        if (!$this->ensureToken()) {
            return;
        }

        $limit = max(1, min(100, (int)($_GET['batch'] ?? 20)));
        $pdo   = \App\Core\Database::getConnection();
        $stmt  = $pdo->query(
            'SELECT id, content FROM ai_learnings
             WHERE deleted_at IS NULL AND embedding_vector IS NULL
             ORDER BY id ASC LIMIT ' . $limit
        );
        $rows = $stmt ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : [];

        $done = 0;
        foreach ($rows as $row) {
            $id = (int)($row['id'] ?? 0);
            if ($id <= 0 || trim((string)($row['content'] ?? '')) === '') {
                continue;
            }
            $this->attachEmbedding($id, (string)$row['content'], 8);
            $done++;
            usleep(50000); // 50ms para não throttlear a API
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok'        => true,
            'processed' => $done,
            'remaining' => count($rows) - $done,
        ], JSON_UNESCAPED_UNICODE);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers privados
    // ─────────────────────────────────────────────────────────────────────────

    private function attachEmbedding(int $learningId, string $content, int $quality): void
    {
        $vector = EmbeddingService::embed($content);
        if ($vector === null) {
            return;
        }
        $pdo = \App\Core\Database::getConnection();
        $pdo->prepare(
            'UPDATE ai_learnings SET embedding_vector = :ev, quality_score = :qs WHERE id = :id LIMIT 1'
        )->execute([
            'ev'  => EmbeddingService::serializeVector($vector),
            'qs'  => min(10, max(1, $quality)),
            'id'  => $learningId,
        ]);
    }

    private function isDuplicate(string $scope, ?int $scopeId, string $content): bool
    {
        // Tenta deduplicação semântica via embedding
        $vector = EmbeddingService::embed($content);
        if ($vector !== null) {
            return AiLearning::existsSimilarSemantic($scope, $scopeId, $vector, 0.92);
        }
        // Fallback: deduplicação por texto exato
        return AiLearning::existsSimilar($scope, $scopeId, $content);
    }

    private function maybeGeneratePromptSuggestions(string $model, int $newlySaved): void
    {
        if ($newlySaved === 0) {
            return;
        }
        $total    = AiLearning::countActive();
        $interval = max(10, (int)Setting::get('ai_suggestion_interval', '50'));
        if ($total <= 0 || ($total % $interval) !== 0) {
            return;
        }

        try {
            $sample     = AiLearning::allGlobal(100);
            $sampleText = implode("\n", array_map(
                fn($l) => '- [' . ($l['category'] ?? 'geral') . '] (q:' . ($l['quality_score'] ?? '?') . ') ' . $l['content'],
                $sample
            ));
            $engine  = new TuquinhaEngine();
            $instruction = "Com base nos aprendizados acumulados abaixo, sugira até 3 adições ao system prompt da IA que a tornariam significativamente melhor.\n"
                . "Retorne APENAS JSON: {\"suggestions\":[{\"suggestion\":\"...\",\"rationale\":\"...\"}]}\n"
                . "Regras: cada sugestão pronta para inserir no prompt (máx 300 chars); justificativa em 1 frase; se não houver boas sugestões, retorne {\"suggestions\":[]}.\n\n"
                . "APRENDIZADOS:\n" . mb_substr($sampleText, 0, 4000, 'UTF-8');

            $result = $engine->generateResponseWithContext(
                [['role' => 'user', 'content' => $instruction]],
                $model !== '' ? $model : null,
                null, null, null, null
            );
            $text = is_array($result)
                ? trim((string)($result['content'] ?? ''))
                : trim((string)$result);

            if ($text !== '' && $text[0] === '{') {
                $json = json_decode($text, true);
                if (is_array($json) && isset($json['suggestions'])) {
                    foreach ((array)$json['suggestions'] as $sg) {
                        $sgText = is_array($sg) ? trim((string)($sg['suggestion'] ?? '')) : '';
                        $sgRat  = is_array($sg) ? trim((string)($sg['rationale'] ?? '')) : '';
                        if ($sgText !== '' && !AiPromptSuggestion::existsSimilar($sgText)) {
                            AiPromptSuggestion::create($sgText, $sgRat);
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            error_log('[CronLearning] Sugestão de prompt falhou: ' . $e->getMessage());
        }
    }
}

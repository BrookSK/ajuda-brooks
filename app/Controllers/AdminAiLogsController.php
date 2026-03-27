<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\AiLearning;
use App\Models\LearningJob;
use App\Models\Setting;

class AdminAiLogsController extends Controller
{
    private function ensureAdmin(): void
    {
        if (empty($_SESSION['is_admin'])) {
            header('Location: /admin/login');
            exit;
        }
    }

    public function index(): void
    {
        $this->ensureAdmin();

        $pdo = Database::getConnection();

        // ── Estatísticas gerais ──────────────────────────────────────────────
        $totalLearnings = AiLearning::countActive();
        $categories     = AiLearning::distinctCategories();

        $stmtQ = $pdo->query(
            "SELECT quality_score, COUNT(*) as cnt
             FROM ai_learnings WHERE deleted_at IS NULL AND quality_score IS NOT NULL
             GROUP BY quality_score ORDER BY quality_score DESC"
        );
        $qualityDist = $stmtQ ? $stmtQ->fetchAll(\PDO::FETCH_ASSOC) : [];

        $stmtGlobal = $pdo->query(
            "SELECT COUNT(*) FROM ai_learnings WHERE deleted_at IS NULL AND scope = 'global'"
        );
        $totalGlobal = $stmtGlobal ? (int)$stmtGlobal->fetchColumn() : 0;

        $stmtPersona = $pdo->query(
            "SELECT COUNT(*) FROM ai_learnings WHERE deleted_at IS NULL AND scope = 'personality'"
        );
        $totalPersonality = $stmtPersona ? (int)$stmtPersona->fetchColumn() : 0;

        $stmtConsolidated = $pdo->query(
            "SELECT COUNT(*) FROM ai_learnings WHERE deleted_at IS NULL AND is_consolidated = 1"
        );
        $totalConsolidated = $stmtConsolidated ? (int)$stmtConsolidated->fetchColumn() : 0;

        // ── Aprendizados recentes (últimas 200 entradas) ──────────────────────
        $filterCategory = trim((string)($_GET['categoria'] ?? ''));
        $filterScope    = trim((string)($_GET['scope'] ?? ''));
        $filterType     = trim((string)($_GET['ltype'] ?? ''));
        if (!in_array($filterType, ['fact', 'experience', 'warning', ''], true)) {
            $filterType = '';
        }
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 50;
        $offset  = ($page - 1) * $perPage;

        $conditions = ['al.deleted_at IS NULL'];
        $params     = [];

        if ($filterCategory !== '') {
            $conditions[] = 'al.category = :cat';
            $params['cat'] = $filterCategory;
        }
        if ($filterScope !== '' && in_array($filterScope, ['global', 'personality'], true)) {
            $conditions[] = 'al.scope = :scope';
            $params['scope'] = $filterScope;
        }
        if ($filterType !== '') {
            $conditions[] = 'al.learning_type = :ltype';
            $params['ltype'] = $filterType;
        }

        // ── Contagem por tipo para stats ──────────────────────────────────────
        $stmtTypes = $pdo->query(
            "SELECT learning_type, COUNT(*) as cnt
             FROM ai_learnings WHERE deleted_at IS NULL
             GROUP BY learning_type"
        );
        $typeStats = $stmtTypes ? $stmtTypes->fetchAll(\PDO::FETCH_KEY_PAIR) : [];

        $whereClause = implode(' AND ', $conditions);

        $stmtLog = $pdo->prepare(
            'SELECT al.id, al.scope, al.content, al.category, al.quality_score,
                    al.usage_count, al.is_consolidated, al.learning_type, al.created_at,
                    p.name AS personality_name
             FROM ai_learnings al
             LEFT JOIN personalities p ON p.id = al.scope_id AND al.scope = \'personality\'
             WHERE ' . $whereClause . '
             ORDER BY al.created_at DESC
             LIMIT ' . (int)$perPage . ' OFFSET ' . (int)$offset
        );
        $stmtLog->execute($params);
        $recentLearnings = $stmtLog->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $stmtCount = $pdo->prepare(
            'SELECT COUNT(*) FROM ai_learnings al WHERE ' . $whereClause
        );
        $stmtCount->execute($params);
        $filteredTotal = (int)$stmtCount->fetchColumn();
        $totalPages    = (int)ceil($filteredTotal / $perPage);

        // ── Status da fila de jobs ────────────────────────────────────────────
        $stmtJobs = $pdo->query(
            "SELECT status, COUNT(*) as cnt
             FROM ai_learning_jobs
             GROUP BY status ORDER BY FIELD(status,'running','pending','done','error')"
        );
        $jobStats = $stmtJobs ? $stmtJobs->fetchAll(\PDO::FETCH_KEY_PAIR) : [];

        // ── Últimos 10 jobs (para ver atividade recente) ──────────────────────
        $stmtRecentJobs = $pdo->query(
            "SELECT id, status, conversation_id, created_at, started_at, done_at, error_text
             FROM ai_learning_jobs
             ORDER BY id DESC LIMIT 10"
        );
        $recentJobs = $stmtRecentJobs ? $stmtRecentJobs->fetchAll(\PDO::FETCH_ASSOC) : [];

        // ── Ritmo de aprendizado: por dia (últimos 14 dias) ───────────────────
        $stmtRhythm = $pdo->query(
            "SELECT DATE(created_at) as day, COUNT(*) as cnt
             FROM ai_learnings
             WHERE deleted_at IS NULL AND created_at >= NOW() - INTERVAL 14 DAY
             GROUP BY day ORDER BY day ASC"
        );
        $rhythmData = $stmtRhythm ? $stmtRhythm->fetchAll(\PDO::FETCH_ASSOC) : [];

        // Fill missing days with 0
        $rhythmByDay = [];
        for ($i = 13; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-{$i} days"));
            $rhythmByDay[$day] = 0;
        }
        foreach ($rhythmData as $r) {
            $rhythmByDay[(string)$r['day']] = (int)$r['cnt'];
        }

        $this->view('admin/ai_logs/index', [
            'pageTitle'         => 'Logs de Aprendizado da IA',
            'totalLearnings'    => $totalLearnings,
            'totalGlobal'       => $totalGlobal,
            'totalPersonality'  => $totalPersonality,
            'totalConsolidated' => $totalConsolidated,
            'categories'        => $categories,
            'qualityDist'       => $qualityDist,
            'recentLearnings'   => $recentLearnings,
            'filteredTotal'     => $filteredTotal,
            'totalPages'        => $totalPages,
            'page'              => $page,
            'filterCategory'    => $filterCategory,
            'filterScope'       => $filterScope,
            'filterType'        => $filterType,
            'typeStats'         => $typeStats,
            'jobStats'          => $jobStats,
            'recentJobs'        => $recentJobs,
            'rhythmByDay'       => $rhythmByDay,
        ]);
    }

    public function live(): void
    {
        $this->ensureAdmin();

        // Endpoint JSON para polling em tempo real (últimos N aprendizados)
        $since = trim((string)($_GET['since'] ?? ''));
        $pdo   = Database::getConnection();

        $params = [];
        $where  = 'deleted_at IS NULL';
        if ($since !== '') {
            $where         .= ' AND created_at > :since';
            $params['since'] = $since;
        } else {
            $where .= ' AND created_at >= NOW() - INTERVAL 5 MINUTE';
        }

        $stmt = $pdo->prepare(
            'SELECT id, scope, content, category, quality_score, learning_type, created_at
             FROM ai_learnings WHERE ' . $where . '
             ORDER BY created_at DESC LIMIT 30'
        );
        $stmt->execute($params);
        $items = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $stmtJobs = $pdo->query(
            "SELECT COUNT(*) FROM ai_learning_jobs WHERE status IN ('pending','running')"
        );
        $pendingJobs = $stmtJobs ? (int)$stmtJobs->fetchColumn() : 0;

        $stmtTotal = $pdo->query('SELECT COUNT(*) FROM ai_learnings WHERE deleted_at IS NULL');
        $total     = $stmtTotal ? (int)$stmtTotal->fetchColumn() : 0;

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'items'       => $items,
            'pendingJobs' => $pendingJobs,
            'total'       => $total,
            'serverTime'  => date('Y-m-d H:i:s'),
        ], JSON_UNESCAPED_UNICODE);
    }
}

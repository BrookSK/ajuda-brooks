<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AiLearning;
use App\Models\LearningJob;
use App\Models\Setting;

class AdminAiLearningsController extends Controller
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

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 50;
        $offset = ($page - 1) * $perPage;

        $filterCategory = trim((string)($_GET['categoria'] ?? ''));

        $learnings  = AiLearning::allForAdmin($perPage, $offset, $filterCategory);
        $total      = AiLearning::countActive($filterCategory);
        $totalPages = (int)ceil($total / $perPage);

        $enabled        = (string)Setting::get('ai_learning_enabled', '1') !== '0';
        $pendingJobs    = LearningJob::countPending();
        $cronToken      = trim((string)Setting::get('news_cron_token', ''));
        $appUrl         = rtrim((string)Setting::get('app_public_url', ''), '/');
        $cronBaseUrl    = ($cronToken !== '' && $appUrl !== '') ? $appUrl : '';
        $cronTokenParam = $cronToken !== '' ? '&token=' . urlencode($cronToken) : '';

        $this->view('admin/ai_learnings/index', [
            'pageTitle'       => 'Aprendizados da IA',
            'learnings'       => $learnings,
            'total'           => $total,
            'page'            => $page,
            'totalPages'      => $totalPages,
            'enabled'         => $enabled,
            'pendingJobs'     => $pendingJobs,
            'cronBaseUrl'     => $cronBaseUrl,
            'cronTokenParam'  => $cronTokenParam,
            'filterCategory'  => $filterCategory,
        ]);
    }

    public function delete(): void
    {
        $this->ensureAdmin();
        $id = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
        if ($id > 0) {
            AiLearning::softDelete($id);
        }
        header('Location: /admin/ia-aprendizados');
        exit;
    }

    public function toggleEnabled(): void
    {
        $this->ensureAdmin();
        $current = (string)Setting::get('ai_learning_enabled', '1');
        $new = ($current === '0') ? '1' : '0';
        Setting::set('ai_learning_enabled', $new);
        header('Location: /admin/ia-aprendizados');
        exit;
    }

    public function deleteAll(): void
    {
        $this->ensureAdmin();
        if (!empty($_POST['confirm']) && $_POST['confirm'] === 'yes') {
            $pdo = \App\Core\Database::getConnection();
            $pdo->exec("UPDATE ai_learnings SET deleted_at = NOW() WHERE deleted_at IS NULL");
        }
        header('Location: /admin/ia-aprendizados');
        exit;
    }
}
